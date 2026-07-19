@extends('layouts.school-admin')

@section('title', 'Mata Uang')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Keuangan</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pengaturan Mata Uang</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border-l-4 border-green-700 text-green-800 text-sm">{{ session('success') }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white border border-rule p-6">
        <h3 class="elite-h3 text-lg ink-primary mb-3">Konfigurasi</h3>
        <form method="POST" action="{{ route('admin.currency.update') }}" class="space-y-3 text-sm">
            @csrf @method('PUT')
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Quick Preset (opsional — auto-fill semua field)</label>
                <div class="flex gap-2 flex-wrap">
                    @foreach($presets as $code => $p)
                        <button type="submit" form="preset-{{ $code }}" class="px-2 py-1 border border-rule text-xs font-mono hover:bg-gray-100">{{ $code }} {{ $p['symbol'] }}</button>
                    @endforeach
                </div>
                @foreach($presets as $code => $p)
                    <form id="preset-{{ $code }}" method="POST" action="{{ route('admin.currency.update') }}" class="hidden">
                        @csrf @method('PUT')
                        <input type="hidden" name="preset" value="{{ $code }}">
                        <input type="hidden" name="currency_code" value="{{ $code }}">
                        <input type="hidden" name="currency_symbol" value="{{ $p['symbol'] }}">
                        <input type="hidden" name="currency_decimals" value="{{ $p['decimals'] }}">
                        <input type="hidden" name="currency_thousands_sep" value="{{ $p['thousands'] }}">
                        <input type="hidden" name="currency_decimal_sep" value="{{ $p['decimal'] }}">
                    </form>
                @endforeach
            </div>

            <hr class="my-4">

            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Currency Code (ISO 4217, 3 huruf)</label>
                    <input name="currency_code" value="{{ $school->currency_code }}" maxlength="3" required class="w-full border-2 border-rule px-3 py-2 font-mono uppercase">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Symbol</label>
                    <input name="currency_symbol" value="{{ $school->currency_symbol }}" maxlength="8" required class="w-full border-2 border-rule px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Decimals (0-6)</label>
                    <input type="number" name="currency_decimals" value="{{ $school->currency_decimals }}" min="0" max="6" required class="w-full border-2 border-rule px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Thousands Separator</label>
                    <input name="currency_thousands_sep" value="{{ $school->currency_thousands_sep }}" maxlength="2" class="w-full border-2 border-rule px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Decimal Separator</label>
                    <input name="currency_decimal_sep" value="{{ $school->currency_decimal_sep }}" maxlength="2" required class="w-full border-2 border-rule px-3 py-2 font-mono">
                </div>
            </div>
            <button class="btn-elite">Simpan</button>
        </form>
    </div>

    <div class="bg-white border border-rule p-6">
        <h3 class="elite-h3 text-lg ink-primary mb-3">Preview</h3>
        <p class="elite-kicker text-[.6rem]">Sample: {{ number_format($sample) }} (minor units)</p>
        <p class="font-display text-3xl ink-primary mt-2">{{ $preview }}</p>
        <hr class="my-4">
        <p class="text-xs font-serif text-gray-600">Semua amount disimpan sebagai integer dalam minor units. Format ditampilkan otomatis di seluruh sistem.</p>
    </div>
</div>
@endsection
