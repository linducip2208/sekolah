@extends('layouts.school-admin')
@section('title', 'Buat Peringatan Darurat')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div x-data="emergencyWizard()" x-init="init()">
    <div class="mb-7">
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Buat Peringatan Darurat</h1>
        <div class="elite-rule"></div>
    </div>

    {{-- Steps indicator --}}
    <div class="flex items-center gap-2 mb-8 overflow-x-auto">
        <template x-for="(step, i) in steps" :key="i">
            <div class="flex items-center gap-2">
                <div :class="{
                    'px-3 py-1.5 rounded-full text-xs font-semibold': true,
                    'bg-white border': currentStep < i,
                    'bg-black text-white': currentStep === i,
                    'bg-green-700 text-white': currentStep > i,
                }" x-text="step"></div>
                <template x-if="i < steps.length - 1">
                    <div :class="{ 'w-8 h-px': true, 'bg-gray-300': currentStep <= i, 'bg-green-700': currentStep > i }"></div>
                </template>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ route('admin.emergency.store') }}" class="bg-white border border-rule p-7">
        @csrf

        {{-- STEP 1: Type --}}
        <div x-show="currentStep === 0" class="space-y-5">
            <h3 class="elite-h3 text-lg ink-primary">Pilih Tipe Darurat</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach([
                    'fire' => ['🔥 Kebakaran', 'bg-red-100 border-red-300'],
                    'earthquake' => ['🌍 Gempa Bumi', 'bg-amber-100 border-amber-300'],
                    'flood' => ['🌊 Banjir', 'bg-blue-100 border-blue-300'],
                    'security' => ['🛡️ Keamanan', 'bg-slate-100 border-slate-300'],
                    'medical' => ['🏥 Medis', 'bg-emerald-100 border-emerald-300'],
                    'other' => ['⚠️ Lainnya', 'bg-purple-100 border-purple-300'],
                ] as $type => [$label, $classes])
                <label class="cursor-pointer p-4 border-2 rounded-lg text-center {{ $classes }} transition hover:shadow-md"
                       :class="{'ring-2 ring-offset-2 ring-black': alertType === '{{ $type }}'}">
                    <input type="radio" name="alert_type" value="{{ $type }}" x-model="alertType" class="sr-only">
                    <span class="block text-sm font-semibold">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            <div class="flex justify-end">
                <button type="button" @click="nextStep()" :disabled="!alertType" class="btn-elite">
                    Lanjut →
                </button>
            </div>
        </div>

        {{-- STEP 2: Template / Message --}}
        <div x-show="currentStep === 1" class="space-y-5" x-cloak>
            <h3 class="elite-h3 text-lg ink-primary">Pesan Peringatan</h3>
            <div>
                <label class="elite-kicker block mb-1">Template Cepat</label>
                <select x-model="selectedTemplate" @change="loadTemplate()" class="w-full border border-rule p-2.5 font-serif">
                    <option value="">— Pesan manual —</option>
                    @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->alert_type }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Judul</label>
                <input type="text" name="title" x-model="title" required class="w-full border border-rule p-2.5 font-serif" placeholder="Judul peringatan darurat...">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Pesan Lengkap</label>
                <textarea name="message" x-model="message" required rows="5" class="w-full border border-rule p-2.5 font-serif" placeholder="Detail peringatan darurat..."></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" @click="currentStep = 0" class="btn-elite-ghost">← Kembali</button>
                <button type="button" @click="nextStep()" :disabled="!title || !message" class="btn-elite">Lanjut →</button>
            </div>
        </div>

        {{-- STEP 3: Recipients --}}
        <div x-show="currentStep === 2" class="space-y-5" x-cloak>
            <h3 class="elite-h3 text-lg ink-primary">Pilih Penerima</h3>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 border border-rule cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="recipient_type" value="all_parents" x-model="recipientType" class="text-black">
                    <div>
                        <div class="font-semibold">Semua Orang Tua</div>
                        <div class="text-xs text-gray-500">Kirim ke semua wali murid</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 border border-rule cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="recipient_type" value="all_staff" x-model="recipientType" class="text-black">
                    <div>
                        <div class="font-semibold">Semua Staff & Guru</div>
                        <div class="text-xs text-gray-500">Kirim ke seluruh staf sekolah</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 border border-rule cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="recipient_type" value="class" x-model="recipientType" class="text-black">
                    <div>
                        <div class="font-semibold">Per Kelas</div>
                        <div class="text-xs text-gray-500">Kirim ke orang tua siswa kelas tertentu</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 border border-rule cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="recipient_type" value="individual" x-model="recipientType" class="text-black">
                    <div>
                        <div class="font-semibold">Individual</div>
                        <div class="text-xs text-gray-500">Pilih penerima spesifik</div>
                    </div>
                </label>
            </div>
            <div class="flex justify-between">
                <button type="button" @click="currentStep = 1" class="btn-elite-ghost">← Kembali</button>
                <button type="button" @click="nextStep()" :disabled="!recipientType" class="btn-elite">Lanjut →</button>
            </div>
        </div>

        {{-- STEP 4: Review & Send --}}
        <div x-show="currentStep === 3" class="space-y-5" x-cloak>
            <h3 class="elite-h3 text-lg ink-primary">Review & Kirim</h3>

            <div>
                <label class="elite-kicker block mb-1">Severity</label>
                <select name="severity" x-model="severity" class="w-full border border-rule p-2.5 font-serif">
                    <option value="info">Informasi</option>
                    <option value="warning">Waspada</option>
                    <option value="critical">KRITIS</option>
                </select>
            </div>

            <div class="border border-red-300 bg-red-50 p-5">
                <div class="font-serif text-lg font-bold mb-2" x-text="title"></div>
                <p class="text-sm whitespace-pre-wrap" x-text="message"></p>
                <div class="mt-3 text-xs text-gray-600">
                    <span class="font-semibold">Tipe:</span> <span x-text="alertType"></span> ·
                    <span class="font-semibold">Severity:</span> <span x-text="severity"></span> ·
                    <span class="font-semibold">Penerima:</span> <span x-text="recipientType"></span>
                </div>
            </div>

            <label class="flex items-center gap-2 text-red-700">
                <input type="checkbox" name="confirm" value="1" required class="w-4 h-4">
                <span class="font-semibold text-sm">Ya, saya yakin ingin mengirim peringatan darurat ini!</span>
            </label>

            <div class="flex justify-between">
                <button type="button" @click="currentStep = 2" class="btn-elite-ghost">← Kembali</button>
                <button type="submit" class="btn-elite" style="background:#dc2626; border-color:#dc2626;">
                    KIRIM PERINGATAN!
                </button>
            </div>
        </div>

        <input type="hidden" name="recipient_ids" :value="JSON.stringify(recipientIds)">
    </form>
</div>

<script>
function emergencyWizard() {
    return {
        currentStep: 0,
        steps: ['Tipe', 'Pesan', 'Penerima', 'Kirim'],
        alertType: '',
        selectedTemplate: '',
        title: '',
        message: '',
        recipientType: 'all_staff',
        recipientIds: [],
        severity: 'warning',

        nextStep() {
            if (this.currentStep < 3) this.currentStep++;
        },

        loadTemplate() {
            if (!this.selectedTemplate) return;
            fetch(`/admin/emergency/templates-by-type/${this.alertType}`)
                .then(r => r.json())
                .then(data => {
                    const tpl = data.find(t => t.id == this.selectedTemplate);
                    if (tpl) {
                        this.title = tpl.title_template;
                        this.message = tpl.message_template;
                    }
                });
        },

        init() {
            this.$watch('alertType', () => {
                if (this.alertType === 'fire') this.severity = 'critical';
                if (this.alertType === 'earthquake') this.severity = 'critical';
            });
        }
    };
}
</script>
@endsection
