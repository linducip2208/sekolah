@extends('elite.layout')

@section('title', $platform['app_name'] . ' — ' . ($platform['tagline'] ?? ''))
@section('description', $platform['description'] ?? '')

@push('head')
{{-- Scroll reveal animations --}}
<style>
    .reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s ease, transform .7s cubic-bezier(.16, 1, .3, 1); }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .1s; }
    .reveal-delay-2 { transition-delay: .2s; }
    .reveal-delay-3 { transition-delay: .3s; }
    .reveal-delay-4 { transition-delay: .4s; }
    .card-lift { transition: transform .35s ease, box-shadow .35s ease; }
    .card-lift:hover { transform: translateY(-6px); box-shadow: 0 24px 48px -12px rgba(0,0,0,.15); }
    @keyframes floatSlow { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
    .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
</style>
@endpush

@push('jsonld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'EducationalOrganization',
    'name'     => $platform['app_name'],
    'url'      => url('/'),
    'logo'     => $platform['logo_url'] ?? null,
    'image'    => $platform['hero_image_url'] ?? null,
    'foundingDate' => $platform['established_year'] ?? null,
    'slogan'   => $platform['motto_translated'] ?? null,
    'description' => $platform['description'] ?? null,
    'address'  => [
        '@type' => 'PostalAddress',
        'streetAddress' => $platform['address_line1'] ?? '',
        'addressLocality' => $platform['address_line2'] ?? '',
    ],
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => $platform['contact_phone'] ?? '',
        'email' => $platform['contact_email'] ?? '',
        'contactType' => 'admissions',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('header')
@include('elite.partials.header')
@endsection

@section('content')

{{-- ============================================================
     HERO — editorial split, large kicker, serif title, motto rule
============================================================ --}}
<section class="relative paper">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-12 gap-10 py-16 lg:py-24 items-center">
        <div class="lg:col-span-7">
            <div class="elite-kicker mb-6">{{ $platform['hero_kicker'] ?? 'Founded MDCCCXC' }}</div>
            <h1 class="elite-h1 text-5xl sm:text-6xl lg:text-7xl ink-primary mb-7">
                {!! $platform['hero_title'] ?? 'A Tradition of Excellence,<br>A Future of Possibility.' !!}
            </h1>
            <div class="elite-rule mb-7"></div>
            <p class="elite-lead max-w-2xl mb-10">
                {{ $platform['hero_subtitle'] ?? '' }}
            </p>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('admin.login') }}" class="btn-elite">Penerimaan</a>
                <a href="/docs" class="btn-elite-ghost">Buku Panduan</a>
                <a href="#program" class="text-sm font-semibold tracking-widest uppercase ink-primary hover:ink-accent ml-2 transition" style="letter-spacing:.2em;">Lihat Program ↓</a>
            </div>

            <div class="mt-14 grid grid-cols-3 gap-6 max-w-xl border-t border-rule pt-8">
                <div>
                    <div class="elite-h2 text-3xl ink-primary">{{ \Carbon\Carbon::now()->year - (int)($platform['established_year'] ?? 1890) }}</div>
                    <div class="elite-kicker mt-1" style="color: var(--c-muted);">Tahun Berkarya</div>
                </div>
                <div>
                    <div class="elite-h2 text-3xl ink-primary">45+</div>
                    <div class="elite-kicker mt-1" style="color: var(--c-muted);">Modul Akademi</div>
                </div>
                <div>
                    <div class="elite-h2 text-3xl ink-primary">∞</div>
                    <div class="elite-kicker mt-1" style="color: var(--c-muted);">Cabang Sekolah</div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5 relative">
            <div class="deco-frame">
                <div class="aspect-[4/5] overflow-hidden bg-[#1a2a44] relative">
                    @if(!empty($platform['hero_image_url']))
                        <img src="{{ $platform['hero_image_url'] }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{-- editorial illustration fallback --}}
                        <svg viewBox="0 0 400 500" class="w-full h-full" preserveAspectRatio="xMidYMid slice">
                            <defs>
                                <linearGradient id="bgGrad" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#0b1d3a"/>
                                    <stop offset="100%" stop-color="#1a2a4d"/>
                                </linearGradient>
                                <pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <circle cx="2" cy="2" r="1" fill="#b8860b" opacity=".3"/>
                                </pattern>
                            </defs>
                            <rect width="400" height="500" fill="url(#bgGrad)"/>
                            <rect width="400" height="500" fill="url(#dots)"/>
                            <g transform="translate(200,180)" fill="#b8860b" opacity=".95">
                                <path d="M0-90 L70-50 L70 30 L0 70 L-70 30 L-70 -50 Z" fill="none" stroke="#b8860b" stroke-width="2"/>
                                <text text-anchor="middle" y="0" font-family="Playfair Display, serif" font-size="48" font-weight="700">A</text>
                                <text text-anchor="middle" y="50" font-family="Cormorant Garamond, serif" font-style="italic" font-size="14" letter-spacing="3">FLOREAT</text>
                            </g>
                            <text x="200" y="380" text-anchor="middle" font-family="Playfair Display, serif" font-size="22" fill="#f8f5ee" letter-spacing="6">{{ strtoupper($platform['app_name'] ?? 'SIKAD PRO') }}</text>
                            <line x1="100" y1="410" x2="300" y2="410" stroke="#b8860b" stroke-width="1"/>
                            <text x="200" y="440" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="14" fill="#b8860b">Est. {{ $platform['established_year'] ?? '1890' }}</text>
                        </svg>
                    @endif
                </div>
            </div>
            <div class="absolute -bottom-6 -left-6 hidden md:block paper px-6 py-4 border border-[var(--c-accent)]" style="background: var(--c-paper);">
                <div class="font-script italic text-2xl ink-secondary">"{{ $platform['motto_latin'] ?? 'Floreat Schola' }}"</div>
                <div class="elite-kicker mt-1" style="color: var(--c-muted);">{{ $platform['motto_translated'] ?? '' }}</div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     PILLARS — Akademi / Karakter / Penerimaan triad
