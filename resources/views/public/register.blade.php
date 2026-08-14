@extends('landing.layout')
@section('title', 'Daftar Sekolah Baru')
@section('description', 'Daftarkan sekolah Anda di Sikad Pro — lengkapi data sekolah, akun administrator, dan pilih paket.')

@section('content')
<section class="lp-section" style="background: var(--lp-bg);">
    <div class="lp-container max-w-4xl">
        <div class="mb-8">
            <p class="lp-kicker mb-3">Pendaftaran</p>
            <h1 class="lp-title text-4xl sm:text-5xl">Daftarkan Sekolah Anda</h1>
            <p class="lp-lead mt-3">Lengkapi data sekolah, akun administrator, lalu pilih paket. Aktivasi dilakukan setelah pembayaran terverifikasi.</p>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="mb-6 p-4 rounded-lg" style="background: var(--lp-accent-soft); border: 1px solid #fca5a5; color: #b91c1c;">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.subscription.submit') }}" class="lp-card lp-card-shadow p-8 space-y-8">
            @csrf

            <div>
                <h2 class="font-display font-bold text-xl mb-4" style="color: var(--lp-ink);">1. Informasi Sekolah</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label" for="school_name">Nama Sekolah <span class="req">*</span></label>
                        <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" required class="input">
                    </div>
                    <div>
                        <label class="label" for="subdomain">Subdomain <span class="req">*</span></label>
                        <div class="flex">
                            <input type="text" id="subdomain" name="subdomain" value="{{ old('subdomain') }}" required pattern="[a-z0-9\-]+" minlength="3" maxlength="40" class="input rounded-r-none font-mono lowercase" placeholder="namasekolah">
                            <span class="px-3 flex items-center border-y border-r text-sm" style="border-color: var(--lp-border); background: var(--lp-surface); color: var(--lp-muted);">.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="label" for="address">Alamat (opsional)</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" maxlength="500" class="input">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="font-display font-bold text-xl mb-4" style="color: var(--lp-ink);">2. Akun Administrator</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="label" for="admin_name">Nama Admin <span class="req">*</span></label>
                        <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required class="input">
                    </div>
                    <div>
                        <label class="label" for="admin_email">Email Admin <span class="req">*</span></label>
                        <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required class="input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="label" for="admin_phone">No. HP/WA</label>
                        <input type="text" id="admin_phone" name="admin_phone" value="{{ old('admin_phone') }}" maxlength="30" class="input">
                    </div>
                </div>
                <p class="form-hint mt-3">Password diberikan oleh admin platform setelah pembayaran terverifikasi.</p>
            </div>

            <div>
                <h2 class="font-display font-bold text-xl mb-4" style="color: var(--lp-ink);">3. Pilih Paket</h2>
                <div class="grid md:grid-cols-3 gap-3">
                    @foreach($plans as $plan)
                        <label class="block cursor-pointer">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" @checked(old('plan_id', $selectedPlan?->id) == $plan->id) class="peer sr-only" required>
                            <div class="border-2 p-5 rounded-lg transition peer-checked:border-[var(--lp-primary)] peer-checked:bg-[var(--lp-accent-soft)]" style="border-color: var(--lp-border);">
                                <div class="font-semibold" style="color: var(--lp-ink);">{{ $plan->name }}</div>
                                <div class="font-display text-2xl font-bold mt-1" style="color: var(--lp-ink);">{{ $plan->price === 0 ? 'Gratis' : 'Rp'.number_format($plan->price/100, 0, ',', '.') }}</div>
                                <div class="text-xs mt-0.5" style="color: var(--lp-muted);">/bulan · {{ $plan->max_students ?: 'unlimited' }} siswa</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5">
                    <label class="label" for="billing_months">Durasi Berlangganan</label>
                    <select id="billing_months" name="billing_months" required class="select">
                        <option value="1">1 bulan</option>
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12" selected>12 bulan (1 tahun) — direkomendasikan</option>
                    </select>
                </div>
            </div>

            <div class="pt-6 border-t" style="border-color: var(--lp-border);">
                <button type="submit" class="lp-btn w-full">Lanjut ke Pembayaran</button>
                <p class="text-xs text-center mt-3" style="color: var(--lp-muted);">Dengan mendaftar, Anda menyetujui syarat &amp; ketentuan platform.</p>
            </div>
        </form>
    </div>
</section>
@endsection
