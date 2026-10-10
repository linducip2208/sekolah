<?php

use App\Models\School;

test('product pillar pages render with unique SEO metadata', function () {
    $pages = [
        '/sistem-informasi-sekolah' => 'Sistem Informasi Sekolah Terpadu',
        '/aplikasi-ppdb-online' => 'Aplikasi PPDB Online',
        '/aplikasi-pembayaran-spp' => 'Aplikasi Pembayaran SPP',
        '/aplikasi-rapor-digital' => 'Aplikasi Rapor Digital',
        '/school-management-system' => 'School Management System',
        '/tentang-sikad-pro' => 'Tentang SIKAD PRO',
    ];

    $titles = [];
    foreach ($pages as $url => $fragment) {
        $response = $this->get($url);
        $response->assertOk();
        $html = $response->getContent();
        expect($html)->toContain('<h1');
        expect($html)->toContain('canonical');
        expect($html)->toContain('application/ld+json');
        expect($html)->toContain($fragment);

        preg_match('/<title>(.*?)<\/title>/s', $html, $m);
        $titles[$url] = trim($m[1] ?? '');
    }

    // Titles must be unique per page (no duplicate titles).
    expect(array_values($titles))->each->not->toBeEmpty();
    expect(count(array_unique(array_values($titles))))->toBe(count($titles));
});

test('sitemap excludes demo schools and includes pillars', function () {
    School::factory()->create(['subdomain' => 'demo', 'is_active' => true]);
    $real = School::factory()->create(['subdomain' => 'sman1asli', 'is_active' => true]);

    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    $xml = $response->getContent();

    expect($xml)->not->toContain('/alternatives-to-demo<');
    expect($xml)->toContain("/alternatives-to-{$real->subdomain}");
    expect($xml)->toContain('/sistem-informasi-sekolah');
    expect($xml)->toContain('/tentang-sikad-pro');

    // No duplicate locs.
    preg_match_all('/<loc>(.*?)<\/loc>/', $xml, $m);
    expect(count($m[1]))->toBe(count(array_unique($m[1])));
});

test('robots blocks private areas and references sitemap', function () {
    $response = $this->get('/robots.txt');
    $response->assertOk();
    $txt = $response->getContent();

    expect($txt)->toContain('Disallow: /admin/');
    expect($txt)->toContain('Disallow: /api/');
    expect($txt)->toContain('Sitemap:');
});

test('pricing page renders with plans, FAQ and offers JSON-LD', function () {
    $response = $this->get('/harga-source-code-sekolah');
    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain('<h1');
    expect($html)->toContain('Lisensi Source Code');
    expect($html)->toContain('SaaS Berlangganan');
    expect($html)->toContain('Enterprise Kustom');
    expect($html)->toContain('application/ld+json');
    expect($html)->toContain('Berapa harga source code SIKAD PRO?');
});

test('buy-source-code pages render per module and reject unknown', function () {
    foreach (['ppdb', 'spp', 'rapor', 'lms', 'full'] as $modul) {
        $response = $this->get("/beli-source-code-{$modul}");
        $response->assertOk();
        expect($response->getContent())->toContain('Beli Source Code');
    }

    $this->get('/beli-source-code-unknown')->assertNotFound();
});

test('sitemap includes pricing and buy pages without duplicates', function () {
    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    $xml = $response->getContent();

    expect($xml)->toContain('/harga-source-code-sekolah');
    expect($xml)->toContain('/beli-source-code-ppdb');
    expect($xml)->toContain('/beli-source-code-full');

    preg_match_all('/<loc>(.*?)<\/loc>/', $xml, $m);
    expect(count($m[1]))->toBe(count(array_unique($m[1])));
});