============================================================ --}}
<section class="bg-[var(--c-primary)] text-white" id="fitur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="ornament-center"></div>
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">Tiga Pilar</div>
            <h2 class="elite-h2 text-4xl sm:text-5xl text-white mb-5">Tradisi yang Membentuk, Inovasi yang Membebaskan</h2>
            <div class="elite-rule mx-auto" style="color: var(--c-accent);"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-px" style="background: rgba(255,255,255,.12);">
            @foreach([
                ['Academia','Untuk Sekolah & Administrator','Pengelolaan akademik, keuangan, dan operasional dengan presisi pedagogis sebuah institusi terhormat.','admin.login','Masuk Administrator'],
                ['Familia','Untuk Orang Tua & Wali','Akses transparan ke perkembangan, prestasi, dan korespondensi anak — dalam genggaman.','portal.invoices','Portal Keluarga'],
                ['Magisterium','Untuk Operator Platform','Tata kelola SaaS multi-cabang, mengikuti standar etika dan kerahasiaan data tertinggi.','super.login','Operator Platform'],
            ] as [$name, $label, $desc, $route, $cta])
                <div class="bg-[var(--c-primary)] p-10 hover:bg-white/5 transition group">
                    <div class="crest-mark mb-6" style="border-color: var(--c-accent); color: var(--c-accent);">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                    <div class="elite-kicker mb-3" style="color: var(--c-accent);">{{ $label }}</div>
                    <h3 class="elite-h3 text-3xl text-white mb-3">{{ $name }}</h3>
                    <p class="font-serif text-lg leading-relaxed text-white/75 mb-6">{{ $desc }}</p>
                    <a href="{{ route($route) }}" class="inline-flex items-center text-sm font-semibold uppercase tracking-widest hover:ink-accent transition" style="color: var(--c-accent); letter-spacing:.2em;">
                        {{ $cta }}
                        <span class="ml-2 transition group-hover:translate-x-1">→</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     PROGRAM CATALOGUE — module list as elegant catalog
