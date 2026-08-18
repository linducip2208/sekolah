<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryAnalyticsController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $totalBooks = Book::where('school_id', $schoolId)->sum('total_quantity');
        $totalTitles = Book::where('school_id', $schoolId)->count();
        $totalMembers = BookIssue::where('school_id', $schoolId)->distinct('issued_to')->count('issued_to');

        $mostBorrowed = BookIssue::where('school_id', $schoolId)
            ->with('book:id,title,author')
            ->select('book_id', DB::raw('count(*) as borrow_count'))
            ->groupBy('book_id')
            ->orderByDesc('borrow_count')
            ->limit(10)
            ->get();

        $overdueBooks = BookIssue::where('school_id', $schoolId)
            ->where('status', 'issued')
            ->where('due_date', '<', Carbon::today())
            ->with('book:id,title')
            ->get();
        $overdueCount = $overdueBooks->count();
        $overdueValue = $overdueBooks->sum('fine_amount');

        $conditionDistribution = Book::where('school_id', $schoolId)
            ->select('rack_location', DB::raw('count(*) as total'))
            ->groupBy('rack_location')
            ->get();

        $monthlyCirculation = BookIssue::where('school_id', $schoolId)
            ->where('issue_date', '>=', Carbon::now()->subMonths(12))
            ->select(DB::raw("DATE_FORMAT(issue_date, '%Y-%m') as month"), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = Carbon::now()->subMonths($i)->format('Y-m');
            $labels[] = Carbon::now()->subMonths($i)->format('M Y');
            $values[] = $monthlyCirculation[$key] ?? 0;
        }
        $circulationTrend = ['labels' => $labels, 'values' => $values];

        return view('school-admin.analytics.library-analytics', [
            'totalBooks'            => $totalBooks,
            'totalTitles'           => $totalTitles,
            'totalMembers'          => $totalMembers,
            'mostBorrowed'          => $mostBorrowed,
            'overdueCount'          => $overdueCount,
            'overdueValue'          => $overdueValue,
            'conditionDistribution' => $conditionDistribution,
            'circulationTrend'      => $circulationTrend,
        ]);
    }
}
