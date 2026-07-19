<?php

namespace App\Services\SEO;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    private string $key;
    private string $keyFilePath;

    private array $engines = [
        'Bing'   => 'https://www.bing.com/indexnow',
        'Yandex' => 'https://yandex.com/indexnow',
        'Seznam' => 'https://search.seznam.cz/indexnow',
        'Naver'  => 'https://searchadvisor.naver.com/indexnow',
    ];

    private int $maxBatch = 5000;

    private string $cacheKey = 'indexnow_submitted_urls';

    private int $cacheMax = 50000;

    public function __construct()
    {
        $this->keyFilePath = public_path('indexnow-key.txt');
        $this->key = $this->readKey();
    }

    private function readKey(): string
    {
        if (file_exists($this->keyFilePath)) {
            return trim(file_get_contents($this->keyFilePath));
        }

        Log::warning('IndexNow: key file not found at ' . $this->keyFilePath);

        return '';
    }

    // ----------------------------------------------------------------
    // URL Collection
    // ----------------------------------------------------------------

    public function collectAllUrls(): array
    {
        $urls   = [];
        $year   = now()->year;
        $appUrl = rtrim(config('app.url', url('/')), '/');

        // Static pages
        $urls[] = $appUrl . '/';
        $urls[] = $appUrl . '/docs';
        $urls[] = $appUrl . '/daftar';

        // Blog
        $urls[] = $appUrl . '/blog';
        $urls[] = $appUrl . '/blog/feed.xml';

        // Blog posts — published
        try {
            if (class_exists(BlogPost::class)) {
                $posts = BlogPost::published()->select('slug')->get();
                foreach ($posts as $post) {
                    $urls[] = $appUrl . '/blog/' . $post->slug;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('IndexNow: failed to collect blog posts — ' . $e->getMessage());
        }

        // Pricing
        $urls[] = $appUrl . '/pricing';

        // pSEO routes — best schools by type + city
        $cities = [
            'jakarta', 'bandung', 'surabaya', 'medan', 'semarang',
            'yogyakarta', 'denpasar', 'makassar', 'palembang', 'tangerang',
            'depok', 'bogor', 'bekasi', 'malang', 'solo',
        ];
        $schoolTypes = ['sd', 'smp', 'sma', 'smk', 'tk', 'pesantren', 'internasional'];

        foreach ($cities as $city) {
            foreach ($schoolTypes as $type) {
                $urls[] = $appUrl . "/best-{$type}-schools-in-{$city}-{$year}";
                $urls[] = $appUrl . "/biaya-spp-{$type}-{$city}";
            }
            $urls[] = $appUrl . "/sekolah-internasional-{$city}";
            $urls[] = $appUrl . "/sekolah-asrama-{$city}";
            $urls[] = $appUrl . "/sekolah-akreditasi-a-{$city}";

            foreach (['islam', 'katolik', 'kristen'] as $religion) {
                $urls[] = $appUrl . "/sekolah-{$religion}-{$city}";
            }
        }

        // Best schools per city (from database)
        try {
            $schools = \App\Models\School::where('is_active', true)->get();
            foreach ($schools as $s) {
                $city = $s->settings['city'] ?? null;
                if ($city) {
                    $urls[] = $appUrl . "/best-schools-{$city}-{$year}";
                    $urls[] = $appUrl . "/ppdb/{$city}";
                }
                $urls[] = $appUrl . "/alternatives-to-{$s->subdomain}";
            }
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        // Curriculum guides
        foreach (['merdeka', 'k13', 'cambridge', 'ib', 'montessori', 'charlotte-mason', 'diniyah'] as $curr) {
            $urls[] = $appUrl . "/kurikulum-{$curr}";
        }

        // SMA majors
        foreach (['ipa', 'ips', 'bahasa', 'agama'] as $major) {
            $urls[] = $appUrl . "/jurusan-sma-{$major}";
        }

        // SMK majors
        foreach (['rpl', 'tkj', 'akuntansi', 'tata-boga', 'multimedia', 'farmasi', 'keperawatan', 'otomotif'] as $major) {
            $urls[] = $appUrl . "/jurusan-smk-{$major}";
        }

        // Scholarships
        foreach (['prestasi', 'kurang-mampu', 'tahfidz', 'olahraga', 'seni', 'akademik'] as $bea) {
            $urls[] = $appUrl . "/beasiswa-{$bea}-{$year}";
        }

        // Teacher jobs + extracurriculars for popular cities
        $popularCities = ['jakarta', 'bandung', 'surabaya', 'yogyakarta', 'medan'];
        foreach ($popularCities as $city) {
            foreach (['matematika', 'bahasa-inggris', 'fisika', 'kimia', 'biologi', 'agama', 'tik'] as $subject) {
                $urls[] = $appUrl . "/lowongan-guru-{$subject}-{$city}";
            }
            foreach (['pramuka', 'paskibra', 'rohis', 'english-club', 'robotik', 'musik', 'basket', 'futsal'] as $eks) {
                $urls[] = $appUrl . "/ekstrakurikuler-{$eks}-{$city}";
            }
        }

        // Donation landing pages
        try {
            $campaigns = \App\Models\Donation\DonationCampaign::withoutGlobalScopes()
                ->where('is_public', true)->where('status', 'active')->with('school')->get();
            foreach ($campaigns as $c) {
                $urls[] = $appUrl . "/donate/{$c->school->subdomain}/{$c->slug}";
            }
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        // Event landing pages
        try {
            $events = \App\Models\Event\SchoolEvent::withoutGlobalScopes()
                ->where('is_published', true)->where('starts_at', '>=', now())->with('school')->get();
            foreach ($events as $e) {
                $urls[] = $appUrl . "/events/{$e->school->subdomain}/{$e->slug}";
            }
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        // Alumni pages
        try {
            $alumniGroups = \App\Models\Alumni\AlumniProfile::withoutGlobalScopes()
                ->where('verified', true)
                ->select('school_id', 'graduation_year')
                ->distinct()
                ->with('school')
                ->get();
            foreach ($alumniGroups as $ag) {
                if ($ag->school) {
                    $urls[] = $appUrl . "/alumni/{$ag->school->subdomain}/{$ag->graduation_year}";
                }
            }
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        // Docs sub-pages
        foreach (['admin', 'parent', 'student', 'teacher', 'super-admin', 'developer'] as $role) {
            $urls[] = $appUrl . "/docs/{$role}";
        }

        return array_unique($urls);
    }

    // ----------------------------------------------------------------
    // Submission Methods
    // ----------------------------------------------------------------

    public function submitSingle(string $url): array
    {
        return $this->submit([$url]);
    }

    public function submit(array $urls): array
    {
        if (empty($this->key)) {
            Log::warning('IndexNow: cannot submit — no API key');

            return array_fill_keys(array_keys($this->engines), false);
        }

        $payload = [
            'host'        => parse_url(config('app.url'), PHP_URL_HOST),
            'key'         => $this->key,
            'keyLocation' => url('indexnow-key.txt'),
            'urlList'     => array_values($urls),
        ];

        $results = [];

        foreach ($this->engines as $name => $endpoint) {
            try {
                $response = Http::timeout(10)
                    ->retry(2, 500)
                    ->post($endpoint, $payload);

                $success = $response->successful();

                if ($success) {
                    Log::info("IndexNow: submitted " . count($urls) . " URLs to {$name}");
                } else {
                    Log::warning("IndexNow: {$name} returned {$response->status()} — {$response->body()}");
                }

                $results[$name] = $success;
            } catch (\Throwable $e) {
                Log::error("IndexNow: {$name} request failed — {$e->getMessage()}");
                $results[$name] = false;
            }
        }

        return $results;
    }

    public function submitAll(): array
    {
        $allUrls = $this->collectAllUrls();
        $chunks  = array_chunk($allUrls, $this->maxBatch);

        $totalResults = [];

        foreach ($chunks as $i => $chunk) {
            $results = $this->submit($chunk);
            $totalResults[] = $results;

            if (count($chunks) > 1 && $i < count($chunks) - 1) {
                sleep(1);
            }
        }

        Log::info('IndexNow: submitAll completed — ' . count($allUrls) . ' total URLs in ' . count($chunks) . ' batches');

        return $totalResults;
    }

    public function submitNewOnly(array $urls): array
    {
        $submitted = $this->getSubmittedUrls();
        $new       = array_diff($urls, $submitted);

        $submittedCount = count($new) > 0 ? 0 : 0;
        $skippedCount   = count($urls) - count($new);

        if (empty($new)) {
            Log::info('IndexNow: no new URLs to submit (all ' . count($urls) . ' already submitted)');

            return ['submitted' => 0, 'skipped' => $skippedCount, 'results' => []];
        }

        $results = $this->submit(array_values($new));
        $submittedCount = count($new);

        $this->markSubmitted(array_values($new));

        Log::info("IndexNow: submitNewOnly — {$submittedCount} new submitted, {$skippedCount} skipped");

        return [
            'submitted' => $submittedCount,
            'skipped'   => $skippedCount,
            'results'   => $results,
        ];
    }

    // ----------------------------------------------------------------
    // Cache Helpers
    // ----------------------------------------------------------------

    private function getSubmittedUrls(): array
    {
        return Cache::get($this->cacheKey, []);
    }

    private function markSubmitted(array $urls): void
    {
        $existing = $this->getSubmittedUrls();
        $merged   = array_unique(array_merge($existing, $urls));

        // Trim to max size
        if (count($merged) > $this->cacheMax) {
            $merged = array_slice($merged, -$this->cacheMax);
        }

        // Cache for 1 year (525600 minutes)
        Cache::put($this->cacheKey, $merged, 525600);
    }

    // ----------------------------------------------------------------
    // Convenience
    // ----------------------------------------------------------------

    public function getKey(): string
    {
        return $this->key;
    }

    public function getEngines(): array
    {
        return $this->engines;
    }
}