============================================================ --}}
<section class="paper" id="program">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
        <div class="grid lg:grid-cols-12 gap-12 mb-16">
            <div class="lg:col-span-5">
                <div class="elite-kicker mb-3">Curriculum Vitae</div>
                <h2 class="elite-h2 text-4xl lg:text-5xl ink-primary leading-tight">Sebuah Kurikulum Manajerial yang Komprehensif</h2>
            </div>
            <div class="lg:col-span-7 lg:pt-4">
                <div class="elite-rule mb-5"></div>
                <p class="elite-lead">Empat puluh lima modul yang ditenun bersama dengan presisi — dari penerimaan murid baru hingga pelacakan alumni — dirancang untuk mengabdi pada keunggulan jangka panjang setiap institusi.</p>
            </div>
        </div>

        <div id="modul" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['Foundation','Multi-tenant · Lisensi · Otentikasi · RBAC','I'],
                ['Akademi Inti','Tahun ajaran · Kelas · Mata pelajaran · Jadwal · Absensi · Ujian · Raport','II'],
                ['Online Classroom','Materi pelajaran · Tugas · Submission · Koreksi terotomasi','III'],
                ['Keuangan','Invoice · Tagihan · Payroll guru · Langganan SaaS','IV'],
                ['Payment Gateway','Dynamic — Midtrans · Xendit · QRIS · VA · E-wallet (BYOK)','V'],
                ['Fasilitas','Perpustakaan · Asrama · Transportasi (bus tracking)','VI'],
                ['Komunikasi','Pengumuman · Chat · Notifikasi FCM/email/SMS','VII'],
                ['Mobile App','Flutter untuk orang tua, siswa, guru','VIII'],
                ['SaaS Panel','Super admin tenant management & billing','IX'],
                ['Student Lifecycle','PPDB online · ID gate · UKS · BP/BK','X'],
                ['Teaching Tools','RPP · Kantin cashless · Mode pesantren · AI assistant · Live class · Bank soal','XI'],
                ['Engagement','Donasi · Alumni · Achievement · Beasiswa · Karir · Event · Ekstrakurikuler','XII'],
                ['Operations','Sinkronisasi Dapodik · Visitor · Inventaris · Yayasan dashboard · Learning analytics','XIII'],
            ] as [$name, $desc, $roman])
                <div class="elite-card p-7 group">
                    <div class="flex items-baseline justify-between mb-4">
                        <span class="font-display text-2xl ink-accent">{{ $roman }}</span>
                        <span class="elite-kicker" style="color: var(--c-muted);">Capitulum</span>
                    </div>
                    <h3 class="elite-h3 text-2xl ink-primary mb-2">{{ $name }}</h3>
                    <div class="w-10 h-px bg-[var(--c-accent)] mb-3 group-hover:w-16 transition-all"></div>
                    <p class="font-serif text-base leading-relaxed text-gray-700">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     QUOTE / VALUE — large editorial quote
============================================================ --}}
<section class="paper border-y border-rule">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-24 text-center">
        <div class="quote-mark mx-auto">"</div>
        <p class="font-display text-3xl sm:text-4xl ink-primary leading-snug mb-8" style="font-weight: 500;">
            Sebuah sistem yang tidak hanya mengelola, tetapi mengangkat — di mana setiap detail teknis adalah penghormatan kepada siswa, guru, dan keluarga yang mempercayakan masa depan mereka kepadanya.
        </p>
        <div class="elite-rule mx-auto mb-4"></div>
        <div class="elite-kicker">Filosofi {{ $platform['app_name'] ?? 'Sikad Pro' }}</div>
    </div>
</section>

{{-- ============================================================
     BYOK — dual column "Stat. Resp."
============================================================ --}}
<section class="bg-[var(--c-primary)] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24 grid lg:grid-cols-2 gap-16 items-center">
        <div>
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">Statuta Responsabilitatis</div>
            <h2 class="elite-h2 text-4xl sm:text-5xl text-white mb-6">Sovereignty atas Data &amp; Vendor.</h2>
            <div class="elite-rule mb-6" style="color: var(--c-accent);"></div>
            <p class="font-serif text-xl leading-relaxed text-white/80 mb-8">
                Sebuah institusi terhormat tidak menyerahkan kunci pada vendor manapun. Sikad Pro menganut prinsip <em style="color: var(--c-accent);">Bring Your Own Keys</em> — setiap integrasi pihak ketiga dapat ditukar tanpa intervensi pengembang.
            </p>
            <ul class="space-y-3 font-serif text-lg text-white/85">
                @foreach([
                    'Payment — Midtrans, Xendit, QRIS, virtual account, transfer manual',
                    'AI — OpenAI, Anthropic, Gemini, DeepSeek, Ollama, vLLM',
                    'SMS / WhatsApp — bebas memilih penyedia',
                    'Storage — S3-compatible (AWS, Wasabi, R2, MinIO)',
                ] as $item)
                    <li class="flex items-start gap-3">
                        <span class="ink-accent text-2xl leading-none" style="color: var(--c-accent);">❦</span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="deco-frame" style="--c-accent: #b8860b;">
            <div class="bg-white/5 border border-white/10 p-8 backdrop-blur">
                <div class="elite-kicker mb-5" style="color: var(--c-accent);">Format-Based Adapter</div>
                <pre class="text-sm text-white/85 leading-relaxed overflow-x-auto" style="font-family: 'JetBrains Mono', ui-monospace, monospace;"><code><span class="text-white/40">// Tiga generic adapter, puluhan provider</span>

