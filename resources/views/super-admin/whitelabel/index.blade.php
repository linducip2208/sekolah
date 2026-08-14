@extends('super-admin.layout')

@section('title', 'Whitelabel Platform')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Whitelabel Platform</h1>
        <p class="text-sm text-gray-600 mt-1">Kustomisasi nama, logo, warna, kontak, dan popup landing page. Berlaku ke seluruh halaman publik (landing, /docs, pSEO).</p>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Image uploads ===== --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">Logo &amp; Gambar</h2>
            <p class="text-xs text-gray-500 mt-0.5">PNG/SVG transparan untuk logo, JPG resolusi tinggi untuk hero (min 1600x1000).</p>
        </div>
        <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                $imageMeta = [
                    'logo_path'       => ['label' => 'Logo Utama (terang)', 'hint' => 'Tampil di header & footer'],
                    'logo_dark_path'  => ['label' => 'Logo Versi Gelap',     'hint' => 'Untuk background gelap'],
                    'favicon_path'    => ['label' => 'Favicon',              'hint' => 'Min 32×32, format ICO/PNG/SVG'],
                    'crest_path'      => ['label' => 'Crest / Lambang',      'hint' => 'Lambang sekolah/akademi'],
                    'hero_image_path' => ['label' => 'Foto Hero Landing',    'hint' => 'Foto kampus/siswa, JPG 1600×1000+'],
                    'og_image_path'   => ['label' => 'Open Graph Preview',   'hint' => 'Untuk preview link sosial, 1200×630'],
                ];
            @endphp
            @foreach($imageMeta as $field => $meta)
                @php $url = $settings[str_replace('_path', '_url', $field)] ?? null; @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-900">{{ $meta['label'] }}</div>
                    <div class="text-xs text-gray-500 mb-3">{{ $meta['hint'] }}</div>

                    <div class="aspect-video bg-gray-50 border border-dashed border-gray-300 rounded-md flex items-center justify-center mb-3 overflow-hidden">
                        @if($url)
                            <img src="{{ $url }}" alt="{{ $meta['label'] }}" class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-xs text-gray-400">Belum ada</span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('super.whitelabel.upload', $field) }}" enctype="multipart/form-data" class="flex gap-2">
                        @csrf
                        <input type="file" name="file" accept="image/*" required class="text-xs flex-1 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-indigo-100 file:text-indigo-700 file:text-xs file:font-medium hover:file:bg-indigo-200">
                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700">Upload</button>
                    </form>

                    @if($url)
                        <form method="POST" action="{{ route('super.whitelabel.remove', $field) }}" onsubmit="return confirm('Hapus gambar ini?')" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus gambar</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('super.whitelabel.update') }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ===== Landing Theme ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Template Landing Page</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pilih template dasar untuk halaman depan. Konten &amp; komponen dibagikan antar template — yang berubah hanya visual.</p>
            </div>
            <div class="p-6" x-data="{ theme: '{{ $settings['landing_theme'] ?? 'modern' }}' }">
                <input type="hidden" name="landing_theme" :value="theme">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($themes as $t)
                        <button type="button" @click="theme = '{{ $t['key'] }}'"
                                class="text-left rounded-lg border-2 p-4 transition"
                                :class="theme === '{{ $t['key'] }}' ? 'border-indigo-600 shadow-sm' : 'border-gray-200 hover:border-gray-300'"
                                :aria-pressed="(theme === '{{ $t['key'] }}').toString()">
                            <div class="flex items-center gap-1.5 mb-2">
                                @foreach(['--lp-primary', '--lp-accent', '--lp-bg'] as $sw)
                                    <span class="w-5 h-5 rounded-full border border-gray-200" style="background: {{ $t['vars'][$sw] }}"></span>
                                @endforeach
                                <span class="ml-auto text-[10px] font-mono text-gray-400">{{ $t['key'] }}</span>
                            </div>
                            <div class="font-semibold text-sm text-gray-900">{{ $t['name'] }}</div>
                            <div class="text-xs text-gray-500 mt-1 leading-snug">{{ $t['description'] }}</div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Identity ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Identitas Platform</h2>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Nama Aplikasi / Akademi</label>
                    <input type="text" name="app_name" value="{{ $settings['app_name'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tipe Institusi</label>
                    <input type="text" name="institution_type" value="{{ $settings['institution_type'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Tagline</label>
                    <input type="text" name="tagline" value="{{ $settings['tagline'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Motto (Latin)</label>
                    <input type="text" name="motto_latin" value="{{ $settings['motto_latin'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Motto (Terjemahan)</label>
                    <input type="text" name="motto_translated" value="{{ $settings['motto_translated'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tahun Berdiri</label>
                    <input type="text" name="established_year" value="{{ $settings['established_year'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ $settings['description'] }}</textarea>
                </div>
            </div>
        </div>

        {{-- ===== Hero ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Hero Section Landing Page</h2>
                <p class="text-xs text-gray-500 mt-0.5">HTML kecil seperti &lt;br&gt; dan &lt;em&gt; diperbolehkan.</p>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Hero Kicker</label>
                    <input type="text" name="hero_kicker" value="{{ $settings['hero_kicker'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div></div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Hero Title (HTML allowed)</label>
                    <textarea name="hero_title" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ $settings['hero_title'] }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ $settings['hero_subtitle'] }}</textarea>
                </div>
            </div>
        </div>

        {{-- ===== Colors ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Skema Warna</h2>
            </div>
            <div class="p-6 grid sm:grid-cols-4 gap-4">
                @foreach(['color_primary'=>'Primary (Navy)','color_secondary'=>'Secondary (Burgundy)','color_accent'=>'Accent (Gold)','color_paper'=>'Paper / Background'] as $key=>$label)
                    <div>
                        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
                        <div class="flex gap-2 mt-1">
                            <input type="color" name="{{ $key }}" value="{{ $settings[$key] }}" class="h-10 w-14 rounded border border-gray-300">
                            <input type="text" value="{{ $settings[$key] }}" class="flex-1 rounded-lg border-gray-300 text-sm" oninput="this.previousElementSibling.value=this.value; this.name='{{ $key }}'">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== Contact ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Kontak &amp; Alamat</h2>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Telepon</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">WhatsApp (kode negara, no spasi — mis. 6281xxx)</label>
                    <input type="text" name="contact_whatsapp" value="{{ $settings['contact_whatsapp'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Alamat Baris 1</label>
                    <input type="text" name="address_line1" value="{{ $settings['address_line1'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Alamat Baris 2</label>
                    <input type="text" name="address_line2" value="{{ $settings['address_line2'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>
        </div>

        {{-- ===== Social ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Sosial Media</h2>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-4">
                @foreach(['social_facebook'=>'Facebook URL','social_instagram'=>'Instagram URL','social_youtube'=>'YouTube URL','social_linkedin'=>'LinkedIn URL'] as $key=>$label)
                    <div>
                        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input type="url" name="{{ $key }}" value="{{ $settings[$key] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="https://...">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== Popup ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Popup Sekali-Tampil di Landing Page</h2>
                <p class="text-xs text-gray-500 mt-0.5">Tampil sekali per browser (localStorage) saat pengunjung pertama buka landing.</p>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="popup_enabled" value="1" {{ !empty($settings['popup_enabled']) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="text-sm font-medium text-gray-700">Aktifkan popup</label>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Judul Popup</label>
                    <input type="text" name="popup_title" value="{{ $settings['popup_title'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Teks CTA</label>
                    <input type="text" name="popup_cta_text" value="{{ $settings['popup_cta_text'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700">Pesan Popup</label>
                    <textarea name="popup_message" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ $settings['popup_message'] }}</textarea>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Telepon Popup</label>
                    <input type="text" name="popup_phone" value="{{ $settings['popup_phone'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">WhatsApp Popup (mis. 6281xxx)</label>
                    <input type="text" name="popup_whatsapp" value="{{ $settings['popup_whatsapp'] }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>
        </div>

        {{-- ===== Footer ===== --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Footer</h2>
            </div>
            <div class="p-6">
                <label class="text-sm font-medium text-gray-700">Disclaimer Footer</label>
                <textarea name="footer_disclaimer" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ $settings['footer_disclaimer'] }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 shadow">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
