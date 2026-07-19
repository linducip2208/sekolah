@extends('layouts.school-admin')
@section('title', 'Kalender Akademik')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Calendarium</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Kalender Akademik</h1>
        <div class="elite-rule"></div>
        <p class="font-serif text-sm text-gray-600 mt-3">Kelola kalender akademik, libur, ujian, dan event sekolah.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.calendar.ical') }}" class="btn-elite-gold text-xs" target="_blank">
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Subscribe iCal
        </a>
        <button type="button" onclick="openCreateModal()" class="btn-elite">+ Tambah Event</button>
    </div>
</div>

<div class="bg-white border border-rule p-4 mb-4 flex flex-wrap gap-3 items-center">
    <select id="filter-type" class="border-2 border-rule px-3 py-2 font-serif text-sm" onchange="applyFilter()">
        <option value="">— Semua Jenis —</option>
        <option value="holiday">Libur</option>
        <option value="exam">Ujian</option>
        <option value="meeting">Rapat</option>
        <option value="extracurricular">Ekstrakurikuler</option>
        <option value="other">Lainnya</option>
    </select>
    <select id="filter-class" class="border-2 border-rule px-3 py-2 font-serif text-sm" onchange="applyFilter()">
        <option value="">— Semua Rombel —</option>
        @foreach($classSections as $cs)
            <option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
        @endforeach
    </select>
    <button onclick="calendar.today()" class="text-xs underline ink-secondary hover:ink-accent px-2">Hari Ini</button>
</div>

<div id="calendar" class="bg-white border border-rule p-4"></div>

{{-- Create / Edit Modal --}}
<div id="event-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" onclick="if(event.target===this) closeModal()">
    <div class="bg-white max-w-lg w-full mx-4 rounded-none border-2 border-[var(--c-primary)] shadow-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-[var(--c-primary)] text-white px-6 py-4 flex justify-between items-center">
            <h2 class="font-display text-xl" id="modal-title">Tambah Event</h2>
            <button onclick="closeModal()" class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <form id="event-form" method="POST" action="{{ route('admin.calendar.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="id" id="event-id">
            <div>
                <label class="block elite-kicker text-[.6rem] mb-1">Judul Event</label>
                <input type="text" name="title" id="event-title" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="block elite-kicker text-[.6rem] mb-1">Deskripsi</label>
                <textarea name="description" id="event-desc" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Jenis</label>
                    <select name="event_type" id="event-type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="holiday">Libur</option>
                        <option value="exam">Ujian</option>
                        <option value="meeting">Rapat</option>
                        <option value="extracurricular">Ekstrakurikuler</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Rombel (opsional)</label>
                    <select name="class_section_id" id="event-class" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Semua —</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Tanggal Mulai</label>
                    <input type="datetime-local" name="start_date" id="event-start" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Tanggal Selesai</label>
                    <input type="datetime-local" name="end_date" id="event-end" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Warna</label>
                    <input type="color" name="color" id="event-color" value="#2563EB" class="h-9 w-16 border-2 border-rule">
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="all_day" id="event-all-day" value="1" checked class="w-4 h-4">
                    <label for="event-all-day" class="text-sm font-serif">Sehari penuh</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="deleteEvent()" id="btn-delete" class="text-xs text-red-700 underline hidden">Hapus</button>
                <button type="button" onclick="closeModal()" class="text-xs text-gray-500 underline">Batal</button>
                <button type="submit" class="btn-elite">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('head')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    :root { --fc-border-color: rgba(11,29,58,.15); --fc-today-bg-color: rgba(11,29,58,.03); --fc-button-bg-color: var(--c-primary); --fc-button-border-color: var(--c-primary); --fc-button-hover-bg-color: var(--c-secondary); --fc-button-hover-border-color: var(--c-secondary); --fc-button-active-bg-color: var(--c-accent); --fc-button-active-border-color: var(--c-accent); }
    .fc .fc-toolbar-title { font-family: 'Playfair Display', Georgia, serif; font-weight: 600; font-size: 1.3rem !important; color: var(--c-ink); }
    .fc .fc-button { font-family: 'Inter', sans-serif; text-transform: uppercase; font-size: .65rem !important; letter-spacing: .08em; font-weight: 600; padding: .5rem 1rem !important; border-radius: 0 !important; }
    .fc .fc-col-header-cell-cushion { font-family: 'Inter', sans-serif; text-transform: uppercase; font-size: .62rem; letter-spacing: .06em; font-weight: 600; color: #6b6660; }
    .fc .fc-daygrid-event { border-radius: 0; font-size: .72rem; font-family: 'Inter', sans-serif; padding: 2px 4px; }
    .fc .fc-daygrid-day-number { font-family: 'Cormorant Garamond', Georgia, serif; font-size: .95rem; color: var(--c-ink); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
let calendar;
let currentEvent = null;

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' },
        locale: 'id',
        firstDay: 1,
        height: 'auto',
        events: {
            url: '{{ route("admin.calendar.feed") }}',
            method: 'GET',
            extraParams: () => ({
                class_section_id: document.getElementById('filter-class').value,
                event_type: document.getElementById('filter-type').value,
            }),
        },
        eventClick: (info) => editEvent(info.event),
        dateClick: (info) => openCreateModal(info.dateStr),
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    });
    calendar.render();
});