OpenAICompatibleAdapter
<span class="text-white/40">  // OpenAI · DeepSeek · Groq · Mistral
  // Together · Ollama · vLLM · Anyscale</span>

AnthropicFormatAdapter
<span class="text-white/40">  // Claude</span>

GeminiFormatAdapter
<span class="text-white/40">  // Google Gemini</span>

<span class="text-white/40">// Pilihan ada di tangan administrator:</span>
Translate     → <em style="color: var(--c-accent);">"User chooses"</em>
LessonSummary → <em style="color: var(--c-accent);">"User chooses"</em></code></pre>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ENROLLMENT — pricing as "houses" / "memberships"
============================================================ --}}
<section class="paper" id="harga">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="ornament-center"></div>
            <div class="elite-kicker mb-3">Penerimaan</div>
            <h2 class="elite-h2 text-4xl sm:text-5xl ink-primary mb-5">Tiga Tingkatan, Satu Standar Keunggulan</h2>
            <p class="elite-lead">Pilihlah tingkatan yang sesuai dengan ukuran institusi Anda. Setiap tingkatan menerima dedikasi yang sama.</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            @foreach([
                ['Scholaris','Rp 0','Untuk institusi yang baru memulai',['Hingga 100 siswa','Modul akademik dasar','Email support','1 administrator'],false,'I'],
                ['Magister','Rp 1.500.000','Untuk institusi yang berkembang',['Hingga 1.000 siswa','45+ modul lengkap','PPDB online & gateway','Aplikasi mobile','Priority support'],true,'II'],
                ['Praepositus','Custom','Untuk yayasan & pesantren',['Tanpa batas siswa','Multi-cabang / yayasan','Dapodik & integrasi khusus','SLA & dedicated CS','Opsi on-premise'],false,'III'],
            ] as [$name, $price, $tag, $perks, $featured, $roman])
                <div class="elite-card p-10 relative {{ $featured ? '' : '' }}" @if($featured) style="border: 2px solid var(--c-accent); position: relative;" @endif>
                    @if($featured)
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-5 py-1 elite-kicker text-white" style="background: var(--c-accent);">Recommended</div>
                    @endif
                    <div class="text-center pb-7 border-b border-rule">
                        <div class="font-display text-3xl ink-accent mb-2">{{ $roman }}</div>
                        <h3 class="elite-h2 text-3xl ink-primary">{{ $name }}</h3>
                        <p class="font-serif italic text-base mt-2" style="color: var(--c-muted);">{{ $tag }}</p>
                    </div>
                    <div class="text-center py-7 border-b border-rule">
                        <div class="elite-h1 text-4xl ink-primary">{{ $price }}</div>
                        <div class="elite-kicker mt-2" style="color: var(--c-muted);">per bulan</div>
                    </div>
                    <ul class="space-y-3 py-7 font-serif text-base text-gray-800">
                        @foreach($perks as $perk)
                            <li class="flex items-start gap-3"><span class="ink-accent">❦</span>{{ $perk }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('admin.login') }}" class="block w-full text-center {{ $featured ? 'btn-elite' : 'btn-elite-ghost' }}">Pilih Tingkat {{ $roman }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     FAQ — accordion with elite frame
