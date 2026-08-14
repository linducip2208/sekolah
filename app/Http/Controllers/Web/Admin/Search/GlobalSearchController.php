<?php

namespace App\Http\Controllers\Web\Admin\Search;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request, SearchService $search): JsonResponse
    {
        $q = trim((string) $request->q);
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }
        $schoolId = auth()->user()->school_id;

        $fulltext = $search->searchSchool($schoolId, $q, 5);
        if (collect($fulltext)->flatten(1)->count() > 0) {
            $results = $this->mapFulltext($fulltext);
            return response()->json(['results' => $results]);
        }

        // Fallback to legacy LIKE
        $like = "%{$q}%";

        $results = [];

        // Students
        $students = DB::table('students as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('s.school_id', $schoolId)
            ->where(fn ($q) => $q->where('u.name', 'like', $like)
                ->orWhere('s.admission_no', 'like', $like)
                ->orWhere('u.email', 'like', $like))
            ->limit(5)
            ->select('s.id', 'u.name', 's.admission_no', 'u.email')
            ->get()
            ->map(fn ($s) => [
                'type'  => 'student',
                'icon'  => 'user',
                'title' => $s->name,
                'sub'   => 'NIS '.$s->admission_no.' · '.$s->email,
                'url'   => route('admin.students.edit', $s->id),
            ]);

        // Staff
        $staff = DB::table('staffs as st')
            ->join('users as u', 'st.user_id', '=', 'u.id')
            ->where('st.school_id', $schoolId)
            ->where(fn ($q) => $q->where('u.name', 'like', $like)
                ->orWhere('st.employee_id', 'like', $like)
                ->orWhere('u.email', 'like', $like))
            ->limit(5)
            ->select('st.id', 'u.name', 'st.employee_id', 'st.designation')
            ->get()
            ->map(fn ($s) => [
                'type'  => 'staff',
                'icon'  => 'users',
                'title' => $s->name,
                'sub'   => ($s->employee_id ?? '—').' · '.($s->designation ?? '—'),
                'url'   => route('admin.staff.edit', $s->id),
            ]);

        // Invoices
        $invoices = DB::table('fee_invoices as fi')
            ->join('students as s', 'fi.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('fi.school_id', $schoolId)
            ->where(fn ($q) => $q->where('fi.invoice_no', 'like', $like)
                ->orWhere('u.name', 'like', $like))
            ->limit(5)
            ->select('fi.id', 'fi.invoice_no', 'fi.status', 'fi.amount', 'u.name as student_name')
            ->get()
            ->map(fn ($i) => [
                'type'  => 'invoice',
                'icon'  => 'money',
                'title' => $i->invoice_no.' · '.$i->student_name,
                'sub'   => 'Rp '.number_format($i->amount/100, 0, ',', '.').' · '.$i->status,
                'url'   => route('admin.fee.invoices.show', $i->id),
            ]);

        // Notices
        $notices = DB::table('notices')->where('school_id', $schoolId)
            ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('content', 'like', $like))
            ->limit(3)
            ->select('id', 'title', 'is_published')
            ->get()
            ->map(fn ($n) => [
                'type'  => 'notice',
                'icon'  => 'bell',
                'title' => $n->title,
                'sub'   => $n->is_published ? 'Published' : 'Draft',
                'url'   => route('admin.notices.edit', $n->id),
            ]);

        $results = collect()
            ->merge($students)
            ->merge($staff)
            ->merge($invoices)
            ->merge($notices)
            ->values()->all();

        return response()->json(['results' => $results]);
    }

    private function mapFulltext(array $grouped): array
    {
        $results = [];

        foreach ($grouped['students'] ?? [] as $s) {
            $userName = DB::table('users')->where('id', $s['user_id'] ?? 0)->value('name') ?? '—';
            $results[] = [
                'type'  => 'student',
                'icon'  => 'user',
                'title' => $userName,
                'sub'   => 'NIS ' . ($s['admission_no'] ?? '—'),
                'url'   => route('admin.students.edit', $s['id']),
            ];
        }
        foreach ($grouped['staff'] ?? [] as $s) {
            $userName = DB::table('users')->where('id', $s['user_id'] ?? 0)->value('name') ?? '—';
            $results[] = [
                'type'  => 'staff',
                'icon'  => 'users',
                'title' => $userName,
                'sub'   => ($s['employee_id'] ?? '—') . ' · ' . ($s['designation'] ?? '—'),
                'url'   => route('admin.staff.edit', $s['id']),
            ];
        }
        foreach ($grouped['invoices'] ?? [] as $i) {
            $results[] = [
                'type'  => 'invoice',
                'icon'  => 'money',
                'title' => $i['invoice_no'] ?? '—',
                'sub'   => money($i['amount'] ?? 0) . ' · ' . ($i['status'] ?? '—'),
                'url'   => route('admin.fee.invoices.show', $i['id']),
            ];
        }
        foreach ($grouped['notices'] ?? [] as $n) {
            $results[] = [
                'type'  => 'notice',
                'icon'  => 'bell',
                'title' => $n['title'] ?? '—',
                'sub'   => 'Pengumuman',
                'url'   => route('admin.notices.edit', $n['id']),
            ];
        }
        return $results;
    }
}
