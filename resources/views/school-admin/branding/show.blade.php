@extends('layouts.school-admin')
@section('title', 'Branding & Whitelabel')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Branding & Whitelabel</h2>
            <p class="text-sm text-gray-600">Logo, warna, nama tampilan, splash screen mobile, header email — semua bisa diatur dari sini.</p>
        </div>
        <form method="POST" action="{{ route('admin.branding.reset') }}" onsubmit="return confirm('Reset semua branding ke default?')">
            @csrf
            <button class="text-sm text-red-600 hover:underline">Reset ke default</button>
        </form>
    </div>

    {{-- Identity --}}
    <form method="POST" action="{{ route('admin.branding.update') }}" class="bg-white rounded-lg shadow p-6 space-y-5">
        @csrf @method('PUT')

        {{-- Template Tema --}}
        <div x-data="{ theme: '{{ $selected }}' }" class="space-y-3">
            <input type="hidden" name="theme" :value="theme">
            <div>
                <h3 class="font-semibold">Template Tema</h3>
                <p class="text-sm text-gray-600 mt-1">Pilih template dasar — warna, font &amp; bentuk akan diterapkan. Warna tetap bisa disesuaikan di bawah.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($themes as $t)
                    <button type="button" @click="theme = '{{ $t['key'] }}'"
                            class="text-left rounded-lg border-2 p-4 transition"
                            :class="theme === '{{ $t['key'] }}' ? 'border-[var(--c-primary)] shadow-md' : 'border-gray-200 hover:border-gray-300'"
                            :aria-pressed="(theme === '{{ $t['key'] }}').toString()">
                        <div class="flex items-center gap-1.5 mb-2">
                            @foreach(['primary', 'secondary', 'accent', 'sidebar'] as $sw)
                                <span class="w-5 h-5 rounded-full border border-gray-200" style="background: {{ $t['palette'][$sw] }}" title="{{ $sw }}"></span>
                            @endforeach
                            <span class="ml-auto text-[10px] font-mono text-gray-400">{{ $t['key'] }}</span>
                        </div>
                        <div class="font-semibold text-sm">{{ $t['name'] }}</div>
                        <div class="text-xs text-gray-500 mt-1 leading-snug">{{ $t['description'] }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <h3 class="font-semibold pt-2">Identitas Sekolah</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Tampilan</label>
                <input name="display_name" type="text" maxlength="200"
                       value="{{ old('display_name', $branding['display_name']) }}"
                       class="w-full border rounded px-3 py-2 text-sm"
                       placeholder="SMK Negeri 1 Jakarta">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tagline</label>
                <input name="tagline" type="text" maxlength="200"
                       value="{{ old('tagline', $branding['tagline']) }}"
                       class="w-full border rounded px-3 py-2 text-sm"
                       placeholder="Berkarakter, Berprestasi, Berkebudayaan">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipe Sekolah</label>
                <input name="school_type_label" type="text" maxlength="100"
                       value="{{ old('school_type_label', $branding['school_type_label']) }}"
                       class="w-full border rounded px-3 py-2 text-sm"
                       placeholder="SMK / SMA / Pesantren / Universitas">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Format Tahun Ajaran</label>
                <input name="academic_year_format" type="text" maxlength="30"
                       value="{{ old('academic_year_format', $branding['academic_year_format']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono"
                       placeholder="Y/Y+1 atau TA Y-Y+1">
            </div>
        </div>

        <h3 class="font-semibold pt-4">Warna</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach(['primary' => 'Primer', 'secondary' => 'Sekunder', 'accent' => 'Aksen', 'sidebar' => 'Sidebar', 'table_header' => 'Header Tabel', 'success' => 'Sukses', 'warning' => 'Peringatan', 'danger' => 'Bahaya'] as $key => $label)
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                    <div class="flex gap-2 items-center">
                        <input name="color_{{ $key }}" type="color"
                               value="{{ old('color_'.$key, $branding['colors'][$key] ?? ($key === 'table_header' ? '#F1F5F9' : '#2563EB')) }}"
                               class="h-10 w-14 border rounded">
                        <input type="text" readonly
                               value="{{ $branding['colors'][$key] ?? ($key === 'table_header' ? '#F1F5F9' : '') }}"
                               class="flex-1 border rounded px-2 py-2 text-xs font-mono bg-gray-50">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Font & Tipografi --}}
        @php
            $fontFamilies = collect($fontPresets)->mapWithKeys(fn ($p, $k) => [$k => $p['family']])->all();
            $currentFontKey = null;
            foreach ($fontPresets as $k => $p) {
                if ($p['family'] === ($branding['font']['family'] ?? null)) { $currentFontKey = $k; break; }
            }
            $currentFontKey ??= 'manrope';
        @endphp
        <div x-data="{
            font: '{{ $currentFontKey }}',
            text: '{{ $branding['colors']['text'] ?? '#0F172A' }}',
            muted: '{{ $branding['colors']['text_muted'] ?? '#64748B' }}',
            primary: '{{ $branding['colors']['primary'] ?? '#2563EB' }}',
            accent: '{{ $branding['colors']['accent'] ?? '#F59E0B' }}',
            radius: '{{ $branding['typography']['radius_scale'] ?? 'medium' }}',
            fonts: @json($fontFamilies)
        }">
            <h3 class="font-semibold pt-4">Font &amp; Tipografi</h3>
            <div class="grid sm:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis Font</label>
                    <select name="font_preset" x-model="font" class="w-full border rounded px-3 py-2 text-sm">
                        @foreach($fontPresets as $k => $p)
                            <option value="{{ $k }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Warna Teks</label>
                        <input type="color" name="color_text" x-model="text" class="h-10 w-full border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Warna Teks Muted</label>
                        <input type="color" name="color_text_muted" x-model="muted" class="h-10 w-full border rounded">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ukuran Font</label>
                    <div class="flex gap-2">
                        @foreach(['compact' => 'Kecil', 'normal' => 'Normal', 'large' => 'Besar'] as $fs => $fsl)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="font_scale" value="{{ $fs }}" class="peer sr-only" {{ ($branding['typography']['font_scale'] ?? 'normal') === $fs ? 'checked' : '' }}>
                                <span class="block text-center text-sm border rounded px-2 py-2 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:font-semibold">{{ $fsl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bentuk Sudut (Radius)</label>
                    <div class="flex gap-2">
                        @foreach(['small' => 'Kotak', 'medium' => 'Sedang', 'large' => 'Bulat'] as $rs => $rsl)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="radius_scale" value="{{ $rs }}" x-model="radius" class="peer sr-only">
                                <span class="block text-center text-sm border rounded px-2 py-2 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:font-semibold">{{ $rsl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Live preview --}}
            <div class="mt-4 p-4 rounded-lg border" style="background: #F8FAFC;">
                <div class="text-xs uppercase tracking-wide mb-2" style="color: #64748B;">Pratinjau</div>
                <div class="p-4 rounded-lg" style="background: #FFFFFF; border: 1px solid #E2E8F0;" :style="'font-family: ' + fonts[font]">
                    <div class="text-lg font-bold" :style="'color: ' + text">Judul Halaman</div>
                    <div class="text-sm mt-1" :style="'color: ' + muted">Ini adalah contoh teks paragraf dengan warna muted untuk melihat hasil font dan tipografi.</div>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white" :style="'background: ' + primary + '; border-radius: ' + (radius === 'small' ? '6px' : radius === 'large' ? '16px' : '12px')">Tombol Utama</span>
                        <span class="inline-flex items-center px-4 py-2 text-sm font-semibold border" :style="'color: ' + primary + '; border-color: ' + primary + '; border-radius: ' + (radius === 'small' ? '6px' : radius === 'large' ? '16px' : '12px')">Tombol Sekunder</span>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="font-semibold pt-4">Mobile App</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama App di OS</label>
                <input name="mobile_app_display_name" type="text" maxlength="200"
                       value="{{ old('mobile_app_display_name', $branding['mobile']['app_name']) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Splash Background</label>
                <input name="mobile_splash_bg_color" type="color"
                       value="{{ old('mobile_splash_bg_color', $branding['mobile']['splash_bg_color']) }}"
                       class="h-10 w-full border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Notifikasi Color</label>
                <input name="fcm_notification_color" type="color"
                       value="{{ old('fcm_notification_color', $branding['notification']['color'] ?? '#2563EB') }}"
                       class="h-10 w-full border rounded">
            </div>
        </div>

        <h3 class="font-semibold pt-4">Email & PDF Receipt</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Layout Receipt</label>
                <select name="receipt_layout" class="w-full border rounded px-3 py-2 text-sm">
                    @foreach(['simple' => 'Simple', 'formal' => 'Formal', 'modern' => 'Modern'] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($branding['pdf']['receipt_layout'] ?? 'formal') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 pt-7">
                <input type="hidden" name="pdf_watermark_enabled" value="0">
                <input type="checkbox" name="pdf_watermark_enabled" value="1"
                       {{ ($branding['pdf']['watermark_enabled'] ?? false) ? 'checked' : '' }}>
                <label class="text-sm">Tampilkan watermark logo di PDF</label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Footer Email</label>
            <textarea name="email_footer_text" rows="3" maxlength="2000"
                      class="w-full border rounded px-3 py-2 text-sm">{{ old('email_footer_text', $branding['email']['footer_text'] ?? '') }}</textarea>
        </div>

        <div class="pt-4 border-t flex justify-end">
            <button type="submit" class="btn-brand">Simpan Branding</button>
        </div>
    </form>

    {{-- Logo Uploads --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4">Logo & Gambar</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $logoSlots = [
                    'primary'      => ['Primary', 'Header & sidebar (light bg)'],
                    'secondary'    => ['Secondary', 'Untuk dark bg / footer'],
                    'monochrome'   => ['Monochrome', 'Untuk PDF / watermark'],
                    'favicon'      => ['Favicon', 'Tab browser'],
                    'login_bg'     => ['Login Background', 'Cover image login'],
                    'splash_logo'  => ['Splash Logo', 'Mobile splash 1024×1024'],
                    'email_header' => ['Email Header', 'Logo di email'],
                    'fcm_icon'     => ['FCM Icon', 'Notif Android (silhouette)'],
                ];
            @endphp
            @foreach($logoSlots as $type => [$label, $hint])
                <div class="border rounded-lg p-4 text-center">
                    <div class="text-sm font-medium mb-1">{{ $label }}</div>
                    <div class="text-xs text-gray-500 mb-3">{{ $hint }}</div>
                    <div class="h-20 flex items-center justify-center bg-gray-50 rounded mb-3">
                        @if($branding['logos'][$type] ?? null)
                            <img src="{{ $branding['logos'][$type] }}?v={{ $branding['cache_version'] }}"
                                 alt="{{ $label }}" class="max-h-16 max-w-full">
                        @else
                            <span class="text-gray-300 text-xs">— belum di-upload —</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.branding.upload-logo') }}"
                          enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="file" name="file" accept=".png,.jpg,.jpeg,.svg,.ico,.webp" required
                               class="w-full text-xs">
                        <div class="flex gap-1 justify-center">
                            <button type="submit" class="text-xs btn-brand py-1 px-3">Upload</button>
                            @if($branding['logos'][$type] ?? null)
                                <button type="submit" form="rm-{{ $type }}" class="text-xs text-red-600 hover:underline">Hapus</button>
                            @endif
                        </div>
                    </form>
                    @if($branding['logos'][$type] ?? null)
                        <form id="rm-{{ $type }}" method="POST" action="{{ route('admin.branding.remove-logo', $type) }}">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