============================================================ --}}
<section class="bg-[var(--c-primary)] text-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-24">
        <div class="text-center mb-12">
            <div class="ornament-center"></div>
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">Pertanyaan Lazim</div>
            <h2 class="elite-h2 text-4xl text-white">Quaestiones &amp; Responsiones</h2>
            <div class="elite-rule mx-auto mt-5" style="color: var(--c-accent);"></div>
        </div>
        <div class="space-y-3">
            @foreach([
                ['Apa itu Sikad Pro?','Sebuah platform manajemen sekolah multi-tenant berbasis cloud — mencakup akademik, keuangan, PPDB, perpustakaan, transportasi, kantin cashless, dashboard yayasan, hingga AI assistant.'],
                ['Apakah cocok untuk pesantren / madrasah?','Ya. Tersedia mode khusus dengan modul hafalan Al-Quran, jadwal sholat, kurikulum diniyah, serta pengelolaan asrama santri.'],
                ['Apakah ada aplikasi mobile?','Ya. Aplikasi Flutter tersedia untuk orang tua, siswa, dan guru — Android dan iOS — dengan push notification realtime.'],
                ['Bisakah saya menggunakan payment gateway sendiri?','Ya, sepenuhnya. Anda input API key Midtrans, Xendit, atau provider apapun di admin panel — bisa diganti kapan saja.'],
                ['Bagaimana keamanan data?','Isolasi multi-tenant per school_id, enkripsi at-rest untuk semua key, role-based access control, audit log, dan backup harian otomatis.'],
                ['Apakah terintegrasi dengan Dapodik?','Ya. Modul Dapodik Sync untuk export/import data siswa & guru sesuai format Kemendikbud.'],
            ] as [$q, $a])
                <details class="bg-white/5 border border-white/10 group" style="font-family: 'Inter', sans-serif;">
                    <summary class="flex items-center justify-between cursor-pointer py-5 px-6 list-none">
                        <span class="elite-h3 text-xl text-white">{{ $q }}</span>
                        <span class="ink-accent text-2xl leading-none transition group-open:rotate-45" style="color: var(--c-accent);">+</span>
                    </summary>
                    <div class="px-6 pb-6 font-serif text-lg leading-relaxed text-white/80">{{ $a }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     DEMO ACCOUNTS — sandbox access
============================================================ --}}
<section class="paper" id="demo">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-24">
        <div class="text-center mb-12">
            <div class="ornament-center"></div>
            <div class="elite-kicker mb-3">Akses Sandbox</div>
            <h2 class="elite-h2 text-4xl sm:text-5xl ink-primary mb-5">Akun Demo — Coba Langsung</h2>
            <div class="elite-rule mx-auto mb-5"></div>
            <p class="elite-lead max-w-2xl mx-auto">Jelajahi panel administrator, portal orang tua, dan dashboard siswa tanpa perlu mendaftar.</p>
        </div>

        <div class="elite-card p-4 sm:p-6 overflow-x-auto reveal">
            <table class="w-full text-sm table-elite">
                <thead>
                    <tr>
                        <th class="text-left">Role</th>
                        <th class="text-left">Email</th>
                        <th class="text-left">Password</th>
                        <th class="text-left">Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Super Admin', 'super@sikadpro.app', 'SuperAdmin123!', route('super.login'), 'Kelola semua sekolah & platform'],
                        ['Administrator Sekolah', 'admin@sman1demo.sch.id', 'Admin123!', route('admin.login'), 'Kelola akademik, keuangan, siswa'],
                        ['Guru / Staff', 'guru1@sman1demo.sch.id', 'Guru123!', route('admin.login'), 'Input nilai, absensi, materi'],
                        ['Orang Tua / Wali', 'wali1@sman1demo.sch.id', 'Wali123!', route('portal.dashboard'), 'Pantau anak: nilai, absensi, SPP'],
                        ['Siswa', 'siswa0_0@sman1demo.sch.id', 'Siswa123!', route('student.dashboard'), 'Lihat jadwal, tugas, nilai'],
                    ] as $i => [$role, $email, $pass, $link, $scope])
                        <tr>
                            <td data-label="Role" class="font-semibold">{{ $role }}</td>
                            <td data-label="Email" class="font-mono text-xs">{{ $email }}</td>
                            <td data-label="Password" class="font-mono text-xs">{{ $pass }}</td>
                            <td data-label="Akses">
                                <a href="{{ $link }}" class="text-xs uppercase tracking-widest font-semibold ink-accent hover:underline">{{ $scope }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('admin.login') }}" class="btn-elite reveal reveal-delay-1">Masuk ke Demo &rarr;</a>
        </div>
    </div>
