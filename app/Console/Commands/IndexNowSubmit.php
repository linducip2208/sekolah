<?php

namespace App\Console\Commands;

use App\Services\SEO\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow
                            {--all : Submit all sitemap URLs}
                            {--new : Submit only new URLs since last run}
                            {--url= : Submit a single URL}';

    protected $description = 'Submit URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $service): int
    {
        if ($url = $this->option('url')) {
            $result = $service->submitSingle($url);
            foreach ($result as $engine => $status) {
                $this->info("{$engine}: " . ($status ? 'OK' : 'FAIL'));
            }
            return self::SUCCESS;
        }

        if ($this->option('new')) {
            $urls   = $service->collectAllUrls();
            $result = $service->submitNewOnly($urls);
            $this->info('Submitted: ' . $result['submitted'] . ' URLs');
            $this->info('Skipped: ' . $result['skipped'] . ' URLs');
            return self::SUCCESS;
        }

        // Default: --all
        $result = $service->submitAll();
        $this->info('Submitted all URLs in batches.');
        return self::SUCCESS;
    }
}
