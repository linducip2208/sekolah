@extends('elite.layout')
@section('title', 'Daftar Sekolah Baru')

@section('header')@include('elite.partials.header')@endsection

@section('content')

<section class="paper">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-16">
        <nav class="elite-kicker mb-7" style="color: var(--c-muted);">
            <a href="/pricing" class="hover:ink-primary">Pricing</a>
            <span class="mx-2 ink-accent">/</span>
            <span>Pendaftaran</span>
        </nav>

        <div class="elite-kicker mb-2">Inscriptio Nova</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-3">Daftarkan Sekolah Anda</h1>
        <div class="elite-rule mb-7"></div>

        @if($errors->any())
            <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
                <ul class="list-disc list-inside font-serif text-sm text-red-800">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.subscription.submit') }}" class="bg-white border border-rule p-8 space-y-6">
            @csrf

            <div>
                <h2 class="elite-h3 text-xl ink-primary mb-1">1. Informasi Sekolah</h2>
                <div class="elite-rule mb-5"></div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="elite-kicker block mb-2">Nama Sekolah</label>
                        <input type="text" name="school_name" value="{{ old('school_name') }}" required
                               class="w-full border-2 border-rule px-4 py-3 font-serif">
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">Subdomain</label>
                        <div class="flex">
                            <input type="text" name="subdomain" value="{{ old('subdomain') }}" required
                                   pattern="[a-z0-9\-]+" minlength="3" maxlength="40"
                                   class="flex-1 border-2 border-rule px-4 py-3 font-mono lowercase"
                                   placeholder="namasekolah">
                            <span class="px-3 py-3 bg-gray-100 border-2 border-l-0 border-rule font-mono text-sm">.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">Alamat (opsional)</label>
                        <input type="text" name="address" value="{{ old('address') }}" maxlength="500"
                               class="w-full border-2 border-rule px-4 py-3 font-serif">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="elite-h3 text-xl ink-primary mb-1">2. Akun Administrator</h2>
                <div class="elite-rule mb-5"></div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="elite-kicker block mb-2">Nama Admin</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full border-2 border-rule px-4 py-3 font-serif">
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">Email Admin</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                               class="w-full border-2 border-rule px-4 py-3 font-serif">
                    </div>
                    <div>
                        <label class="elite-kicker block mb-2">No. HP/WA</label>
                        <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" maxlength="30"
                               class="w-full border-2 border-rule px-4 py-3 font-serif">
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 font-serif italic">Password akan diberikan oleh admin platform setelah pembayaran terverifikasi.</p>
            </div>

            <div>
                <h2 class="elite-h3 text-xl ink-primary mb-1">3. Pilih Paket</h2>
                <div class="elite-rule mb-5"></div>
                <div class="grid md:grid-cols-3 gap-3">
                    @foreach($plans as $plan)
                        <label class="block cursor-pointer">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                   @checked(old('plan_id', $selectedPlan?->id) == $plan->id)
                                   class="peer sr-only" required>
                            <div class="border-2 border-rule p-5 peer-checked:border-[var(--c-accent)] peer-checked:bg-[rgba(184,134,11,.06)] transition">
                                <div class="elite-h3 text-lg ink-primary mb-1">{{ $plan->name }}</div>
                                <div class="font-display text-2xl ink-primary mb-1">
                                    {{ $plan->price === 0 ? 'Gratis' : 'Rp'.number_format($plan->price/100, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">/bulan · {{ $plan->max_students ?: 'unlimited' }} siswa</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5">
                    <label class="elite-kicker block mb-2">Durasi Berlangganan</label>
                    <select name="billing_months" required class="border-2 border-rule px-4 py-3 font-serif">
                        <option value="1">1 bulan</option>
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12" selected>12 bulan (1 tahun) — direkomendasikan</option>
                    </select>
                </div>
            </div>

            <div class="pt-5 border-t border-rule">
                <button type="submit" class="btn-elite w-full" style="padding: 1rem;">Lanjut ke Pembayaran</button>
                <p class="text-xs text-center text-gray-500 mt-3 font-serif">Dengan mendaftar, Anda menyetujui syarat & ketentuan platform.</p>
            </div>
        </form>
    </div>
</section>

@endsection
