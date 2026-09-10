@props(['status'])
@php
    /*
     * Status system terpusat (spec #33) — warna konsisten antar modul.
     * Semua modul wajib lewat komponen ini, bukan badge manual.
     */
    $labels = [
        'draft' => 'Draft', 'pending' => 'Menunggu', 'submitted' => 'Terkirim',
        'in_review' => 'Ditinjau', 'under_review' => 'Ditinjau', 'review' => 'Ditinjau',
        'approved' => 'Disetujui', 'rejected' => 'Ditolak',
        'active' => 'Aktif', 'inactive' => 'Nonaktif', 'archived' => 'Arsip',
        'paid' => 'Lunas', 'partial' => 'Sebagian', 'unpaid' => 'Belum Bayar',
        'overdue' => 'Terlambat', 'refunded' => 'Dikembalikan',
        'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'canceled' => 'Dibatalkan',
        'present' => 'Hadir', 'absent' => 'Alpha', 'late' => 'Terlambat',
        'half_day' => 'Setengah Hari', 'on_leave' => 'Izin',
        'high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah', 'critical' => 'Kritis',
    ];
    $tones = [
        'draft' => 'default', 'pending' => 'warning', 'submitted' => 'info',
        'in_review' => 'info', 'under_review' => 'info', 'review' => 'info',
        'approved' => 'success', 'rejected' => 'danger',
        'active' => 'success', 'inactive' => 'default', 'archived' => 'default',
        'paid' => 'success', 'partial' => 'warning', 'unpaid' => 'warning',
        'overdue' => 'danger', 'refunded' => 'info',
        'completed' => 'success', 'cancelled' => 'danger', 'canceled' => 'danger',
        'present' => 'success', 'absent' => 'danger', 'late' => 'warning',
        'half_day' => 'warning', 'on_leave' => 'info',
        'high' => 'warning', 'medium' => 'warning', 'low' => 'success', 'critical' => 'danger',
    ];
    $key = strtolower(str_replace([' ', '-'], '_', trim((string) $status)));
    $tone = $tones[$key] ?? 'default';
    $label = $labels[$key] ?? ucfirst((string) $status);
@endphp
<x-ui.badge :variant="$tone" {{ $attributes }}>{{ $label }}</x-ui.badge>
