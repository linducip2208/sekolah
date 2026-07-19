<x-mail::message>
# 📅 Laporan Harian — {{ $report->report_date->format('d M Y') }}

Berikut ringkasan aktivitas anak Anda hari ini:

@if($report->attendance)
<x-mail::panel>
**🎒 Kehadiran**: {{ ucfirst($report->attendance['status'] ?? '-') }}
@if(!empty($report->attendance['note']))<br>Catatan: {{ $report->attendance['note'] }}@endif
</x-mail::panel>
@endif

@if($report->canteen_summary)
<x-mail::panel>
**🍱 Kantin**: {{ $report->canteen_summary['orders'] ?? 0 }} pesanan
- Total belanja: Rp {{ number_format(($report->canteen_summary['total'] ?? 0) / 100, 0, ',', '.') }}
</x-mail::panel>
@endif

@if($report->clinic_visit)
<x-mail::panel>
**🏥 Kunjungan UKS**

- Keluhan: {{ $report->clinic_visit['symptoms'] ?? '-' }}
@if(!empty($report->clinic_visit['diagnosis']))
- Diagnosis: {{ $report->clinic_visit['diagnosis'] }}
@endif
@if(!empty($report->clinic_visit['sent_home']))
- ⚠️ Anak dipulangkan dari sekolah
@endif
</x-mail::panel>
@endif

@if($report->wellness_checkin)
<x-mail::panel>
**😊 Mood Check-in**: {{ $report->wellness_checkin['mood_score'] ?? '-' }}/10
</x-mail::panel>
@endif

@if($report->discipline_events && count($report->discipline_events) > 0)
<x-mail::panel>
**📋 Catatan Disiplin Hari Ini**

@foreach($report->discipline_events as $ev)
- {{ $ev['description'] ?? '-' }} ({{ $ev['points'] ?? 0 }} poin)
@endforeach
</x-mail::panel>
@endif

<x-mail::button :url="config('app.url') . '/portal'">
Lihat Detail di Portal
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
