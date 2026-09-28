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
