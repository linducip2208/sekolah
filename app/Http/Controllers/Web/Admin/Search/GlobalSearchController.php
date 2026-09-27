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

        // Batch-load user names once (avoids N+1 per row).
        $userIds = collect($grouped['students'] ?? [])->pluck('user_id')
            ->merge(collect($grouped['staff'] ?? [])->pluck('user_id'))
            ->merge(collect($grouped['users'] ?? [])->pluck('id'))
            ->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $names = $userIds === []
            ? []
            : DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id')->all();

        $seenStudents = [];
        foreach ($grouped['students'] ?? [] as $s) {
            $seenStudents[(int) ($s['id'] ?? 0)] = true;
            $results[] = [
                'type'  => 'student',
                'icon'  => 'user',
                'title' => $names[(int) ($s['user_id'] ?? 0)] ?? '—',
                'sub'   => 'NIS ' . ($s['admission_no'] ?? '—'),
                'url'   => route('admin.students.edit', $s['id']),
            ];
        }

        // Users matched by name/email: resolve students among them.
        $extraUserIds = collect($grouped['users'] ?? [])->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        if ($extraUserIds !== []) {
            $extraStudents = DB::table('students')
                ->where('school_id', auth()->user()->school_id)
                ->whereIn('user_id', $extraUserIds)
                ->select('id', 'user_id', 'admission_no')
                ->get();
            foreach ($extraStudents as $s) {
                if (isset($seenStudents[(int) $s->id])) {
                    continue;
                }
                $seenStudents[(int) $s->id] = true;
                $results[] = [
                    'type'  => 'student',
                    'icon'  => 'user',
                    'title' => $names[(int) $s->user_id] ?? '—',
                    'sub'   => 'NIS ' . ($s->admission_no ?? '—'),
                    'url'   => route('admin.students.edit', $s->id),
                ];
            }
        }
        foreach ($grouped['staff'] ?? [] as $s) {
            $results[] = [
                'type'  => 'staff',
                'icon'  => 'users',
                'title' => $names[(int) ($s['user_id'] ?? 0)] ?? '—',
                'sub'   => ($s['employee_id'] ?? '—') . ' · ' . ($s['designation'] ?? '—'),
                'url'   => route('admin.staff.edit', $s['id']),
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
