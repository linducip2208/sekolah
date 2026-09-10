<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\MyWorkService;
use Illuminate\View\View;

/**
 * My Work — pusat pekerjaan: semua hal yang butuh aksi pengguna,
 * dikelompokkan per prioritas dengan CTA langsung.
 */
class MyWorkController extends Controller
{
    public function index(MyWorkService $service): View
    {
        $user = auth()->user();
        $items = $service->items((int) $user->school_id, $user);

        $grouped = [
            'critical' => ['label' => 'Kritis', 'desc' => 'Perlu ditangani hari ini', 'tone' => 'danger', 'icon' => 'alert', 'items' => []],
            'high' => ['label' => 'Penting', 'desc' => 'Selesaikan dalam 1–2 hari', 'tone' => 'warning', 'icon' => 'clock', 'items' => []],
            'normal' => ['label' => 'Normal', 'desc' => 'Kerjakan minggu ini', 'tone' => 'info', 'icon' => 'check', 'items' => []],
        ];
        foreach ($items as $item) {
            $grouped[$item['priority']]['items'][] = $item;
        }

        return view('school-admin.my-work', [
            'grouped' => $grouped,
            'total' => count($items),
        ]);
    }
}