function applyFilter() {
    calendar.refetchEvents();
}

function openCreateModal(dateStr) {
    currentEvent = null;
    document.getElementById('modal-title').textContent = 'Tambah Event';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('event-form').action = '{{ route("admin.calendar.store") }}';
    document.getElementById('event-id').value = '';
    document.getElementById('event-title').value = '';
    document.getElementById('event-desc').value = '';
    document.getElementById('event-type').value = 'other';
    document.getElementById('event-class').value = '';
    document.getElementById('event-color').value = '#2563EB';
    document.getElementById('event-all-day').checked = true;
    document.getElementById('btn-delete').classList.add('hidden');

    if (dateStr) {
        document.getElementById('event-start').value = dateStr.includes('T') ? dateStr : dateStr + 'T08:00';
        document.getElementById('event-end').value = '';
    }

    document.getElementById('event-modal').classList.remove('hidden');
    document.getElementById('event-modal').classList.add('flex');
}

function editEvent(event) {
    currentEvent = event;
    const props = event.extendedProps;
    document.getElementById('modal-title').textContent = 'Edit Event';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('event-form').action = '{{ url("/admin/calendar") }}/' + event.id;
    document.getElementById('event-id').value = event.id;
    document.getElementById('event-title').value = event.title;
    document.getElementById('event-desc').value = props.description || '';
    document.getElementById('event-type').value = props.event_type || 'other';
    document.getElementById('event-class').value = props.class_section_id || '';
    document.getElementById('event-color').value = event.backgroundColor || '#2563EB';
    document.getElementById('event-all-day').checked = event.allDay;
    document.getElementById('event-start').value = event.start ? event.start.toISOString().slice(0, 16) : '';
    document.getElementById('event-end').value = event.end ? event.end.toISOString().slice(0, 16) : '';
    document.getElementById('btn-delete').classList.remove('hidden');
    document.getElementById('btn-delete').onclick = () => deleteEvent(event.id);

    document.getElementById('event-modal').classList.remove('hidden');
    document.getElementById('event-modal').classList.add('flex');
}

function deleteEvent(id) {
    if (!confirm('Hapus event ini?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ url("/admin/calendar") }}/' + (id || document.getElementById('event-id').value);
    form.innerHTML = '@csrf @method("DELETE")';
    document.body.appendChild(form);
    form.submit();
}

function closeModal() {
    document.getElementById('event-modal').classList.add('hidden');
    document.getElementById('event-modal').classList.remove('flex');
}

document.getElementById('event-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const method = document.getElementById('form-method').value;
    const form = this;
    const formData = new FormData(form);
    const url = form.action;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData,
    }).then(r => {
        if (r.redirected) {
            window.location.href = r.url;
        } else if (r.ok) {
            closeModal();
            calendar.refetchEvents();
            return r.json().then(data => {
                if (data.success) alert(data.message || 'Event disimpan.');
            });
        }
    }).catch(err => { console.error(err); closeModal(); calendar.refetchEvents(); });
});
</script>
@endpush