</section>

{{-- ============================================================
     TAMPILAN APLIKASI — screenshot gallery
============================================================ --}}
<section class="bg-[var(--c-primary)] text-white" id="tampilan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-24">
        <div class="text-center mb-16">
            <div class="ornament-center"></div>
            <div class="elite-kicker mb-3" style="color: var(--c-accent);">Tampilan Aplikasi</div>
            <h2 class="elite-h2 text-4xl sm:text-5xl text-white mb-5">Antarmuka yang Elegan &amp; Fungsional</h2>
            <div class="elite-rule mx-auto mb-5" style="color: var(--c-accent);"></div>
            <p class="font-serif text-xl leading-relaxed text-white/75 max-w-3xl mx-auto">Dirancang dengan prinsip editorial — tipografi yang jernih, hierarki visual yang presisi, dan responsif di semua perangkat.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach([
                ['Dashboard Admin', 'Statistik real-time: siswa, staf, pendapatan, notifikasi. Command center operasional sekolah.'],
                ['Manajemen Siswa', 'CRUD lengkap: data diri, wali, kelas, asrama, transportasi. Import CSV massal.'],
                ['Keuangan & SPP', 'Struktur biaya, generate invoice massal, rekam pembayaran, laporan outstanding.'],
                ['Absensi Harian', 'Check-in per kelas, rekap bulanan, notifikasi ke orang tua via push/email.'],
                ['Ujian & Penilaian', 'Buat ujian, input nilai, kalkulasi otomatis grade, cetak raport PDF.'],
                ['PPDB Online', 'Formulir pendaftaran publik, verifikasi berkas, seleksi, pengumuman otomatis.'],
                ['Perpustakaan', 'Katalog buku, peminjaman, pengembalian, denda keterlambatan otomatis.'],
                ['Portal Orang Tua', 'Dashboard per anak: nilai, absensi, tagihan SPP, laporan harian, chat guru.'],
                ['Chat & Pengumuman', 'Notifikasi push, broadcast pengumuman, chat antar role dengan real-time.'],
            ] as $i => [$title, $desc])
                <div class="elite-card p-5 bg-white/5 border-white/10 card-lift reveal reveal-delay-{{ $i % 4 + 1 }}">
                    <div class="bg-[#0b1d3a] rounded mb-4 h-40 sm:h-44 flex items-center justify-center overflow-hidden border border-white/10">
                        <div class="text-center text-white/30">
                            <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-xs uppercase tracking-widest">Screenshot {{ $i + 1 }}</span>
                        </div>
                    </div>
                    <h3 class="elite-h3 text-xl text-white mb-2">{{ $title }}</h3>
                    <p class="font-serif text-base leading-relaxed text-white/70">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <p class="font-script italic text-lg text-white/60 mb-5">Tangkapan layar akan diperbarui setiap rilis untuk merefleksikan versi terkini.</p>
            <a href="/docs" class="btn-elite-ghost" style="border-color: rgba(255,255,255,.3); color: #fff;">Lihat Dokumentasi Lengkap &rarr;</a>
        </div>
    </div>
</section>

{{-- ============================================================
     INVITATION — visit / inquire
============================================================ --}}
<section class="paper">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-24 text-center">
        <div class="ornament-center"></div>
        <div class="elite-kicker mb-3">Visitatio Cordialis</div>
        <h2 class="elite-h1 text-5xl ink-primary mb-6">Kami Mengundang Anda untuk Berkunjung.</h2>
        <div class="elite-rule mx-auto mb-7"></div>
        <p class="elite-lead max-w-2xl mx-auto mb-10">
            Demonstrasi pribadi, sesi tanya-jawab dengan tim akademis, dan akses sandbox — tanpa biaya, tanpa kewajiban.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('admin.login') }}" class="btn-elite">Mulai Demo</a>
            @if(!empty($platform['whatsapp_link']))
                <a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="btn-elite-gold">Hubungi via WhatsApp</a>
            @endif
            <a href="mailto:{{ $platform['contact_email'] ?? '' }}" class="btn-elite-ghost">Kirim Korespondensi</a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(function(el) { observer.observe(el); });
})();
</script>
@endpush
