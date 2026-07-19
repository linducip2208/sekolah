@extends('layouts.school-admin')
@section('title', 'Report Builder')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.x.x/Sortable.min.js"></script>
@endpush

@section('content')
<div x-data="reportBuilder()" x-init="init()" class="report-builder">
    <div class="mb-7 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Pembangun Laporan Kustom</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Seret-dan-lepas kolom, atur filter, pratinjau langsung, dan ekspor.</p>
        </div>
        <div class="flex gap-2">
            <button @click="exportCsv()" class="btn-elite text-[.6rem]" style="padding:.55rem 1rem;">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Ekspor CSV
            </button>
            <button @click="exportPdf()" class="btn-elite text-[.6rem]" style="padding:.55rem 1rem; background: var(--c-accent); border-color: var(--c-accent);">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Ekspor PDF
            </button>
            <button @click="showSaveModal = true" class="btn-elite text-[.6rem]" style="padding:.55rem 1rem; background: var(--c-success); border-color: var(--c-success);">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Simpan Template
            </button>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-5">
        {{-- Left Panel: Konfigurasi --}}
        <div class="lg:col-span-4 space-y-4">
            {{-- Data Source --}}
            <div class="elite-card">
                <div class="card-header">Sumber Data</div>
                <div class="p-4">
                    <select x-model="config.data_source" @change="onSourceChange()" class="w-full border-2 border-rule px-3 py-2 text-sm font-serif">
                        <option value="students">Siswa</option>
                        <option value="marks">Nilai & Ujian</option>
                        <option value="attendance">Absensi</option>
                        <option value="invoices">Invoice SPP</option>
                        <option value="payments">Pembayaran</option>
                        <option value="staff">Staff & Guru</option>
                    </select>
                </div>
            </div>

            {{-- Columns --}}
            <div class="elite-card">
                <div class="card-header flex justify-between items-center">
                    <span>Kolom <span class="text-xs font-normal text-gray-400">(seret urut)</span></span>
                    <button @click="showColumnPicker = !showColumnPicker" class="text-xs font-mono text-[var(--c-accent)] hover:underline">
                        + Tambah
                    </button>
                </div>
                <div class="p-4">
                    {{-- Column picker dropdown --}}
                    <div x-show="showColumnPicker" @click.outside="showColumnPicker = false" class="mb-3 border border-rule bg-white p-2 max-h-40 overflow-y-auto shadow">
                        <template x-for="col in availableColumns()" :key="col.field">
                            <div @click="addColumn(col)" class="px-2 py-1.5 hover:bg-gray-100 cursor-pointer text-sm font-serif flex justify-between"
                                 x-show="!config.columns.some(c => (typeof c === 'string' ? c : c.field) === col.field)">
                                <span x-text="col.label"></span>
                                <span class="text-[.6rem] text-gray-400 font-mono" x-text="col.computed ? 'computed' : ''"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Selected columns — sortable --}}
                    <div id="column-sort-list" class="space-y-1.5">
                        <template x-for="(col, idx) in config.columns" :key="idx">
                            <div class="flex items-center gap-2 border border-rule bg-gray-50 px-2 py-1.5 text-sm cursor-grab">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8zm6 0h2v2h-2zM8 11h2v2H8zm6 0h2v2h-2zm-6 5h2v2H8zm6 0h2v2h-2z"/></svg>
                                <span class="flex-1 font-mono text-[.65rem] truncate" x-text="typeof col === 'string' ? col : col.label || col.field"></span>
                                <button @click="removeColumn(idx)" class="text-red-500 hover:text-red-700 text-xs">x</button>
                            </div>
                        </template>
                        <div x-show="config.columns.length === 0" class="text-xs text-gray-400 italic py-2">Belum ada kolom dipilih.</div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="elite-card">
                <div class="card-header flex justify-between items-center">
                    <span>Filter</span>
                    <button @click="addFilter()" class="text-xs font-mono text-[var(--c-accent)] hover:underline">+ Filter</button>
                </div>
                <div class="p-4 space-y-2">
                    <template x-for="(f, idx) in config.filters" :key="idx">
                        <div class="flex gap-2 items-start">
                            <select x-model="f.field" class="border border-rule px-2 py-1.5 text-xs flex-1">
                                <option value="">-- Field --</option>
                                <template x-for="af in availableFilters()" :key="af.field">
                                    <option :value="af.field" x-text="af.label"></option>
                                </template>
                            </select>
                            <select x-model="f.operator" class="border border-rule px-2 py-1.5 text-xs w-20">
                                <option value="=">=</option>
                                <option value="like">LIKE</option>
                                <option value=">=">>=</option>
                                <option value="<=">&lt;=</option>
                                <option value="between">BETWEEN</option>
                            </select>
                            <input type="text" x-model="f.value" class="border border-rule px-2 py-1.5 text-xs w-28" placeholder="Nilai">
                            <button @click="removeFilter(idx)" class="text-red-500 hover:text-red-700 text-xs p-1">x</button>
                        </div>
                    </template>
                    <div x-show="config.filters.length === 0" class="text-xs text-gray-400 italic py-1">Tidak ada filter.</div>
                </div>
            </div>

            {{-- Grouping --}}
            <div class="elite-card">
                <div class="card-header">Pengelompokan</div>
                <div class="p-4">
                    <div class="flex gap-2 items-center">
                        <select x-model="config.grouping.field" class="border border-rule px-2 py-1.5 text-xs flex-1">
                            <option value="">-- Tanpa Group --</option>
                            <template x-for="g in groupingFields()" :key="g.field">
                                <option :value="g.field" x-text="g.label"></option>
                            </template>
                        </select>
                        <select x-show="config.grouping.field" x-model="config.grouping.aggregate" class="border border-rule px-2 py-1.5 text-xs w-24">
                            <option value="count">COUNT</option>
                            <option value="sum">SUM</option>
                            <option value="avg">AVG</option>
                            <option value="max">MAX</option>
                            <option value="min">MIN</option>
                        </select>
                    </div>
                    <div x-show="config.grouping.field" class="mt-2">
                        <input type="text" x-model="config.grouping.aggregate_target" class="border border-rule px-2 py-1.5 text-xs w-full" placeholder="Field agregasi (default: *)">
                    </div>
                </div>
            </div>

            {{-- Chart Config --}}
            <div class="elite-card">
                <div class="card-header flex justify-between items-center">
                    <span>Grafik</span>
                    <button @click="config.chart_config = config.chart_config ? null : {}" class="text-xs font-mono" :class="config.chart_config ? 'text-red-500' : 'text-[var(--c-accent)]'" x-text="config.chart_config ? 'Hapus' : '+ Grafik'"></button>
                </div>
                <div class="p-4" x-show="config.chart_config">
                    <div class="space-y-2">
                        <select x-model="config.chart_config.type" class="border border-rule px-2 py-1.5 text-xs w-full">
                            <option value="bar">Bar Chart</option>
                            <option value="line">Line Chart</option>
                            <option value="pie">Pie Chart</option>
                        </select>
                        <input type="text" x-model="config.chart_config.label_field" class="border border-rule px-2 py-1.5 text-xs w-full" placeholder="Field label (sumbu-x)">
                        <input type="text" x-model="config.chart_config.value_field" class="border border-rule px-2 py-1.5 text-xs w-full" placeholder="Field nilai (sumbu-y)">
                        <input type="text" x-model="config.chart_config.label" class="border border-rule px-2 py-1.5 text-xs w-full" placeholder="Label dataset">
                    </div>
                </div>
            </div>

            {{-- Load Template --}}
            <div class="elite-card">
                <div class="card-header">Template Tersimpan</div>
                <div class="p-3 max-h-40 overflow-y-auto">
                    <template x-for="t in templates" :key="t.id">
                        <div class="flex items-center justify-between py-1.5 border-b border-rule last:border-0 text-sm">
                            <button @click="loadTemplate(t)" class="font-serif text-left flex-1 hover:text-[var(--c-accent)] truncate" x-text="t.name"></button>
                            <button @click="deleteTemplate(t.id)" class="text-red-400 hover:text-red-600 text-xs ml-2">hapus</button>
                        </div>
                    </template>
                    <div x-show="templates.length === 0" class="text-xs text-gray-400 italic">Belum ada template.</div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Preview --}}
        <div class="lg:col-span-8 space-y-4">
            {{-- Status bar --}}
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span x-text="'Total: ' + totalCount + ' baris'"></span>
                <span class="w-2 h-2 rounded-full" :class="loading ? 'bg-yellow-500 animate-pulse' : 'bg-green-500'"></span>
                <span x-text="loading ? 'Memuat...' : 'Siap'"></span>
            </div>

            {{-- Chart area --}}
            <div x-show="chartData" class="elite-card">
                <div class="card-header">Grafik</div>
                <div class="p-5">
                    <canvas id="reportChart" height="220"></canvas>
                </div>
            </div>

            {{-- Data table --}}
            <div class="elite-card overflow-hidden">
                <div class="card-header">Pratinjau Data</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-[var(--c-primary)] text-white">
                            <tr>
                                <template x-for="(label, field) in previewColumns" :key="field">
                                    <th class="px-3 py-2.5 text-left elite-kicker text-[.55rem] whitespace-nowrap" x-text="label"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, ri) in previewRows" :key="ri">
                                <tr class="border-t border-rule hover:bg-gray-50">
                                    <template x-for="(label, field) in previewColumns" :key="field">
                                        <td class="px-3 py-2 font-mono text-[.65rem] whitespace-nowrap" x-text="formatCell(row[field])"></td>
                                    </template>
                                </tr>
                            </template>
                            <tr x-show="previewRows.length === 0 && !loading">
                                <td :colspan="Object.keys(previewColumns).length || 1" class="p-10 text-center text-gray-500 italic font-serif">
                                    Pilih sumber data dan kolom, lalu klik "Pratinjau" untuk melihat hasil.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Refresh button --}}
            <div class="flex justify-end">
                <button @click="fetchPreview()" :disabled="loading" class="btn-elite" style="padding:.65rem 1.5rem;">
                    <span x-show="!loading">Pratinjau</span>
                    <span x-show="loading" class="flex items-center gap-1">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Memuat...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Save Template Modal --}}
    <div x-show="showSaveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.6);">
        <div @click.outside="showSaveModal = false" class="bg-white w-full max-w-md mx-4 p-6 border border-rule shadow-xl">
            <h3 class="elite-h3 text-xl ink-primary mb-4">Simpan Template</h3>
            <div class="space-y-3">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Template</label>
                    <input type="text" x-model="saveForm.name" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: Laporan Nilai Semester 1">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea x-model="saveForm.description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Deskripsi singkat..."></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe Laporan</label>
                    <select x-model="saveForm.report_type" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="student">Siswa</option>
                        <option value="academic">Akademik</option>
                        <option value="finance">Keuangan</option>
                        <option value="attendance">Absensi</option>
                        <option value="combined">Gabungan</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="saveForm.is_shared" id="is_shared" class="border-rule">
                    <label for="is_shared" class="text-xs text-gray-600">Bagikan ke admin lain</label>
                </div>
                <div class="flex gap-2 pt-2">
                    <button @click="doSaveTemplate()" class="btn-elite flex-1" style="background: var(--c-success); border-color: var(--c-success);">Simpan</button>
                    <button @click="showSaveModal = false" class="border border-rule px-4 py-2 font-serif text-sm flex-1">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reportBuilder() {
    return {
        config: {
            data_source: 'students',
            columns: [
                { field: 'name', label: 'Nama' },
                { field: 'admission_no', label: 'No. Induk' },
                { field: 'gender', label: 'Jenis Kelamin' },
            ],
            filters: [],
            grouping: { field: '', aggregate: 'count', aggregate_target: '*' },
            chart_config: null,
        },
        previewRows: [],
        previewColumns: {},
        chartData: null,
        totalCount: 0,
        loading: false,
        templates: [],
        showColumnPicker: false,
        showSaveModal: false,
        saveForm: { name: '', description: '', report_type: 'combined', is_shared: false },
        chartInstance: null,

        init() {
            this.fetchTemplates();
            this.fetchPreview();
            this.$nextTick(() => {
                this.initSortable();
            });
        },

        initSortable() {
            const el = document.getElementById('column-sort-list');
            if (!el) return;
            Sortable.create(el, {
                animation: 150,
                onEnd: (evt) => {
                    const item = this.config.columns.splice(evt.oldIndex, 1)[0];
                    this.config.columns.splice(evt.newIndex, 0, item);
                }
            });
        },

        availableColumns() {
            const common = [
                { field: 'name', label: 'Nama', computed: false },
                { field: 'student_name', label: 'Nama Siswa', computed: false },
                { field: 'admission_no', label: 'No. Induk', computed: false },
                { field: 'gender', label: 'Jenis Kelamin', computed: false },
                { field: 'date_of_birth', label: 'Tanggal Lahir', computed: false },
                { field: 'age', label: 'Usia (terhitung)', computed: true },
                { field: 'guardian_name', label: 'Wali', computed: false },
                { field: 'guardian_phone', label: 'Telp Wali', computed: false },
            ];

            const academic = [
                { field: 'obtained_marks', label: 'Nilai', computed: false },
                { field: 'total_marks', label: 'Nilai Maks', computed: false },
                { field: 'grade', label: 'Nilai Huruf', computed: false },
                { field: 'gpa', label: 'Rata-rata', computed: true },
                { field: 'subject_name', label: 'Mata Pelajaran', computed: false },
                { field: 'exam_name', label: 'Ujian', computed: false },
            ];

            const attendance = [
                { field: 'date', label: 'Tanggal', computed: false },
                { field: 'status', label: 'Status', computed: false },
                { field: 'attendance_pct', label: '% Kehadiran', computed: true },
                { field: 'class_name', label: 'Kelas', computed: false },
                { field: 'section_name', label: 'Section', computed: false },
            ];

            const finance = [
                { field: 'invoice_no', label: 'No. Invoice', computed: false },
                { field: 'amount', label: 'Tagihan', computed: false },
                { field: 'paid_amount', label: 'Terbayar', computed: false },
                { field: 'arrears', label: 'Tunggakan', computed: true },
                { field: 'fee_collection_pct', label: '% Koleksi', computed: true },
                { field: 'payment_count', label: 'Jumlah Bayar', computed: true },
                { field: 'total_paid', label: 'Total Dibayar', computed: true },
            ];

            const staff = [
                { field: 'phone', label: 'Telepon', computed: false },
                { field: 'subject_name', label: 'Mata Pelajaran', computed: false },
            ];

            const source = this.config.data_source;
            let cols = [...common];
            if (source === 'marks') cols = [...academic, ...common];
            if (source === 'attendance') cols = [...attendance, ...common];
            if (source === 'invoices' || source === 'payments') cols = [...finance, ...common];
            if (source === 'staff') cols = [...staff, ...common];

            return cols;
        },

        availableFilters() {
            const common = [
                { field: 'gender', label: 'Jenis Kelamin' },
                { field: 'class_section_id', label: 'Rombel' },
                { field: 'date_from', label: 'Dari Tanggal' },
                { field: 'date_to', label: 'Sampai Tanggal' },
                { field: 'month', label: 'Bulan (Y-m)' },
            ];
            const marks = [
                { field: 'exam_id', label: 'Ujian' },
                { field: 'subject_id', label: 'Mata Pelajaran' },
                { field: 'semester_id', label: 'Semester' },
            ];
            const attendance = [
                { field: 'status', label: 'Status Absensi' },
            ];
            const finance = [
                { field: 'status', label: 'Status Invoice' },
            ];

            const source = this.config.data_source;
            if (source === 'marks') return [...marks, ...common];
            if (source === 'attendance') return [...attendance, ...common];
            if (source === 'invoices' || source === 'payments') return [...finance, ...common];
            return common;
        },

        groupingFields() {
            return [
                { field: 'class_name', label: 'Kelas' },
                { field: 'section_name', label: 'Section' },
                { field: 'gender', label: 'Jenis Kelamin' },
                { field: 'grade', label: 'Grade/Nilai Huruf' },
                { field: 'status', label: 'Status' },
                { field: 'subject_name', label: 'Mata Pelajaran' },
                { field: 'month', label: 'Bulan' },
            ];
        },

        addColumn(col) {
            this.config.columns.push({ ...col });
            this.$nextTick(() => this.initSortable());
        },

        removeColumn(index) {
            this.config.columns.splice(index, 1);
        },

        addFilter() {
            this.config.filters.push({ field: '', operator: '=', value: '' });
        },

        removeFilter(index) {
            this.config.filters.splice(index, 1);
        },

        onSourceChange() {
            this.config.columns = this.availableColumns().slice(0, 3);
            this.config.filters = [];
            this.config.grouping = { field: '', aggregate: 'count', aggregate_target: '*' };
            this.config.chart_config = null;
            this.fetchPreview();
        },

        async fetchPreview() {
            if (this.config.columns.length === 0) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route("admin.reports.builder.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ config: this.config }),
                });
                const data = await res.json();
                if (data.error) { alert(data.error); return; }
                this.previewRows = data.rows || [];
                this.previewColumns = data.columns || {};
                this.totalCount = data.total_count || 0;
                this.renderChart();
            } catch (e) {
                console.error('Preview error:', e);
            } finally {
                this.loading = false;
            }
        },

        renderChart() {
            if (this.chartInstance) { this.chartInstance.destroy(); this.chartInstance = null; }
            if (!this.config.chart_config || !this.previewRows.length) return;

            const cfg = this.config.chart_config;
            const labelField = cfg.label_field;
            const valueField = cfg.value_field;
            if (!labelField || !valueField) return;

            const labels = this.previewRows.map(r => r[labelField]);
            const values = this.previewRows.map(r => parseFloat(r[valueField]) || 0);

            const colors = ['rgba(37,99,235,0.6)','rgba(184,134,11,0.6)','rgba(16,185,129,0.6)','rgba(234,179,8,0.6)','rgba(220,38,38,0.6)','rgba(147,51,234,0.6)'];
            const borderColors = colors.map(c => c.replace('0.6', '1'));

            this.chartData = { type: cfg.type, labels, datasets: [{ label: cfg.label || 'Data', data: values, backgroundColor: colors, borderColor: borderColors, borderWidth: 1 }] };

            const ctx = document.getElementById('reportChart');
            if (!ctx) return;

            this.chartInstance = new Chart(ctx, {
                type: cfg.type || 'bar',
                data: { labels, datasets: this.chartData.datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top', labels: { font: { family: 'Inter' } } },
                    },
                    scales: cfg.type !== 'pie' ? {
                        y: { beginAtZero: true }
                    } : {},
                },
            });
        },

        formatCell(value) {
            if (value === null || value === undefined) return '-';
            if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
            if (typeof value === 'number' && !Number.isInteger(value)) return parseFloat(value).toFixed(2);
            return value;
        },

        async exportCsv() {
            try {
                const res = await fetch('{{ route("admin.reports.builder.export-csv") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ config: this.config }),
                });
                const blob = await res.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'laporan.csv';
                a.click();
                URL.revokeObjectURL(url);
            } catch (e) { alert('Gagal ekspor CSV'); }
        },

        async exportPdf() {
            try {
                const res = await fetch('{{ route("admin.reports.builder.export-pdf") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ config: this.config }),
                });
                const data = await res.json();
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                } else if (data.error) {
                    alert(data.error);
                }
            } catch (e) { alert('Gagal ekspor PDF'); }
        },

        async doSaveTemplate() {
            if (!this.saveForm.name) return alert('Nama template wajib diisi.');
            try {
                const body = {
                    name: this.saveForm.name,
                    description: this.saveForm.description,
                    report_type: this.saveForm.report_type,
                    config: this.config,
                    is_shared: this.saveForm.is_shared,
                };
                const res = await fetch('{{ route("admin.reports.builder.save-template") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (data.success) {
                    this.showSaveModal = false;
                    this.saveForm = { name: '', description: '', report_type: 'combined', is_shared: false };
                    this.fetchTemplates();
                }
            } catch (e) { alert('Gagal menyimpan template'); }
        },

        loadTemplate(template) {
            this.config = { ...this.config, ...template.config };
            this.$nextTick(() => { this.initSortable(); this.fetchPreview(); });
        },

        async deleteTemplate(id) {
            if (!confirm('Hapus template ini?')) return;
            try {
                await fetch('{{ route("admin.reports.builder.delete-template", ["template" => "__ID__"]) }}'.replace('__ID__', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                this.fetchTemplates();
            } catch (e) { alert('Gagal menghapus template'); }
        },

        async fetchTemplates() {
            try {
                const res = await fetch('{{ route("admin.reports.builder.templates") }}');
                const data = await res.json();
                this.templates = data.templates || [];
            } catch (e) {}
        },
    };
}
</script>

<style>
.card-header {
    font-family: 'Inter', sans-serif;
    font-size: .65rem;
    letter-spacing: .15em;
    text-transform: uppercase;
    font-weight: 600;
    color: var(--c-primary);
    padding: .85rem 1rem;
    background: rgba(11,29,58,.04);
    border-bottom: 1px solid var(--c-border, #e2e8f0);
}
.elite-card {
    background: white;
    border: 1px solid var(--c-border, #e2e8f0);
    border-radius: 8px;
    overflow: hidden;
}
[x-cloak] { display: none !important; }
</style>
@endpush
