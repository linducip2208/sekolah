<?php

namespace App\Http\Controllers\Web\SEO;

use App\Http\Controllers\Controller;
use App\Models\Alumni\AlumniProfile;
use App\Models\Donation\DonationCampaign;
use App\Models\Event\SchoolEvent;
use App\Models\PPDB\PpdbPeriod;
use App\Models\School;
use App\Services\Branding\BrandingService;
use App\Services\SEO\StructuredDataBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PseoController extends Controller
{
    public function __construct(
        private BrandingService $branding,
        private StructuredDataBuilder $sdBuilder,
    ) {}

    private function tenantUrl(string $subdomain, string $path = ''): string
    {
        $base = config('multitenancy.base_domain', 'sikadpro.whitelabel.co.id');
        return "https://{$subdomain}.{$base}" . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    /**
     * /best-schools-{city}-{year}
     * Top schools listing per city + year
     */
    public function bestSchools(string $city, string $year): View
    {
        $schools = School::where('is_active', true)
            ->where('settings->city', $city)
            ->limit(10)
            ->get();

        $jsonLd = $this->sdBuilder->itemList(
            "Sekolah Terbaik di {$city} Tahun {$year}",
            $schools->map(fn ($s) => [
                'type' => 'EducationalOrganization',
                'name' => $s->name,
                'url'  => $this->tenantUrl($s->subdomain),
            ])->toArray(),
        );

        return view('seo.best-schools', [
            'city'    => ucfirst($city),
            'year'    => $year,
            'schools' => $schools,
            'jsonLd'  => $jsonLd,
            'meta'    => [
                'title'       => "10 Sekolah Terbaik di {$city} Tahun {$year}",
                'description' => "Daftar sekolah terbaik di {$city} tahun {$year} dengan akreditasi A, prestasi nasional, dan fasilitas lengkap. Bandingkan dan pilih sekolah terbaik untuk anak Anda.",
            ],
        ]);
    }

    /**
     * /alternatives-to-{slug}
     * Alternatives to a specific school
     */
    public function alternatives(string $slug): View
    {
        $school = School::where('subdomain', $slug)->firstOrFail();
        $alternatives = School::where('id', '!=', $school->id)
            ->where('is_active', true)
            ->limit(10)
            ->get();

        $jsonLd = $this->sdBuilder->itemList(
            "Alternatif Sekolah Selain {$school->name}",
            $alternatives->map(fn ($s) => [
                'type' => 'EducationalOrganization',
                'name' => $s->name,
                'url'  => $this->tenantUrl($s->subdomain),
            ])->toArray(),
        );

        return view('seo.alternatives', [
            'school'       => $school,
            'alternatives' => $alternatives,
            'jsonLd'       => $jsonLd,
            'meta'         => [
                'title'       => "10 Alternatif Sekolah Selain {$school->name}",
                'description' => "Cari alternatif sekolah serupa dengan {$school->name}? Bandingkan 10 sekolah terbaik dengan kurikulum, biaya, dan fasilitas yang setara.",
            ],
        ]);
    }

    /**
     * /compare/{a}-vs-{b}
     */
    public function compare(string $a, string $b): View
    {
        $schoolA = School::where('subdomain', $a)->firstOrFail();
        $schoolB = School::where('subdomain', $b)->firstOrFail();

        return view('seo.compare', [
            'a' => $schoolA,
            'b' => $schoolB,
            'meta' => [
                'title'       => "{$schoolA->name} vs {$schoolB->name} — Perbandingan Lengkap",
                'description' => "Bandingkan {$schoolA->name} dengan {$schoolB->name} dari sisi kurikulum, biaya SPP, fasilitas, prestasi, dan ulasan parent. Pilih sekolah terbaik untuk anak Anda.",
            ],
        ]);
    }

    /**
     * /ppdb/{city}
     * List sekolah buka PPDB di kota
     */
    public function ppdbByCity(string $city): View
    {
        $periods = PpdbPeriod::withoutGlobalScopes()
            ->where('is_published', true)
            ->where('close_date', '>=', now())
            ->whereHas('school', fn ($q) => $q->where('settings->city', $city))
            ->with('school')
            ->get();

        $jsonLd = $this->sdBuilder->itemList(
            "PPDB {$city} " . now()->year,
            $periods->map(fn ($p) => [
                'type' => 'EducationalOrganization',
                'name' => $p->school->name . ' — ' . $p->name,
                'url'  => $this->tenantUrl($p->school->subdomain, 'ppdb'),
            ])->toArray(),
        );

        return view('seo.ppdb-by-city', [
            'city'    => ucfirst($city),
            'periods' => $periods,
            'jsonLd'  => $jsonLd,
            'meta'    => [
                'title'       => "PPDB Online {$city} " . now()->year . " — Daftar Sekolah Buka Pendaftaran",
                'description' => "Daftar PPDB online di {$city} tahun " . now()->year . ". Sekolah buka pendaftaran, jalur zonasi, prestasi, dan afirmasi. Daftar tanpa antri.",
            ],
        ]);
    }

    /**
     * /donate/{school-slug}/{campaign-slug}
     */
    public function donationLanding(string $subdomain, string $slug): View
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $campaign = DonationCampaign::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('slug', $slug)
            ->firstOrFail();

        $branding = $this->branding->getForSchool($school->id);

        $jsonLd = $this->sdBuilder->donationCampaign([
            'title'         => $campaign->title,
            'description'   => strip_tags($campaign->description),
            'target_amount' => $campaign->target_amount,
            'school_name'   => $school->name,
        ]);

        return view('seo.donation-landing', [
            'school'   => $school,
            'campaign' => $campaign,
            'branding' => $branding,
            'jsonLd'   => $jsonLd,
            'meta'     => [
                'title'       => "{$campaign->title} — Donasi untuk {$school->name}",
                'description' => substr(strip_tags($campaign->description), 0, 160),
            ],
        ]);
    }

    /**
     * /events/{school-slug}/{event-slug}
     */
    public function eventLanding(string $subdomain, string $slug): View
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $event = SchoolEvent::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('slug', $slug)
            ->firstOrFail();

        $jsonLd = $this->sdBuilder->event([
            'name'         => $event->title,
            'description'  => strip_tags($event->description),
            'starts_at'    => $event->starts_at?->toIso8601String(),
            'ends_at'      => $event->ends_at?->toIso8601String(),
            'venue'        => $event->venue,
            'city'         => $event->city,
            'organizer'    => $school->name,
            'ticket_price' => $event->ticket_price,
        ]);

        return view('seo.event-landing', [
            'school' => $school,
            'event'  => $event,
            'jsonLd' => $jsonLd,
            'meta'   => [
                'title'       => "{$event->title} — {$school->name}",
                'description' => substr(strip_tags($event->description), 0, 160),
            ],
        ]);
    }

    /**
     * /alumni/{school-slug}/{year}
     */
    public function alumniByYear(string $subdomain, string $year): View
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $alumni = AlumniProfile::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('graduation_year', $year)
            ->where('verified', true)
            ->paginate(50);

        return view('seo.alumni-year', [
            'school' => $school,
            'year'   => $year,
            'alumni' => $alumni,
            'meta'   => [
                'title'       => "Alumni {$school->name} Angkatan {$year}",
                'description' => "Direktori alumni {$school->name} angkatan {$year}. Karir, perusahaan, dan kontak alumni untuk mentoring & networking.",
            ],
        ]);
    }

    // ============================================================
    // EXTENDED pSEO — domain-rich routes for SEO long-tail
    // ============================================================

    private array $cityProfiles = [
        'jakarta' => 'Jakarta',          'bandung' => 'Bandung',         'surabaya' => 'Surabaya',
        'medan'   => 'Medan',            'semarang' => 'Semarang',       'yogyakarta' => 'Yogyakarta',
        'denpasar'=> 'Denpasar',         'makassar' => 'Makassar',       'palembang' => 'Palembang',
        'tangerang' => 'Tangerang',      'depok'    => 'Depok',          'bogor'     => 'Bogor',
        'bekasi'  => 'Bekasi',           'malang'   => 'Malang',         'solo'      => 'Solo',
    ];

    private function cityLabel(string $slug): string
    {
        return $this->cityProfiles[strtolower($slug)] ?? ucwords(str_replace('-', ' ', $slug));
    }

    /**
     * /best-{type}-schools-in-{city}-{year}
     */
    public function bestSchoolsByType(string $type, string $city, string $year): View
    {
        $typeLabel = strtoupper($type);
        $cityLabel = $this->cityLabel($city);

        $schools = School::where('is_active', true)
            ->where('settings->city', $city)
            ->where(function ($q) use ($type) {
                $q->where('settings->level', $type)->orWhere('settings->type', $type);
            })
            ->limit(10)->get();

        $jsonLd = $this->sdBuilder->itemList(
            "10 {$typeLabel} Terbaik di {$cityLabel} {$year}",
            $schools->map(fn ($s) => ['type' => 'EducationalOrganization', 'name' => $s->name, 'url' => $this->tenantUrl($s->subdomain)])->toArray(),
        );

        return view('seo.generic-list', [
            'jsonLd' => $jsonLd,
            'pageKicker' => 'Direktori Institusi',
            'pageTitle'  => "10 {$typeLabel} Terbaik di {$cityLabel} Tahun {$year}",
            'pageLead'   => "Daftar pilihan {$typeLabel} unggulan di {$cityLabel} dengan akreditasi A, prestasi nasional, kurikulum modern, dan fasilitas lengkap untuk tahun ajaran {$year}/{$year}-1.",
            'items'      => $schools->map(fn ($s) => [
                'title' => $s->name,
                'desc'  => $s->settings['address'] ?? $cityLabel,
                'url'   => $this->tenantUrl($s->subdomain),
                'meta'  => $s->settings['accreditation'] ?? null,
            ])->toArray(),
            'narrative'  => $this->bestSchoolsNarrative($typeLabel, $cityLabel, $year),
            'faq'        => $this->bestSchoolsFaq($typeLabel, $cityLabel, $year),
            'meta' => [
                'title'       => "10 {$typeLabel} Terbaik di {$cityLabel} Tahun {$year} — Akreditasi A & Prestasi",
                'description' => "Bandingkan 10 {$typeLabel} terbaik di {$cityLabel} tahun {$year}. Lengkap dengan akreditasi, biaya SPP, kurikulum, jurusan, dan kontak admisi.",
            ],
        ]);
    }

    /**
     * /sekolah-{religion}-{city}
     */
    public function schoolsByReligion(string $religion, string $city): View
    {
        $religionLabel = ucfirst($religion);
        $cityLabel = $this->cityLabel($city);

        $schools = School::where('is_active', true)
            ->where('settings->city', $city)
            ->where('settings->religion', $religion)
            ->limit(20)->get();

        return view('seo.generic-list', [
            'pageKicker' => "Religio · {$religionLabel}",
            'pageTitle'  => "Sekolah {$religionLabel} di {$cityLabel}",
            'pageLead'   => "Pilihan sekolah {$religionLabel} di {$cityLabel} — dari TK, SD, SMP, hingga SMA. Termasuk pesantren, madrasah, dan sekolah dengan kurikulum agama yang kuat.",
            'items'      => $schools->map(fn ($s) => [
                'title' => $s->name, 'desc' => $s->settings['address'] ?? $cityLabel,
                'url' => $this->tenantUrl($s->subdomain), 'meta' => $s->settings['level'] ?? null,
            ])->toArray(),
            'narrative' => "<p>Memilih sekolah dengan landasan religi yang kuat berarti menanamkan nilai sejak dini. Sekolah {$religionLabel} di {$cityLabel} hadir untuk menggabungkan kurikulum nasional dengan pendalaman agama, akhlak, dan praktik ibadah harian.</p><p>Pertimbangan utama meliputi: kualitas guru agama, fasilitas ibadah, program tahfidz/pendalaman kitab, integrasi karakter dalam mata pelajaran umum, serta lingkungan sosial yang mendukung pengamalan nilai.</p>",
            'faq' => [
                ['Apa keunggulan sekolah ' . $religionLabel . '?', 'Pendidikan karakter berbasis ajaran agama, lingkungan kondusif untuk pengamalan nilai, dan integrasi nilai religi dalam mata pelajaran umum.'],
                ['Berapa biaya rata-rata sekolah ' . $religionLabel . ' di ' . $cityLabel . '?', 'Bervariasi mulai Rp 200rb hingga Rp 5jt per bulan. Banyak sekolah menyediakan beasiswa kurang mampu dan tahfidz.'],
                ['Apakah ada program asrama / boarding?', 'Beberapa pesantren modern dan sekolah Islam terpadu menyediakan opsi boarding dengan jadwal harian terstruktur.'],
            ],
            'meta' => [
                'title' => "Daftar Sekolah {$religionLabel} di {$cityLabel} — Pilihan Terbaik",
                'description' => "Direktori sekolah {$religionLabel} di {$cityLabel} — TK, SD, SMP, SMA. Lengkap dengan profil, biaya, kurikulum, dan kontak admisi.",
            ],
        ]);
    }

    public function internationalSchools(string $city): View
    {
        $cityLabel = $this->cityLabel($city);
        return view('seo.generic-list', [
            'pageKicker' => 'International Schools',
            'pageTitle'  => "Sekolah Internasional di {$cityLabel}",
            'pageLead'   => "Direktori lengkap sekolah internasional di {$cityLabel} dengan kurikulum Cambridge, IB (International Baccalaureate), dan American.",
            'items'      => School::where('is_active', true)
                ->where('settings->city', $city)
                ->where('settings->curriculum_type', 'international')
                ->limit(20)->get()
                ->map(fn ($s) => ['title' => $s->name, 'desc' => $s->settings['curriculum'] ?? 'Cambridge / IB', 'url' => $this->tenantUrl($s->subdomain), 'meta' => null])->toArray(),
            'narrative' => "<p>Sekolah internasional di {$cityLabel} menawarkan pengakuan global, lulusan diterima di universitas top dunia, serta kurikulum yang mengembangkan critical thinking sejak dini. Mayoritas menggunakan Cambridge (IGCSE → A-Level) atau IB (PYP → DP).</p><p>Pertimbangan: bahasa pengantar (English-only / bilingual), rasio guru asing, fasilitas, biaya tahunan (umumnya Rp 80jt–Rp 350jt), serta jalur kelanjutan ke universitas luar negeri.</p>",
            'faq' => [
                ['Berapa biaya sekolah internasional di ' . $cityLabel . '?', 'Berkisar Rp 80 juta sampai Rp 350 juta per tahun, tergantung jenjang dan kurikulum.'],
                ['Apa beda Cambridge vs IB?', 'Cambridge fokus pada penguasaan mendalam tiap mata pelajaran (IGCSE → A-Level). IB lebih holistik dengan ekstended essay, CAS, dan TOK.'],
                ['Apakah lulusannya bisa lanjut ke universitas Indonesia?', 'Bisa. Lulusan A-Level dan IB DP diterima di universitas Indonesia dengan konversi nilai sesuai aturan Kemendikbud.'],
            ],
            'meta' => [
                'title' => "Sekolah Internasional di {$cityLabel} — Cambridge & IB",
                'description' => "Daftar sekolah internasional di {$cityLabel} dengan kurikulum Cambridge IGCSE/A-Level dan IB. Bandingkan biaya, fasilitas, dan jalur universitas.",
            ],
        ]);
    }

    public function boardingSchools(string $city): View
    {
        $cityLabel = $this->cityLabel($city);
        return view('seo.generic-list', [
            'pageKicker' => 'Boarding Schools',
            'pageTitle'  => "Sekolah Asrama / Boarding School di {$cityLabel}",
            'pageLead'   => "Sekolah dengan fasilitas asrama di {$cityLabel} — pendidikan holistik 24 jam, pembentukan karakter dan kemandirian, plus akademik intensif.",
            'items'      => School::where('is_active', true)
                ->where('settings->city', $city)
                ->where('settings->has_boarding', true)
                ->limit(20)->get()
                ->map(fn ($s) => ['title' => $s->name, 'desc' => $s->settings['boarding_capacity'] ?? null, 'url' => $this->tenantUrl($s->subdomain), 'meta' => null])->toArray(),
            'narrative' => "<p>Boarding school di {$cityLabel} menawarkan immersi penuh — siswa tinggal di kampus, ikut jadwal terstruktur 24 jam, dan dibimbing oleh wali asrama. Cocok untuk anak yang membutuhkan disiplin tinggi atau berasal dari kota berbeda.</p><p>Keunggulan: belajar mandiri, jaringan teman seumur hidup, fokus akademik tanpa distraksi gadget, kegiatan ekstrakurikuler intensif. Biaya umumnya 40–60% lebih tinggi dari sekolah reguler.</p>",
            'faq' => [
                ['Mulai usia berapa anak boleh boarding?', 'Umumnya SMP (12 tahun ke atas). Ada beberapa sekolah Islam terpadu yang membuka boarding sejak SD kelas 4.'],
                ['Bolehkah pulang setiap akhir pekan?', 'Tergantung kebijakan sekolah. Banyak yang membolehkan pulang sebulan sekali, sebagian sistem ketat dengan pulang per semester.'],
                ['Berapa rasio wali asrama vs siswa?', 'Standar baik adalah 1 wali untuk 15-20 siswa, dengan supervisi 24 jam.'],
            ],
            'meta' => [
                'title' => "Boarding School di {$cityLabel} — Sekolah Asrama Terbaik",
                'description' => "Sekolah asrama / boarding di {$cityLabel}. Pendidikan 24 jam, pembentukan karakter, dan akademik intensif. Bandingkan biaya, fasilitas, kurikulum.",
            ],
        ]);
    }

    public function accreditationASchools(string $city): View
    {
        $cityLabel = $this->cityLabel($city);
        return view('seo.generic-list', [
            'pageKicker' => 'Akreditasi A',
            'pageTitle'  => "Sekolah Akreditasi A di {$cityLabel}",
            'pageLead'   => "Daftar sekolah dengan akreditasi A (unggul) di {$cityLabel} — terbukti memenuhi standar nasional pendidikan Indonesia.",
            'items'      => School::where('is_active', true)
                ->where('settings->city', $city)
                ->where('settings->accreditation', 'A')
                ->limit(30)->get()
                ->map(fn ($s) => ['title' => $s->name, 'desc' => $s->settings['level'] ?? null, 'url' => $this->tenantUrl($s->subdomain), 'meta' => 'Akreditasi A'])->toArray(),
            'narrative' => "<p>Akreditasi A merupakan peringkat tertinggi yang diberikan BAN-S/M (Badan Akreditasi Nasional) kepada sekolah/madrasah. Sekolah dengan akreditasi A telah memenuhi 8 Standar Nasional Pendidikan (SNP) dengan nilai 91-100.</p><p>Memilih sekolah ber-akreditasi A memberi jaminan kualitas dasar. Namun pertimbangkan juga kecocokan nilai sekolah dengan keluarga, jarak rumah, biaya, dan testimoni alumni.</p>",
            'faq' => [
                ['Apa itu akreditasi A?', 'Peringkat tertinggi dari BAN-S/M dengan rentang nilai 91-100, diberikan kepada sekolah yang memenuhi 8 Standar Nasional Pendidikan.'],
                ['Berapa lama akreditasi berlaku?', '5 tahun, kemudian sekolah harus reakreditasi.'],
                ['Apakah ijazah sekolah ber-akreditasi A lebih dihargai?', 'Sama legalnya, tapi kualitas pembelajaran umumnya lebih konsisten dan lulusannya lebih siap di jenjang berikutnya.'],
            ],
            'meta' => [
                'title' => "Sekolah Akreditasi A di {$cityLabel} — Daftar Lengkap",
                'description' => "Direktori sekolah akreditasi A di {$cityLabel}. Lengkap dengan jenjang (SD/SMP/SMA), kurikulum, biaya, dan kontak admisi.",
            ],
        ]);
    }

    public function tuitionByCity(string $type, string $city): View
    {
        $typeLabel = strtoupper($type);
        $cityLabel = $this->cityLabel($city);
        return view('seo.generic-content', [
            'pageKicker' => 'Biaya Pendidikan',
            'pageTitle'  => "Biaya SPP {$typeLabel} di {$cityLabel} — Panduan {$cityLabel} " . now()->year,
            'pageLead'   => "Estimasi biaya SPP, uang masuk, seragam, dan biaya lain untuk {$typeLabel} di {$cityLabel} tahun ajaran " . now()->year . "/" . (now()->year + 1) . ".",
            'narrative' => $this->tuitionNarrative($typeLabel, $cityLabel),
            'faq' => [
                ["Berapa biaya SPP {$typeLabel} di {$cityLabel}?", "Bervariasi luas — sekolah negeri umumnya gratis (BOS), swasta umum Rp 500rb-3jt, swasta favorit Rp 3-12jt, internasional Rp 12-40jt per bulan."],
                ['Apa saja biaya selain SPP?', 'Uang pangkal (sekali bayar), seragam, buku, ekstrakurikuler, study tour, asrama (jika boarding).'],
                ['Apakah ada cicilan?', 'Banyak sekolah swasta menawarkan cicilan 6-12x untuk uang pangkal. Untuk SPP umumnya bayar bulanan.'],
                ['Apakah ada beasiswa?', 'Ya. Sekolah favorit umumnya menyediakan beasiswa prestasi (akademik/non-akademik), kurang mampu, anak guru, dan tahfidz.'],
            ],
            'meta' => [
                'title' => "Biaya SPP {$typeLabel} di {$cityLabel} " . now()->year . " — Estimasi Lengkap",
                'description' => "Panduan biaya {$typeLabel} di {$cityLabel} tahun " . now()->year . ". SPP, uang pangkal, seragam, buku — dengan rentang harga sekolah negeri, swasta, dan internasional.",
            ],
        ]);
    }

    public function curriculumGuide(string $name): View
    {
        $labels = ['merdeka' => 'Kurikulum Merdeka', 'k13' => 'Kurikulum 2013 (K13)', 'cambridge' => 'Cambridge International', 'ib' => 'International Baccalaureate (IB)', 'montessori' => 'Montessori', 'charlotte-mason' => 'Charlotte Mason', 'diniyah' => 'Kurikulum Diniyah'];
        $title = $labels[$name] ?? ucfirst($name);
        return view('seo.generic-content', [
            'pageKicker' => 'Curricula',
            'pageTitle'  => "Panduan Lengkap {$title}",
            'pageLead'   => "Apa itu {$title}? Bagaimana penerapannya di sekolah? Untuk siapa kurikulum ini cocok? Penjelasan lengkap untuk orang tua dan calon siswa.",
            'narrative' => $this->curriculumNarrative($name, $title),
            'faq' => [
                ["Apa keunggulan {$title}?", "Setiap kurikulum punya filosofi & metode unik — pilih yang sesuai gaya belajar anak dan visi keluarga."],
                ["Apakah {$title} diakui di Indonesia?", "Ya, semua kurikulum yang diakui Kemendikbud / BAN dapat menghasilkan ijazah yang sah."],
                ["Bisa pindah dari kurikulum lain ke {$title}?", "Bisa, namun perlu masa adaptasi. Konsultasikan dengan kepala sekolah / kurikulum."],
            ],
            'meta' => [
                'title' => "{$title} — Panduan Lengkap untuk Orang Tua",
                'description' => "Penjelasan {$title}: filosofi, metode, asesmen, kelebihan dan kekurangan. Cocok untuk anak seperti apa.",
            ],
        ]);
    }

    public function smaMajor(string $name): View
    {
        $labels = ['ipa' => 'IPA (Ilmu Pengetahuan Alam)', 'ips' => 'IPS (Ilmu Pengetahuan Sosial)', 'bahasa' => 'Bahasa & Budaya', 'agama' => 'Keagamaan'];
        $title = $labels[$name] ?? ucfirst($name);
        return view('seo.generic-content', [
            'pageKicker' => 'Penjurusan SMA',
            'pageTitle'  => "Panduan Jurusan SMA {$title}",
            'pageLead'   => "Memilih jurusan {$title} di SMA — mata pelajaran inti, prospek kuliah, dan karir yang terbuka.",
            'narrative' => $this->smaMajorNarrative($name, $title),
            'faq' => [
                ['Bisakah lintas jurusan saat SBMPTN/UTBK?', 'Bisa, tapi peluang lebih kecil. Anak IPA umumnya bisa pilih jurusan IPA & IPS, sebaliknya IPS terbatas ke IPS.'],
                ['Kapan harus memilih jurusan?', 'Umumnya akhir kelas 10 atau awal kelas 11. Beberapa sekolah memberi tes minat-bakat.'],
                ['Pengaruh jurusan terhadap kesuksesan karir?', 'Bukan satu-satunya faktor. Soft skill, networking, dan growth mindset jauh lebih menentukan jangka panjang.'],
            ],
            'meta' => [
                'title' => "Jurusan SMA {$title} — Mata Pelajaran, Prospek, dan Karir",
                'description' => "Panduan lengkap jurusan {$title} di SMA. Mata pelajaran inti, jurusan kuliah yang terbuka, dan prospek karir.",
            ],
        ]);
    }

    public function smkMajor(string $name): View
    {
        $labels = ['rpl' => 'Rekayasa Perangkat Lunak (RPL)', 'tkj' => 'Teknik Komputer & Jaringan (TKJ)', 'akuntansi' => 'Akuntansi', 'tata-boga' => 'Tata Boga', 'multimedia' => 'Multimedia', 'farmasi' => 'Farmasi', 'keperawatan' => 'Keperawatan', 'otomotif' => 'Teknik Otomotif', 'teknik-mesin' => 'Teknik Mesin', 'listrik' => 'Teknik Listrik'];
        $title = $labels[$name] ?? ucwords(str_replace('-', ' ', $name));
        return view('seo.generic-content', [
            'pageKicker' => 'Kompetensi Keahlian',
            'pageTitle'  => "Jurusan SMK {$title} — Panduan Lengkap",
            'pageLead'   => "Apa yang dipelajari di jurusan {$title}? Sertifikasi yang bisa didapat, prospek kerja, dan rata-rata gaji lulusan.",
            'narrative' => "<p>Jurusan SMK {$title} menyiapkan siswa siap kerja melalui kombinasi teori dan praktik intensif (umumnya 60% praktik, 40% teori). Kurikulum dirancang sejalan dengan kebutuhan industri, lengkap dengan sertifikasi profesi.</p><p>Mata pelajaran kejuruan umumnya 70% dari total jam, ditambah PKL (Praktik Kerja Lapangan) di perusahaan mitra selama 3-6 bulan. Lulusannya bisa langsung kerja, kuliah, atau wirausaha.</p><p>Prospek kerja {$title} terbuka di sektor industri terkait — dari teknisi hingga supervisor — dengan jenjang karir yang jelas.</p>",
            'faq' => [
                ["Apa beda SMK {$title} vs SMA?", 'SMK fokus skill praktik untuk siap kerja; SMA fokus akademik untuk lanjut kuliah. Lulusan SMK juga bisa kuliah dengan jurusan terkait.'],
                ['Sertifikasi apa yang bisa didapat?', 'Tergantung jurusan — umumnya BNSP, sertifikat vendor (Cisco, Microsoft, Oracle, dll), atau sertifikat industri.'],
                ['Berapa rata-rata gaji fresh graduate?', 'Bervariasi Rp 3-7 juta per bulan tergantung kompetensi, kota, dan portofolio (jika ada).'],
            ],
            'meta' => [
                'title' => "Jurusan SMK {$title} — Mata Pelajaran, Sertifikasi, Prospek Kerja",
                'description' => "Panduan jurusan SMK {$title}: mata pelajaran kejuruan, sertifikasi industri, PKL, dan prospek kerja lulusan.",
            ],
        ]);
    }

    public function teacherJobs(string $subject, string $city): View
    {
        $subjectLabel = ucwords(str_replace('-', ' ', $subject));
        $cityLabel = $this->cityLabel($city);
        return view('seo.generic-content', [
            'pageKicker' => 'Karir Pendidik',
            'pageTitle'  => "Lowongan Guru {$subjectLabel} di {$cityLabel} " . now()->year,
            'pageLead'   => "Lowongan guru {$subjectLabel} di {$cityLabel} — sekolah swasta, internasional, dan negeri yang sedang membuka rekrutmen.",
            'narrative' => "<p>Profesi guru {$subjectLabel} di {$cityLabel} memiliki prospek yang stabil dengan banyak pilihan jenjang karir. Mulai dari sekolah swasta dengan rekrutmen rolling, sekolah internasional dengan benefit kompetitif, hingga sekolah negeri melalui jalur PPPK / CPNS.</p><p>Kualifikasi umum: S1 Pendidikan {$subjectLabel} atau bidang relevan, sertifikat pendidik, pengalaman mengajar (untuk sekolah favorit), dan kemampuan komunikasi yang baik. Untuk sekolah internasional umumnya membutuhkan kemampuan bahasa Inggris aktif.</p><p>Range gaji: sekolah swasta umum Rp 3-6 juta, sekolah favorit Rp 6-12 juta, internasional Rp 12-30 juta per bulan untuk pengalaman 5+ tahun.</p>",
            'faq' => [
                ['Bagaimana cara melamar?', 'Lengkapi CV, surat lamaran, ijazah, sertifikat pendidik. Lamar via email HRD sekolah atau platform job portal.'],
                ['Apakah perlu sertifikat pendidik (Akta IV / PPG)?', 'Untuk sekolah negeri & favorit, ya. Untuk swasta umum tidak wajib di awal, tapi diutamakan.'],
                ['Bisakah lulusan non-FKIP melamar?', 'Bisa untuk swasta. Untuk negeri perlu PPG terlebih dahulu.'],
            ],
            'meta' => [
                'title' => "Lowongan Guru {$subjectLabel} di {$cityLabel} " . now()->year,
                'description' => "Loker guru {$subjectLabel} di {$cityLabel}. Sekolah swasta, internasional, dan negeri. Persyaratan, gaji, dan cara melamar.",
            ],
        ]);
    }

    public function scholarshipGuide(string $type, string $year): View
    {
        $typeLabel = ucwords(str_replace('-', ' ', $type));
        return view('seo.generic-content', [
            'pageKicker' => 'Beasiswa Sekolah',
            'pageTitle'  => "Beasiswa {$typeLabel} {$year} — Panduan Lengkap",
            'pageLead'   => "Daftar beasiswa {$typeLabel} untuk siswa SD-SMA tahun {$year}, persyaratan, jumlah bantuan, dan cara mendaftar.",
            'narrative' => "<p>Beasiswa {$typeLabel} hadir sebagai bentuk komitmen sekolah dan yayasan untuk membuka akses pendidikan bagi siswa berbakat dan berprestasi. Tahun {$year} ini, banyak sekolah swasta dan negeri yang membuka kuota beasiswa lebih besar dari tahun sebelumnya.</p><p>Persyaratan umum meliputi: rapor minimal tertentu (untuk prestasi akademik), surat keterangan tidak mampu (untuk beasiswa kurang mampu), portofolio prestasi (lomba, sertifikat), serta wawancara dengan tim seleksi.</p><p>Besaran beasiswa bervariasi: full ride (gratis SPP + uang pangkal + seragam), separuh, hingga potongan SPP 25-50% selama satu tahun ajaran. Beberapa sekolah juga memberikan tunjangan biaya hidup untuk siswa boarding.</p>",
            'faq' => [
                ['Kapan masa pendaftaran beasiswa?', 'Umumnya bersamaan dengan PPDB (Maret-Juni). Beberapa sekolah membuka beasiswa midyear.'],
                ['Bisakah dipertahankan setiap tahun?', 'Umumnya berlaku 1 tahun, dengan opsi perpanjangan jika nilai/prestasi tetap.'],
                ['Bagaimana jika gagal seleksi?', 'Bisa coba beasiswa di sekolah lain, atau program beasiswa pemerintah (KIP, PIP, ADEM).'],
            ],
            'meta' => [
                'title' => "Beasiswa {$typeLabel} {$year} — Persyaratan, Jumlah, Cara Daftar",
                'description' => "Panduan beasiswa {$typeLabel} tahun {$year}. Daftar sekolah pemberi, persyaratan, besaran, dan tips lolos seleksi.",
            ],
        ]);
    }

    public function extracurricularByCity(string $name, string $city): View
    {
        $nameLabel = ucwords(str_replace('-', ' ', $name));
        $cityLabel = $this->cityLabel($city);
        return view('seo.generic-content', [
            'pageKicker' => 'Ekstrakurikuler',
            'pageTitle'  => "Sekolah dengan Ekstrakurikuler {$nameLabel} Terbaik di {$cityLabel}",
            'pageLead'   => "Ekstrakurikuler {$nameLabel} membentuk soft skill, prestasi, dan jaringan sosial siswa. Berikut sekolah di {$cityLabel} dengan program {$nameLabel} unggulan.",
            'narrative' => "<p>Ekstrakurikuler {$nameLabel} berperan besar dalam pembentukan karakter siswa — disiplin, kerja sama, kepemimpinan, dan ketekunan. Sekolah-sekolah unggul di {$cityLabel} berinvestasi serius pada program ini melalui pelatih bersertifikat, fasilitas memadai, dan kompetisi rutin.</p><p>Manfaat jangka panjang: portofolio prestasi yang berguna untuk SNBP/jalur prestasi universitas, beasiswa, hingga karir. Banyak universitas top memberi nilai tambah pada lulusan dengan rekam jejak ekstrakurikuler kuat.</p><p>Tips memilih: sesuaikan dengan minat anak (jangan dipaksa), cek kualifikasi pelatih, fasilitas, jadwal latihan yang tidak bentrok dengan akademik, dan rekam jejak prestasi.</p>",
            'faq' => [
                ['Berapa banyak ekstrakurikuler ideal?', 'Maksimal 2-3 untuk kelas regular agar tidak mengganggu akademik. Untuk siswa SMA jurusan, fokus pada 1-2 yang konsisten.'],
                ['Apakah perlu biaya tambahan?', 'Beberapa ekstrakurikuler intensif (musik privat, robotik, bahasa asing) membutuhkan biaya tambahan. Yang umum (pramuka, paskibra) gratis.'],
                ['Mulai usia berapa?', 'Umumnya kelas 4 SD. Pramuka bisa mulai kelas 2.'],
            ],
            'meta' => [
                'title' => "Ekstrakurikuler {$nameLabel} di {$cityLabel} — Sekolah Terbaik",
                'description' => "Sekolah dengan ekstrakurikuler {$nameLabel} terbaik di {$cityLabel}. Pelatih bersertifikat, fasilitas, dan rekam jejak prestasi.",
            ],
        ]);
    }

    // Narrative builders
    private function bestSchoolsNarrative(string $type, string $city, string $year): string
    {
        return "<p>Memilih {$type} terbaik di {$city} bukan hanya soal akreditasi, tetapi kecocokan visi pendidikan dengan nilai keluarga. Tahun {$year} membawa lanskap pendidikan yang lebih kompetitif — sekolah berlomba meningkatkan kurikulum, fasilitas, hingga integrasi teknologi.</p><p>Faktor pertimbangan utama: akreditasi BAN-S/M, kualitas guru, kurikulum (Merdeka, K13, atau internasional), fasilitas (laboratorium, perpustakaan, lapangan), prestasi siswa, dan biaya total (SPP + uang masuk + seragam).</p><p>Untuk {$type} di {$city}, kami merekomendasikan kunjungan langsung sebelum mendaftar — perhatikan suasana, keramahan staf, dan kebersihan lingkungan. Bicara dengan orang tua siswa eksisting untuk perspektif jujur.</p>";
    }

    private function bestSchoolsFaq(string $type, string $city, string $year): array
    {
        return [
            ["Apa kriteria {$type} terbaik di {$city}?", "Akreditasi A, kualitas guru bersertifikat, fasilitas lengkap, kurikulum modern, prestasi akademik & non-akademik, dan komunitas orang tua yang aktif."],
            ['Bagaimana cara mendaftar PPDB online?', 'Sekolah-sekolah ini umumnya menerima pendaftaran online via portal masing-masing. Siapkan dokumen: KK, akta lahir, rapor, foto, dan surat rekomendasi (jika ada).'],
            ["Berapa biaya rata-rata {$type} terbaik di {$city}?", "Bervariasi mulai dari SPP gratis (negeri) hingga Rp 12 juta/bulan (swasta favorit). Plus uang pangkal Rp 5-50 juta sekali bayar."],
            ['Apakah ada open house atau trial day?', 'Mayoritas sekolah favorit mengadakan open house Januari-Maret. Beberapa membuka trial class untuk calon siswa.'],
        ];
    }

    private function tuitionNarrative(string $type, string $city): string
    {
        return "<p>Biaya {$type} di {$city} sangat bervariasi — dari gratis (sekolah negeri yang ditanggung BOS) hingga puluhan juta per bulan untuk sekolah internasional. Memahami struktur biaya total adalah langkah penting dalam perencanaan keuangan keluarga.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Komponen Biaya</h3><ul class='space-y-2 font-serif'><li><strong>Uang pangkal / pendaftaran</strong> — sekali bayar saat masuk, Rp 0 (negeri) hingga Rp 50 juta+ (internasional)</li><li><strong>SPP bulanan</strong> — Rp 0 hingga Rp 40 juta/bulan</li><li><strong>Seragam</strong> — Rp 500rb-3jt</li><li><strong>Buku & alat tulis</strong> — Rp 1-5jt per tahun</li><li><strong>Ekstrakurikuler intensif</strong> — Rp 200rb-2jt per bulan</li><li><strong>Asrama (jika boarding)</strong> — Rp 1-15jt per bulan</li></ul><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Tips Mengelola Biaya</h3><p>Manfaatkan beasiswa (banyak sekolah swasta menyediakan), program cicilan uang pangkal, asuransi pendidikan, dan tabungan terjadwal. Hitung total cost of education sampai SMA selesai sebelum memutuskan.</p>";
    }

    private function curriculumNarrative(string $name, string $title): string
    {
        $bodies = [
            'merdeka' => "<p>Kurikulum Merdeka adalah kurikulum yang diluncurkan Kemendikbud-Ristek pada 2022, menggantikan secara bertahap K13. Filosofinya: pembelajaran yang lebih relevan, mendalam, dan menyenangkan.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Karakteristik</h3><ul class='space-y-2 font-serif'><li><strong>Pembelajaran berbasis projek</strong> — siswa mengerjakan proyek lintas mata pelajaran</li><li><strong>Profil Pelajar Pancasila</strong> — fokus pada 6 dimensi karakter</li><li><strong>Capaian Pembelajaran (CP)</strong> menggantikan KI-KD yang lebih ringkas</li><li><strong>Asesmen formatif & sumatif</strong> — ada P5 (Projek Penguatan Profil Pelajar Pancasila)</li><li><strong>Diferensiasi</strong> — guru menyesuaikan dengan kebutuhan tiap siswa</li></ul>",
            'k13'     => "<p>Kurikulum 2013 (K13) adalah kurikulum nasional yang diberlakukan sejak 2013-2014, dengan revisi pada 2017. Mengusung pendekatan saintifik dan tematik integratif.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Karakteristik</h3><ul class='space-y-2 font-serif'><li><strong>Pendekatan saintifik</strong> — mengamati, menanya, mencoba, menalar, mengkomunikasikan</li><li><strong>Kompetensi Inti & Dasar (KI-KD)</strong> sebagai standar</li><li><strong>Penilaian autentik</strong> — sikap, pengetahuan, keterampilan</li><li><strong>Tematik integratif</strong> di SD</li></ul>",
            'cambridge' => "<p>Cambridge International adalah program internasional dari University of Cambridge, terdiri dari Cambridge Primary, Lower Secondary, IGCSE, AS & A Level. Diakui di lebih dari 160 negara dengan ribuan universitas top dunia.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Jenjang</h3><ul class='space-y-2 font-serif'><li><strong>Primary (5-11 tahun)</strong> — fondasi literasi, matematika, sains</li><li><strong>Lower Secondary (11-14)</strong> — penguatan core subjects</li><li><strong>IGCSE (14-16)</strong> — setara SMP-SMA awal, ujian internasional</li><li><strong>AS & A Level (16-19)</strong> — setara universitas tahun pertama</li></ul>",
            'ib'      => "<p>International Baccalaureate (IB) adalah program internasional yang menekankan pembelajaran holistik, critical thinking, dan international-mindedness.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>4 Program IB</h3><ul class='space-y-2 font-serif'><li><strong>PYP (3-12 tahun)</strong> — Primary Years Programme, inquiry-based</li><li><strong>MYP (11-16)</strong> — Middle Years Programme, 8 subject groups</li><li><strong>DP (16-19)</strong> — Diploma Programme, 6 subjects + EE + TOK + CAS</li><li><strong>CP</strong> — Career-related Programme</li></ul>",
            'montessori' => "<p>Montessori adalah pendekatan pendidikan yang dikembangkan Maria Montessori (Italia, 1907). Berlandaskan pada keyakinan bahwa anak adalah pembelajar alami yang membutuhkan lingkungan terpersiapkan.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Prinsip Inti</h3><ul class='space-y-2 font-serif'><li><strong>Prepared environment</strong> — ruang kelas dirancang untuk eksplorasi mandiri</li><li><strong>Mixed-age classroom</strong> — anak usia berbeda belajar bersama</li><li><strong>Auto-education</strong> — anak memilih aktivitasnya sendiri</li><li><strong>Material Montessori</strong> — alat belajar konkret yang self-correcting</li></ul>",
            'charlotte-mason' => "<p>Charlotte Mason adalah pendekatan klasik dari Inggris (akhir 1800-an), populer di komunitas homeschool. Filosofi: <em>education is an atmosphere, a discipline, a life</em>.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Karakteristik</h3><ul class='space-y-2 font-serif'><li><strong>Living books</strong> — buku berkualitas tinggi, bukan textbook</li><li><strong>Narration</strong> — anak menceritakan kembali apa yang dibaca</li><li><strong>Nature study</strong> — observasi alam langsung</li><li><strong>Short lessons</strong> — sesi belajar singkat & fokus</li><li><strong>Habit training</strong> — pembentukan kebiasaan baik</li></ul>",
            'diniyah' => "<p>Kurikulum Diniyah adalah kurikulum keagamaan Islam yang umum diterapkan di pesantren, madrasah, dan sekolah Islam terpadu. Diperkuat dengan kurikulum nasional untuk lulusan tetap mendapat ijazah formal.</p><h3 class='font-display text-xl ink-primary mt-6 mb-3'>Mata Pelajaran Khas</h3><ul class='space-y-2 font-serif'><li><strong>Tahfidz Al-Quran</strong> — hafalan dengan target tertentu (5 juz, 10 juz, 30 juz)</li><li><strong>Bahasa Arab</strong> — nahwu, shorof, balaghah</li><li><strong>Fiqih</strong> — hukum-hukum Islam</li><li><strong>Aqidah Akhlak</strong> — keyakinan dan perilaku</li><li><strong>Tarikh Islam</strong> — sejarah Islam</li><li><strong>Hadits & Tafsir</strong> — sumber hukum Islam</li></ul>",
        ];
        return $bodies[$name] ?? "<p>{$title} adalah salah satu pendekatan kurikulum yang populer di Indonesia. Pelajari karakteristik, kelebihan, dan kekurangannya untuk menentukan kecocokan dengan anak Anda.</p>";
    }

    private function smaMajorNarrative(string $name, string $title): string
    {
        $bodies = [
            'ipa' => "<p>Jurusan IPA mempersiapkan siswa untuk karir di bidang sains, teknologi, kedokteran, dan rekayasa. Mata pelajaran inti: Matematika Lanjutan, Fisika, Kimia, Biologi.</p><p>Prospek kuliah luas: kedokteran, farmasi, teknik (sipil, mesin, elektro, informatika), MIPA murni, pertanian, peternakan, ilmu komputer. Banyak universitas memberi prioritas ke jurusan IPA untuk hampir semua program studi.</p>",
            'ips' => "<p>Jurusan IPS mempersiapkan siswa untuk karir di bidang sosial, ekonomi, hukum, dan humaniora. Mata pelajaran inti: Ekonomi, Geografi, Sejarah, Sosiologi.</p><p>Prospek kuliah: hukum, ekonomi, manajemen, akuntansi, hubungan internasional, psikologi, komunikasi, jurnalistik, sastra, pendidikan. Jurusan ini kuat untuk yang ingin berkarir di bisnis, pemerintahan, atau sektor sosial.</p>",
            'bahasa' => "<p>Jurusan Bahasa fokus pada penguasaan bahasa dan budaya — Bahasa Indonesia, Inggris, plus bahasa pilihan (Jepang, Mandarin, Jerman, Prancis, Korea, dll). Plus Antropologi.</p><p>Prospek kuliah: sastra, linguistik, hubungan internasional, jurnalistik, penerjemah, pariwisata, pendidikan bahasa. Cocok untuk yang punya passion bahasa dan ingin karir global.</p>",
            'agama' => "<p>Jurusan Keagamaan fokus pada pendalaman agama dengan kurikulum diniyah formal — Aqidah Akhlak, Fiqih, Quran Hadits, Bahasa Arab, Sejarah Kebudayaan Islam (untuk MA Islam).</p><p>Prospek kuliah: Tafsir, Hadits, Fiqih, Pendidikan Agama, Hukum Islam, Ekonomi Syariah. Banyak lulusan melanjutkan ke universitas Islam top di dalam dan luar negeri (Al-Azhar, Madinah, dsb).</p>",
        ];
        return $bodies[$name] ?? "<p>Jurusan {$title} di SMA memiliki karakteristik dan prospek tersendiri.</p>";
    }
    /**
     * /sitemap.xml — generated dynamically
     */
    public function sitemap(): \Illuminate\Http\Response
    {
        $urls   = [];
        $year   = now()->year;
        $cities = array_keys($this->cityProfiles);

        $urls[] = ['loc' => url('/'), 'priority' => '1.0'];
        $urls[] = ['loc' => url('/docs'), 'priority' => '0.7'];
        foreach (['admin','parent','student','teacher','super-admin','developer'] as $role) {
            $urls[] = ['loc' => url("/docs/{$role}"), 'priority' => '0.6'];
        }

        try {
            $schools = School::where('is_active', true)->get();
            foreach ($schools as $s) {
                $city = $s->settings['city'] ?? null;
                if ($city) {
                    $urls[] = ['loc' => url("/best-schools-{$city}-{$year}"), 'priority' => '0.8'];
                    $urls[] = ['loc' => url("/ppdb/{$city}"), 'priority' => '0.9'];
                }
                $urls[] = ['loc' => url("/alternatives-to-{$s->subdomain}"), 'priority' => '0.7'];
            }
        } catch (\Throwable $e) {
            // table not yet migrated — skip
        }

        // Extended pSEO permutations
        $schoolTypes = ['sd', 'smp', 'sma', 'smk', 'tk', 'pesantren', 'internasional'];
        foreach ($cities as $city) {
            foreach ($schoolTypes as $type) {
                $urls[] = ['loc' => url("/best-{$type}-schools-in-{$city}-{$year}"), 'priority' => '0.7'];
                $urls[] = ['loc' => url("/biaya-spp-{$type}-{$city}"), 'priority' => '0.7'];
            }
            $urls[] = ['loc' => url("/sekolah-internasional-{$city}"), 'priority' => '0.7'];
            $urls[] = ['loc' => url("/sekolah-asrama-{$city}"), 'priority' => '0.7'];
            $urls[] = ['loc' => url("/sekolah-akreditasi-a-{$city}"), 'priority' => '0.7'];
            foreach (['islam', 'katolik', 'kristen'] as $religion) {
                $urls[] = ['loc' => url("/sekolah-{$religion}-{$city}"), 'priority' => '0.6'];
            }
        }

        foreach (['merdeka','k13','cambridge','ib','montessori','charlotte-mason','diniyah'] as $curr) {
            $urls[] = ['loc' => url("/kurikulum-{$curr}"), 'priority' => '0.6'];
        }
        foreach (['ipa','ips','bahasa','agama'] as $major) {
            $urls[] = ['loc' => url("/jurusan-sma-{$major}"), 'priority' => '0.6'];
        }
        foreach (['rpl','tkj','akuntansi','tata-boga','multimedia','farmasi','keperawatan','otomotif'] as $major) {
            $urls[] = ['loc' => url("/jurusan-smk-{$major}"), 'priority' => '0.6'];
        }
        foreach (['prestasi','kurang-mampu','tahfidz','olahraga','seni','akademik'] as $bea) {
            $urls[] = ['loc' => url("/beasiswa-{$bea}-{$year}"), 'priority' => '0.6'];
        }

        $popularCities = ['jakarta','bandung','surabaya','yogyakarta','medan'];
        foreach ($popularCities as $city) {
            foreach (['matematika','bahasa-inggris','fisika','kimia','biologi','agama','tik'] as $subject) {
                $urls[] = ['loc' => url("/lowongan-guru-{$subject}-{$city}"), 'priority' => '0.5'];
            }
            foreach (['pramuka','paskibra','rohis','english-club','robotik','musik','basket','futsal'] as $eks) {
                $urls[] = ['loc' => url("/ekstrakurikuler-{$eks}-{$city}"), 'priority' => '0.5'];
            }
        }

        // Blog posts
        if (class_exists(\App\Models\BlogPost::class)) {
            $posts = \App\Models\BlogPost::published()->select('slug', 'updated_at')->get();
            foreach ($posts as $post) {
                $urls[] = ['loc' => url("/blog/{$post->slug}"), 'priority' => '0.7', 'lastmod' => $post->updated_at->toAtomString()];
            }
        }

        try {
            $campaigns = DonationCampaign::withoutGlobalScopes()
                ->where('is_public', true)->where('status', 'active')->with('school')->get();
            foreach ($campaigns as $c) {
                $urls[] = ['loc' => url("/donate/{$c->school->subdomain}/{$c->slug}"), 'priority' => '0.6'];
            }
        } catch (\Throwable $e) {
            // table not yet migrated
        }

        try {
            $events = SchoolEvent::withoutGlobalScopes()
                ->where('is_published', true)->where('starts_at', '>=', now())->with('school')->get();
            foreach ($events as $e) {
                $urls[] = ['loc' => url("/events/{$e->school->subdomain}/{$e->slug}"), 'priority' => '0.6'];
            }
        } catch (\Throwable $e) {
            // table not yet migrated
        }

        try {
            if (class_exists(\App\Models\BlogPost::class)) {
                $blogPosts = \App\Models\BlogPost::published()->select('slug', 'updated_at')->get();
                foreach ($blogPosts as $post) {
                    $urls[] = ['loc' => route('blog.show', $post->slug), 'priority' => '0.7', 'lastmod' => $post->updated_at->toAtomString()];
                }
                $urls[] = ['loc' => route('blog.index'), 'priority' => '0.7'];
                if (class_exists(\App\Models\BlogCategory::class)) {
                    $categories = \App\Models\BlogCategory::select('slug')->get();
                    foreach ($categories as $cat) {
                        $urls[] = ['loc' => route('blog.category', $cat->slug), 'priority' => '0.6'];
                    }
                }
            }
        } catch (\Throwable $e) {
            // table not yet migrated
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$u['loc']}</loc>\n";
            if (!empty($u['lastmod'])) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): \Illuminate\Http\Response
    {
        $content = "User-agent: *\n"
            . "Allow: /\n"
            . "Allow: /docs\n"
            . "Allow: /blog\n"
            . "Allow: /pricing\n"
            . "Allow: /daftar\n"
            . "Allow: /indexnow-key.txt\n"
            . "Allow: /best-schools-*\n"
            . "Allow: /best-*-schools-in-*\n"
            . "Allow: /alternatives-to-*\n"
            . "Allow: /compare/*\n"
            . "Allow: /ppdb/*\n"
            . "Allow: /sekolah-*\n"
            . "Allow: /biaya-spp-*\n"
            . "Allow: /kurikulum-*\n"
            . "Allow: /jurusan-sma-*\n"
            . "Allow: /jurusan-smk-*\n"
            . "Allow: /lowongan-guru-*\n"
            . "Allow: /beasiswa-*\n"
            . "Allow: /ekstrakurikuler-*\n"
            . "Allow: /donate/*\n"
            . "Allow: /events/*\n"
            . "Allow: /alumni/*\n"
            . "Allow: /blog/*\n"
            . "Disallow: /admin/\n"
            . "Disallow: /super/\n"
            . "Disallow: /api/\n"
            . "Disallow: /portal/\n"
            . "\n"
            . "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content)->header('Content-Type', 'text/plain');
    }
}
