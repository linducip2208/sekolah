<?php

namespace App\Console\Commands;

use App\Models\Donation\DonationCampaign;
use App\Models\Event\SchoolEvent;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ExportPseoStaticCommand extends Command
{
    protected $signature = 'pseo:export
        {--output= : Output directory (default public/sitemap)}
        {--base-url= : Override APP_URL for the rendered <loc> entries}
        {--limit= : Limit total URLs (debug)}
        {--no-root-copy : Skip copying sitemap.xml + robots.txt to public root}';

    protected $description = 'Render all programmatic SEO pages to static HTML files and generate sitemap.xml.';

    public function handle(HttpKernel $kernel): int
    {
        $output = $this->option('output') ?: public_path('sitemap');
        $baseUrl = rtrim($this->option('base-url') ?: config('app.url'), '/');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if (!File::isDirectory($output)) {
            File::makeDirectory($output, 0755, true);
        }

        // Override URL generator + app.url so canonical/og:url/internal links use the target host
        $originalAppUrl = config('app.url');
        config(['app.url' => $baseUrl]);
        \Illuminate\Support\Facades\URL::forceRootUrl($baseUrl);
        if (str_starts_with($baseUrl, 'https://')) {
            \Illuminate\Support\Facades\URL::forceHttps(true);
        }

        $urls = $this->buildPseoUrls();
        if ($limit) {
            $urls = array_slice($urls, 0, $limit);
        }

        $this->info("Rendering " . count($urls) . " pSEO pages → {$output}");
        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        $success = 0;
        $failed  = 0;
        $manifest = [];

        foreach ($urls as $entry) {
            $path = $entry['path'];
            $filename = $this->pathToFilename($path);
            $absoluteLoc = $baseUrl . $path;

            try {
                $request = Request::create($path, 'GET');
                $response = $kernel->handle($request);

                if ($response->getStatusCode() === 200) {
                    File::put($output . DIRECTORY_SEPARATOR . $filename, $response->getContent());
                    $manifest[] = [
                        'path'     => $path,
                        'loc'      => $absoluteLoc,
                        'file'     => $filename,
                        'priority' => $entry['priority'],
                        'size'     => strlen($response->getContent()),
                    ];
                    $success++;
                } else {
                    $failed++;
                    $this->warn("\nSkip {$path} (HTTP {$response->getStatusCode()})");
                }

                $kernel->terminate($request, $response);
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("\nError {$path}: " . $e->getMessage());
            }

            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $sitemapXml = $this->buildSitemapXml(array_map(
            fn($m) => ['loc' => $m['loc'], 'priority' => $m['priority']],
            $manifest
        ));
        File::put($output . DIRECTORY_SEPARATOR . 'sitemap.xml', $sitemapXml);

        File::put(
            $output . DIRECTORY_SEPARATOR . 'manifest.json',
            json_encode([
                'generated_at' => now()->toIso8601String(),
                'base_url'     => $baseUrl,
                'total_pages'  => $success,
                'failed'       => $failed,
                'pages'        => $manifest,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        config(['app.url' => $originalAppUrl]);

        // Copy sitemap.xml + robots.txt to public root for standard discovery
        if (!$this->option('no-root-copy')) {
            File::copy($output . DIRECTORY_SEPARATOR . 'sitemap.xml', public_path('sitemap.xml'));
            $robotsContent  = "User-agent: *\n";
            $robotsContent .= "Allow: /\n";
            $robotsContent .= "Disallow: /admin\n";
            $robotsContent .= "Disallow: /super\n";
            $robotsContent .= "Disallow: /api\n";
            $robotsContent .= "Disallow: /__pair\n";
            $robotsContent .= "\nSitemap: {$baseUrl}/sitemap.xml\n";
            File::put(public_path('robots.txt'), $robotsContent);
        }

        $this->info("✓ Success: {$success}");
        if ($failed > 0) {
            $this->warn("✗ Failed:  {$failed}");
        }
        $this->info("✓ Base URL: {$baseUrl}");
        $this->info("✓ Sitemap:  {$output}/sitemap.xml");
        $this->info("✓ Manifest: {$output}/manifest.json");
        if (!$this->option('no-root-copy')) {
            $this->info("✓ Root copy: public/sitemap.xml + public/robots.txt");
        }

        return self::SUCCESS;
    }

    /**
     * Build the full list of pSEO URLs with priority.
     * Mirrors the logic in PseoController::sitemap().
     */
    private function buildPseoUrls(): array
    {
        $urls = [];
        $year = now()->year;

        // Cities — sample, expand as needed
        $cities = [
            'jakarta', 'bandung', 'surabaya', 'yogyakarta', 'medan',
            'semarang', 'makassar', 'palembang', 'tangerang', 'depok',
            'bekasi', 'bogor', 'malang', 'denpasar', 'pekanbaru',
        ];

        // Live data
        try {
            $schools = School::where('is_active', true)->get();
            foreach ($schools as $s) {
                $city = $s->settings['city'] ?? null;
                if ($city) {
                    $urls[] = ['path' => "/best-schools-{$city}-{$year}", 'priority' => '0.8'];
                    $urls[] = ['path' => "/ppdb/{$city}",                 'priority' => '0.9'];
                }
                $urls[] = ['path' => "/alternatives-to-{$s->subdomain}",  'priority' => '0.7'];
            }
        } catch (\Throwable) {}

        $schoolTypes       = ['sd', 'smp', 'sma', 'smk', 'tk', 'pesantren', 'internasional'];
        $tuitionTypes      = ['sd', 'smp', 'sma', 'smk', 'tk', 'pesantren']; // /biaya-spp regex excludes 'internasional'
        foreach ($cities as $city) {
            foreach ($schoolTypes as $type) {
                $urls[] = ['path' => "/best-{$type}-schools-in-{$city}-{$year}", 'priority' => '0.7'];
            }
            foreach ($tuitionTypes as $type) {
                $urls[] = ['path' => "/biaya-spp-{$type}-{$city}", 'priority' => '0.7'];
            }
            $urls[] = ['path' => "/sekolah-internasional-{$city}",    'priority' => '0.7'];
            $urls[] = ['path' => "/sekolah-asrama-{$city}",           'priority' => '0.7'];
            $urls[] = ['path' => "/sekolah-akreditasi-a-{$city}",     'priority' => '0.7'];
            foreach (['islam', 'katolik', 'kristen'] as $religion) {
                $urls[] = ['path' => "/sekolah-{$religion}-{$city}", 'priority' => '0.6'];
            }
        }

        foreach (['merdeka', 'k13', 'cambridge', 'ib', 'montessori', 'charlotte-mason', 'diniyah'] as $curr) {
            $urls[] = ['path' => "/kurikulum-{$curr}", 'priority' => '0.6'];
        }
        foreach (['ipa', 'ips', 'bahasa', 'agama'] as $major) {
            $urls[] = ['path' => "/jurusan-sma-{$major}", 'priority' => '0.6'];
        }
        foreach (['rpl', 'tkj', 'akuntansi', 'tata-boga', 'multimedia', 'farmasi', 'keperawatan', 'otomotif', 'teknik-mesin', 'listrik'] as $major) {
            $urls[] = ['path' => "/jurusan-smk-{$major}", 'priority' => '0.6'];
        }
        foreach (['prestasi', 'kurang-mampu', 'tahfidz', 'olahraga', 'seni', 'akademik'] as $bea) {
            $urls[] = ['path' => "/beasiswa-{$bea}-{$year}", 'priority' => '0.6'];
        }

        $popularCities = ['jakarta', 'bandung', 'surabaya', 'yogyakarta', 'medan'];
        foreach ($popularCities as $city) {
            foreach (['matematika', 'bahasa-inggris', 'fisika', 'kimia', 'biologi', 'agama', 'tik'] as $subject) {
                $urls[] = ['path' => "/lowongan-guru-{$subject}-{$city}", 'priority' => '0.5'];
            }
            foreach (['pramuka', 'paskibra', 'rohis', 'english-club', 'robotik', 'musik', 'basket', 'futsal'] as $eks) {
                $urls[] = ['path' => "/ekstrakurikuler-{$eks}-{$city}", 'priority' => '0.5'];
            }
        }

        try {
            $campaigns = DonationCampaign::withoutGlobalScopes()
                ->where('is_public', true)->where('status', 'active')->with('school')->get();
            foreach ($campaigns as $c) {
                $urls[] = ['path' => "/donate/{$c->school->subdomain}/{$c->slug}", 'priority' => '0.6'];
            }
        } catch (\Throwable) {}

        try {
            $events = SchoolEvent::withoutGlobalScopes()
                ->where('is_published', true)->where('starts_at', '>=', now())->with('school')->get();
            foreach ($events as $e) {
                $urls[] = ['path' => "/events/{$e->school->subdomain}/{$e->slug}", 'priority' => '0.6'];
            }
        } catch (\Throwable) {}

        return $urls;
    }

    private function pathToFilename(string $path): string
    {
        $name = trim($path, '/');
        $name = str_replace('/', '__', $name);
        $name = preg_replace('/[^a-z0-9_\-]/i', '_', $name);
        return $name . '.html';
    }

    private function buildSitemapXml(array $urls): string
    {
        $now = now()->toAtomString();
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";
        return $xml;
    }
}
