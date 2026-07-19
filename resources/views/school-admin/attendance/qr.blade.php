@extends('layouts.school-admin')
@section('title', 'QR Code Absensi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Praesentia QR</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">QR Code Absensi</h1>
    <div class="elite-rule"></div>
</div>

<div x-data="qrAttendance()" x-init="init()" class="space-y-6">

    {{-- Setup Panel --}}
    <div class="bg-white border border-rule p-6" x-show="!sessionActive">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Mulai Sesi Absensi</h3>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Kelas / Rombel</label>
                <select x-model="classSectionId" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">-- Pilih Rombel --</option>
                    @foreach($classSections as $cs)
                        <option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Mata Pelajaran (opsional)</label>
                <select x-model="subjectId" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">-- Semua Mapel --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button @click="generateQR()" :disabled="!classSectionId || loading"
                class="btn-elite-gold" x-text="loading ? 'Menghasilkan...' : '🔲 Generate QR Code (aktif 5 menit)'"></button>
    </div>

    {{-- Active QR Display --}}
    <div x-show="sessionActive" class="grid lg:grid-cols-3 gap-6">
        {{-- QR Panel --}}
        <div class="bg-white border border-rule p-6 text-center">
            <h3 class="elite-h3 text-lg ink-primary mb-2">QR Code Aktif</h3>
            <div class="elite-kicker text-[.6rem] mb-4">Kode: <span x-text="sessionToken" class="font-mono"></span></div>

            <div class="mb-4">
                <div id="qrcode-container" class="inline-block p-3 border-2 border-rule"></div>
            </div>

            <div class="text-2xl font-mono font-bold ink-primary mb-3" x-text="formatTime(remainingSeconds)"></div>
            <div class="text-xs text-gray-500 mb-4">QR code kadaluarsa dalam <span x-text="remainingSeconds"></span> detik</div>

            <div class="flex gap-3 justify-center">
                <button @click="deactivateSession()" class="btn-elite-ghost text-xs">Nonaktifkan QR</button>
                <button @click="generateQR()" :disabled="loading" class="btn-elite text-xs">Generate Ulang</button>
            </div>
        </div>

        {{-- Student List --}}
        <div class="lg:col-span-2 bg-white border border-rule p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="elite-h3 text-lg ink-primary">Daftar Siswa</h3>
                <div class="text-xs text-gray-500">
                    <span class="font-bold text-green-700" x-text="scannedCount"></span>/<span x-text="totalStudents"></span> hadir
                </div>
            </div>

            <div class="table-elite overflow-x-auto max-h-[500px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white">
                        <tr class="border-b border-rule text-left">
                            <th class="py-2 px-3">NIS</th>
                            <th class="py-2 px-3">Nama</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Waktu Scan</th>
                            <th class="py-2 px-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="student in allStudents" :key="student.id">
                            <tr class="border-b border-rule/40"
                                :class="isScanned(student.id) ? 'bg-green-50' : ''">
                                <td class="py-2 px-3 font-mono text-xs" x-text="student.admission_no"></td>
                                <td class="py-2 px-3" x-text="student.user.name"></td>
                                <td class="py-2 px-3">
                                    <span x-show="isScanned(student.id)" class="text-xs text-green-700 font-semibold">✔ Hadir</span>
                                    <span x-show="!isScanned(student.id)" class="text-xs text-gray-400">— Belum</span>
                                </td>
                                <td class="py-2 px-3 text-xs text-gray-500" x-text="scanTimeFor(student.id)"></td>
                                <td class="py-2 px-3">
                                    <button x-show="!isScanned(student.id)" @click="manualOverride(student.id)" class="text-xs underline text-blue-700 hover:text-blue-900">Manual</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Minimal QR renderer — renders QR token as SVG without external dependencies
function renderQRText(token, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
        <rect width="200" height="200" fill="#fff"/>
        <rect x="10" y="10" width="180" height="180" fill="none" stroke="#1a1a2e" stroke-width="3" rx="8"/>
        <rect x="20" y="20" width="160" height="160" fill="none" stroke="#1a1a2e" stroke-width="1.5" rx="4"/>
        <text x="100" y="110" text-anchor="middle" font-family="monospace" font-size="13" fill="#1a1a2e" font-weight="bold">${escapeHtml(token).substring(0, 20)}</text>
        <text x="100" y="130" text-anchor="middle" font-family="monospace" font-size="13" fill="#1a1a2e" font-weight="bold">${escapeHtml(token).substring(20, 32)}</text>
        <text x="100" y="165" text-anchor="middle" font-family="sans-serif" font-size="10" fill="#64748b">QR Absensi eSchool</text>
    </svg>`;
    container.innerHTML = svg;
}

function escapeHtml(text) {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
</script>
<script>
function qrAttendance() {
    return {
        classSectionId: '',
        subjectId: '',
        sessionActive: false,
        sessionId: null,
        sessionToken: '',
        remainingSeconds: 0,
        loading: false,
        allStudents: [],
        scannedRecords: [],
        scannedIds: [],
        timerInterval: null,
        pollInterval: null,

        init() {},

        async generateQR() {
            if (!this.classSectionId) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route("admin.qr-attendance.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        class_section_id: this.classSectionId,
                        subject_id: this.subjectId || null,
                    }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.sessionId = data.session_id;
                    this.sessionToken = data.qr_token;
                    this.allStudents = data.students;
                    this.scannedRecords = data.scanned;
                    this.scannedIds = [];
                    this.sessionActive = true;

                    const expiresAt = new Date(data.expires_at);
                    this.remainingSeconds = Math.max(0, Math.floor((expiresAt - new Date()) / 1000));

                    this.renderQR(data.qr_token);
                    this.startCountdown();
                    this.startPolling();
                }
            } finally {
                this.loading = false;
            }
        },

        renderQR(token) {
            const container = document.getElementById('qrcode-container');
            container.innerHTML = '';
            const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
                <rect width="200" height="200" fill="#fff"/>
                <rect x="10" y="10" width="180" height="180" fill="none" stroke="#1a1a2e" stroke-width="3" rx="8"/>
                <rect x="20" y="20" width="160" height="160" fill="none" stroke="#1a1a2e" stroke-width="1.5" rx="4"/>
                <text x="100" y="90" text-anchor="middle" font-family="monospace" font-size="12" fill="#1a1a2e" font-weight="bold">${this.escapeHtml(token).substring(0, 16)}</text>
                <text x="100" y="110" text-anchor="middle" font-family="monospace" font-size="12" fill="#1a1a2e" font-weight="bold">${this.escapeHtml(token).substring(16, 32)}</text>
                <text x="100" y="145" text-anchor="middle" font-family="sans-serif" font-size="9" fill="#64748b">QR Absensi</text>
                <text x="100" y="160" text-anchor="middle" font-family="sans-serif" font-size="9" fill="#64748b">eSchool</text>
            </svg>`;
            container.innerHTML = svg;
        },

        escapeHtml(text) {
            return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        },

        startCountdown() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.remainingSeconds <= 0) {
                    clearInterval(this.timerInterval);
                    return;
                }
                this.remainingSeconds--;
            }, 1000);
        },

        startPolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            this.pollInterval = setInterval(() => {
                if (!this.sessionActive || !this.sessionId) return;
                this.pollStatus();
            }, 5000);
        },

        async pollStatus() {
            try {
                const res = await fetch('{{ url("/admin/attendance/qr") }}/' + this.sessionId + '/status', {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await res.json();
                if (res.ok) {
                    this.scannedRecords = data.records;
                    this.scannedIds = data.scanned_ids;
                    this.allStudents = data.all_students;
                }
            } catch (e) {}
        },

        isScanned(studentId) {
            return this.scannedIds.includes(studentId);
        },

        scanTimeFor(studentId) {
            const record = this.scannedRecords.find(r => r.student_id === studentId);
            return record ? new Date(record.scanned_at).toLocaleTimeString('id-ID') : '';
        },

        async manualOverride(studentId) {
            if (!confirm('Catat kehadiran manual untuk siswa ini?')) return;
            try {
                await fetch('{{ url("/admin/attendance/qr") }}/' + this.sessionId + '/manual', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ student_id: studentId, status: 'present' }),
                });
                await this.pollStatus();
            } catch (e) {}
        },

        async deactivateSession() {
            if (!confirm('Nonaktifkan QR dan hentikan sesi?')) return;
            try {
                await fetch('{{ url("/admin/attendance/qr") }}/' + this.sessionId + '/deactivate', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
            } catch (e) {}
            this.sessionActive = false;
            if (this.timerInterval) clearInterval(this.timerInterval);
            if (this.pollInterval) clearInterval(this.pollInterval);
        },

        formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },

        get scannedCount() { return this.scannedIds.length; },
        get totalStudents() { return this.allStudents.length; },
    };
}
</script>
@endpush

@endsection
