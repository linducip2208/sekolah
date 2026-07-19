@extends('super-admin.layout')
@section('title', 'Edit Gateway')
@section('content')

<a href="{{ route('super.billing.gateways.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="max-w-2xl">
    <div class="elite-kicker mb-2">Modificatio Portae</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Edit Gateway: {{ $gateway->name }}</h1>
    <div class="elite-rule mb-7"></div>

    @if($errors->any())
        <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
            <ul class="list-disc list-inside font-serif text-sm text-red-800">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super.billing.gateways.update', $gateway) }}" class="bg-white border border-rule p-7 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="elite-kicker block mb-2">Nama Provider</label>
            <input type="text" name="name" value="{{ old('name', $gateway->name) }}" required maxlength="200"
                   class="w-full border-2 border-rule px-3 py-2 font-serif">
        </div>

        <div>
            <label class="elite-kicker block mb-2">Slug</label>
            <input type="text" value="{{ $gateway->slug }}" disabled
                   class="w-full border-2 border-rule px-3 py-2 font-mono bg-gray-50 text-gray-500">
            <p class="text-xs text-gray-400 mt-1">Slug tidak bisa diubah setelah dibuat.</p>
        </div>

        <div>
            <label class="elite-kicker block mb-2">Format API</label>
            <select name="api_format" required class="w-full border-2 border-rule px-3 py-2 font-serif">
                @foreach($formats as $f)
                    <option value="{{ $f }}" @selected(old('api_format', $gateway->api_format) === $f)>
                        @switch($f)
                            @case('redirect_checkout') Redirect Checkout (Snap, hosted) @break
                            @case('virtual_account')   Virtual Account @break
                            @case('ewallet_deeplink')  E-Wallet (deeplink) @break
                            @case('qris_dynamic')      QRIS Dynamic @break
                            @case('qris_static')       QRIS Static @break
                            @case('bank_transfer_manual') Bank Transfer Manual @break
                            @default {{ $f }}
                        @endswitch
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="elite-kicker block mb-2">Base URL</label>
            <input type="url" name="base_url" value="{{ old('base_url', $gateway->base_url) }}" maxlength="500"
                   class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>

        <div class="pt-4 border-t border-rule">
            <p class="font-serif text-sm text-gray-600 mb-3 italic">Kosongkan field di bawah jika tidak ingin mengubah nilai yang sudah tersimpan.</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">API Key
                        @if($gateway->api_key_encrypted)<span class="text-green-700">(tersimpan)</span>@endif
                    </label>
                    <input type="text" name="api_key" maxlength="500"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                           autocomplete="off" placeholder="••• ganti jika perlu">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Secret Key
                        @if($gateway->secret_key_encrypted)<span class="text-green-700">(tersimpan)</span>@endif
                    </label>
                    <input type="text" name="secret_key" maxlength="500"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                           autocomplete="off">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Merchant ID
                        @if($gateway->merchant_id_encrypted)<span class="text-green-700">(tersimpan)</span>@endif
                    </label>
                    <input type="text" name="merchant_id" maxlength="500"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                           autocomplete="off">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Webhook Secret
                        @if($gateway->webhook_secret_encrypted)<span class="text-green-700">(tersimpan)</span>@endif
                    </label>
                    <input type="text" name="webhook_secret" maxlength="500"
                           class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                           autocomplete="off">
                </div>
            </div>
        </div>

        <div>
            <label class="elite-kicker block mb-2">Callback URL</label>
            <input type="url" name="callback_url" value="{{ old('callback_url', $gateway->callback_url) }}" maxlength="500"
                   class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>

        <div class="grid grid-cols-3 gap-3 pt-3 border-t border-rule">
            <div>
                <label class="elite-kicker block mb-2">Prioritas</label>
                <input type="number" name="priority" value="{{ old('priority', $gateway->priority) }}" min="0" max="100"
                       class="w-full border-2 border-rule px-3 py-2 font-mono">
            </div>
            <label class="flex items-end gap-2 pb-2">
                <input type="checkbox" name="is_sandbox" value="1" @checked($gateway->is_sandbox)>
                <span class="text-sm">Sandbox</span>
            </label>
            <label class="flex items-end gap-2 pb-2">
                <input type="checkbox" name="is_active" value="1" @checked($gateway->is_active)>
                <span class="text-sm">Aktif</span>
            </label>
        </div>

        <button type="submit" class="btn-elite w-full" style="padding:.7rem;">Simpan Perubahan</button>
    </form>
</div>

@endsection
