@php
    /** @var \App\Models\Payment\PaymentMethod|null $method */
    $method ??= null;
    $isEdit = (bool) $method;
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Provider</label>
            <select name="payment_provider_id" required class="w-full border rounded px-3 py-2 text-sm">
                <option value="">— Pilih provider —</option>
                @foreach($providers as $p)
                    <option value="{{ $p->id }}"
                            {{ old('payment_provider_id', $method?->payment_provider_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->api_format }})
                    </option>
                @endforeach
            </select>
            @if($providers->isEmpty())
                <p class="text-sm text-red-600 mt-1">Belum ada provider aktif. <a href="{{ route('admin.payment.providers.create') }}" class="underline">Tambah provider dulu</a>.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kode Internal</label>
            <input name="code" type="text" required maxlength="50"
                   value="{{ old('code', $method?->code) }}"
                   placeholder="va_bca / qris / gopay / bank_manual"
                   class="w-full border rounded px-3 py-2 text-sm font-mono">
            <p class="text-xs text-gray-500 mt-1">Unik per sekolah. Pakai snake_case.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Tampil</label>
            <input name="display_name" type="text" required maxlength="200"
                   value="{{ old('display_name', $method?->display_name) }}"
                   placeholder="Virtual Account BCA"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Logo URL (opsional)</label>
            <input name="logo_url" type="url"
                   value="{{ old('logo_url', $method?->logo_url) }}"
                   placeholder="https://.../logo-bca.png"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Instruksi (opsional)</label>
        <textarea name="instruction_template" rows="3"
                  placeholder="Tampilkan ke orang tua saat metode ini dipilih"
                  class="w-full border rounded px-3 py-2 text-sm">{{ old('instruction_template', $method?->instruction_template) }}</textarea>
    </div>

    <div class="grid grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Fee Flat (Rp)</label>
            <input name="fee_flat" type="number" min="0"
                   value="{{ old('fee_flat', $method?->fee_flat ?? 0) }}"
                   class="w-full border rounded px-3 py-2 text-sm">
            <p class="text-xs text-gray-500 mt-1">Disimpan dalam sen. Misal Rp 4.000 = 400000.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Fee Persentase (bp)</label>
            <input name="fee_percent_bp" type="number" min="0" max="10000"
                   value="{{ old('fee_percent_bp', $method?->fee_percent_bp ?? 0) }}"
                   class="w-full border rounded px-3 py-2 text-sm">
            <p class="text-xs text-gray-500 mt-1">Basis points: 100 = 1%.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Ditanggung</label>
            <select name="fee_borne_by" class="w-full border rounded px-3 py-2 text-sm">
                <option value="0" {{ old('fee_borne_by', $method?->fee_borne_by ?? 0) == 0 ? 'selected' : '' }}>Orang tua</option>
                <option value="1" {{ old('fee_borne_by', $method?->fee_borne_by ?? 0) == 1 ? 'selected' : '' }}>Sekolah</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Expiry (menit)</label>
            <input name="expiry_minutes" type="number" min="1" max="43200"
                   value="{{ old('expiry_minutes', $method?->expiry_minutes ?? 1440) }}"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Min Amount (Rp)</label>
            <input name="min_amount" type="number" min="0"
                   value="{{ old('min_amount', $method?->min_amount ?? 0) }}"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Max Amount (Rp)</label>
            <input name="max_amount" type="number" min="0"
                   value="{{ old('max_amount', $method?->max_amount) }}"
                   class="w-full border rounded px-3 py-2 text-sm"
                   placeholder="(kosong = unlimited)">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Sort Order</label>
            <input name="sort_order" type="number" min="0" max="1000"
                   value="{{ old('sort_order', $method?->sort_order ?? 0) }}"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
    </div>

    <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $method?->is_active ?? 1) ? 'checked' : '' }}>
            Aktif (ditampilkan ke parent)
        </label>
    </div>

    <div class="pt-4 border-t flex justify-end gap-2">
        <a href="{{ route('admin.payment.methods.index') }}" class="px-4 py-2 text-sm">Batal</a>
        <button type="submit" class="btn-brand" {{ $providers->isEmpty() ? 'disabled' : '' }}>
            {{ $isEdit ? 'Simpan' : 'Tambah Metode' }}
        </button>
    </div>
</div>
