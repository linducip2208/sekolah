<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\BudgetTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BudgetController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* ==================== DASHBOARD ==================== */

    public function dashboard(Request $request): View
    {
        $schoolId = $this->schoolId();
        $academicYearId = $request->academic_year_id;

        $categories = BudgetCategory::where('school_id', $schoolId)
            ->whereNull('parent_id')
            ->with(['children'])
            ->orderBy('code')
            ->get();

        $itemsQuery = BudgetItem::where('school_id', $schoolId)
            ->with('category');

        if ($academicYearId) {
            $itemsQuery->where('academic_year_id', $academicYearId);
        }

        $items = $itemsQuery->orderBy('name')->get();

        $totalPlanned = $items->sum('planned_amount');
        $totalActual  = $items->sum('actual_amount');

        $byType = [
            'income'  => ['planned' => 0, 'actual' => 0],
            'expense' => ['planned' => 0, 'actual' => 0],
        ];

        $categoryChart = [
            'labels'  => [],
            'planned' => [],
            'actual'  => [],
        ];

        foreach ($items as $item) {
            $catType = $item->category?->type ?? 'expense';
            $byType[$catType]['planned'] += $item->planned_amount;
            $byType[$catType]['actual']  += $item->actual_amount;
        }

        foreach ($categories as $cat) {
            $catItems = $items->where('budget_category_id', $cat->id);
            $p = $catItems->sum('planned_amount');
            $a = $catItems->sum('actual_amount');
            if ($p > 0 || $a > 0) {
                $categoryChart['labels'][]  = $cat->name;
                $categoryChart['planned'][] = $p / 100;
                $categoryChart['actual'][]  = $a / 100;
            }
        }

        $doughnutLabels = [];
        $doughnutData   = [];
        foreach ($items->groupBy('budget_category_id') as $cid => $group) {
            $catName = $group->first()->category?->name ?? 'Tanpa Kategori';
            $doughnutLabels[] = $catName;
            $doughnutData[]   = $group->sum('actual_amount') / 100;
        }

        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get();

        return view('school-admin.finance.budget.dashboard', compact(
            'categories', 'items', 'totalPlanned', 'totalActual',
            'byType', 'categoryChart', 'doughnutLabels', 'doughnutData',
            'academicYears', 'academicYearId'
        ));
    }

    /* ==================== CATEGORIES ==================== */

    public function categories(): View
    {
        $schoolId = $this->schoolId();
        $categories = BudgetCategory::where('school_id', $schoolId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('code')
            ->get();

        $allFlat = BudgetCategory::where('school_id', $schoolId)
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get();

        return view('school-admin.finance.budget.categories', compact('categories', 'allFlat'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'code'        => 'required|string|max:20|unique:budget_categories,code,NULL,id,school_id,' . $this->schoolId(),
            'parent_id'   => 'nullable|exists:budget_categories,id',
            'type'        => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        BudgetCategory::create([
            'school_id'   => $this->schoolId(),
            'name'        => $data['name'],
            'code'        => $data['code'],
            'parent_id'   => $data['parent_id'] ?? null,
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'Kategori anggaran ditambahkan.');
    }

    public function updateCategory(Request $request, BudgetCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'code'        => 'required|string|max:20|unique:budget_categories,code,' . $category->id . ',id,school_id,' . $this->schoolId(),
            'parent_id'   => 'nullable|exists:budget_categories,id',
            'type'        => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name'        => $data['name'],
            'code'        => $data['code'],
            'parent_id'   => $data['parent_id'] ?? null,
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'Kategori anggaran diperbarui.');
    }

    public function deleteCategory(BudgetCategory $category): RedirectResponse
    {
        if ($category->items()->exists()) {
            return back()->with('success', 'Kategori tidak dapat dihapus karena masih memiliki item anggaran.');
        }
        if ($category->children()->exists()) {
            return back()->with('success', 'Kategori tidak dapat dihapus karena masih memiliki sub-kategori.');
        }
        $category->delete();
        return back()->with('success', 'Kategori anggaran dihapus.');
    }

    /* ==================== ITEMS ==================== */

    public function items(Request $request): View
    {
        $schoolId = $this->schoolId();
        $academicYearId = $request->academic_year_id;

        $itemsQuery = BudgetItem::where('school_id', $schoolId)
            ->with(['category', 'academicYear']);

        if ($academicYearId) {
            $itemsQuery->where('academic_year_id', $academicYearId);
        }
        if ($request->status) {
            $itemsQuery->where('status', $request->status);
        }
        if ($request->category_id) {
            $itemsQuery->where('budget_category_id', $request->category_id);
        }

        $items = $itemsQuery->orderBy('name')->paginate(30);

        $categories    = BudgetCategory::where('school_id', $schoolId)->orderBy('code')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();

        return view('school-admin.finance.budget.items', compact(
            'items', 'categories', 'academicYears', 'academicYearId'
        ));
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'budget_category_id' => 'required|exists:budget_categories,id',
            'academic_year_id'   => 'nullable|exists:academic_years,id',
            'name'               => 'required|string|max:200',
            'description'        => 'nullable|string',
            'planned_amount_rp'  => 'required|numeric|min:0',
            'status'             => 'required|in:planned,approved,revised',
        ]);

        BudgetItem::create([
            'school_id'         => $this->schoolId(),
            'budget_category_id' => $data['budget_category_id'],
            'academic_year_id'  => $data['academic_year_id'] ?? null,
            'name'              => $data['name'],
            'description'       => $data['description'] ?? null,
            'planned_amount'    => (int) ($data['planned_amount_rp'] * 100),
            'actual_amount'     => 0,
            'status'            => $data['status'],
        ]);

        return back()->with('success', 'Item anggaran ditambahkan.');
    }

    public function updateItem(Request $request, BudgetItem $item): RedirectResponse
    {
        $data = $request->validate([
            'budget_category_id' => 'required|exists:budget_categories,id',
            'academic_year_id'   => 'nullable|exists:academic_years,id',
            'name'               => 'required|string|max:200',
            'description'        => 'nullable|string',
            'planned_amount_rp'  => 'required|numeric|min:0',
            'status'             => 'required|in:planned,approved,revised',
        ]);

        $item->update([
            'budget_category_id' => $data['budget_category_id'],
            'academic_year_id'   => $data['academic_year_id'] ?? null,
            'name'               => $data['name'],
            'description'        => $data['description'] ?? null,
            'planned_amount'     => (int) ($data['planned_amount_rp'] * 100),
            'status'             => $data['status'],
        ]);

        return back()->with('success', 'Item anggaran diperbarui.');
    }

    public function deleteItem(BudgetItem $item): RedirectResponse
    {
        $item->delete();
        return back()->with('success', 'Item anggaran dihapus.');
    }

    public function toggleStatusItem(BudgetItem $item): RedirectResponse
    {
        $newStatus = match ($item->status) {
            'planned'  => 'approved',
            'approved' => 'revised',
            'revised'  => 'planned',
        };
        $item->update(['status' => $newStatus]);
        return back()->with('success', 'Status item anggaran diubah ke ' . $newStatus . '.');
    }

    /* ==================== TRANSACTIONS ==================== */

    public function transactions(Request $request): View
    {
        $schoolId = $this->schoolId();
        $itemId = $request->budget_item_id;

        $txQuery = BudgetTransaction::where('school_id', $schoolId)
            ->with(['budgetItem.category', 'recordedBy']);

        if ($itemId) {
            $txQuery->where('budget_item_id', $itemId);
        }
        if ($request->from) {
            $txQuery->whereDate('transaction_date', '>=', $request->from);
        }
        if ($request->to) {
            $txQuery->whereDate('transaction_date', '<=', $request->to);
        }
        if ($request->search) {
            $txQuery->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('reference_no', 'like', "%{$request->search}%");
            });
        }

        $transactions = $txQuery->orderByDesc('transaction_date')->paginate(30);

        $items = BudgetItem::where('school_id', $schoolId)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('school-admin.finance.budget.transactions', compact('transactions', 'items', 'itemId'));
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'budget_item_id'   => 'required|exists:budget_items,id',
            'transaction_date' => 'required|date',
            'amount_rp'        => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'reference_no'     => 'nullable|string|max:100',
            'receipt'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('budget-receipts', 'public');
        }

        $amount = (int) ($data['amount_rp'] * 100);

        BudgetTransaction::create([
            'school_id'        => $this->schoolId(),
            'budget_item_id'   => $data['budget_item_id'],
            'transaction_date' => $data['transaction_date'],
            'amount'           => $amount,
            'description'      => $data['description'] ?? null,
            'reference_no'     => $data['reference_no'] ?? null,
            'receipt_path'     => $receiptPath,
            'recorded_by'      => auth()->id(),
        ]);

        $item = BudgetItem::find($data['budget_item_id']);
        if ($item) {
            $item->increment('actual_amount', $amount);
        }

        return back()->with('success', 'Transaksi anggaran dicatat.');
    }

    public function deleteTransaction(BudgetTransaction $transaction): RedirectResponse
    {
        $item = $transaction->budgetItem;
        if ($item) {
            $item->decrement('actual_amount', $transaction->amount);
        }

        if ($transaction->receipt_path) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }

        $transaction->delete();
        return back()->with('success', 'Transaksi anggaran dihapus.');
    }

    /* ==================== EXPORT ==================== */

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $schoolId = $this->schoolId();

        return response()->streamDownload(function () use ($schoolId, $request) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            if ($request->type === 'items') {
                fputcsv($handle, ['Kode Kategori', 'Kategori', 'Item', 'Deskripsi', 'Rencana (Rp)', 'Realisasi (Rp)', 'Progress %', 'Status']);
                $items = BudgetItem::where('school_id', $schoolId)
                    ->with('category')
                    ->when($request->academic_year_id, fn($q) => $q->where('academic_year_id', $request->academic_year_id))
                    ->orderBy('name')
                    ->get();
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->category?->code,
                        $item->category?->name,
                        $item->name,
                        $item->description,
                        $item->planned_amount / 100,
                        $item->actual_amount / 100,
                        $item->progress_percent,
                        $item->status,
                    ]);
                }
            } else {
                fputcsv($handle, ['Tanggal', 'Item', 'Kategori', 'Jumlah (Rp)', 'Deskripsi', 'No. Referensi']);
                $txs = BudgetTransaction::where('school_id', $schoolId)
                    ->with('budgetItem.category')
                    ->when($request->budget_item_id, fn($q) => $q->where('budget_item_id', $request->budget_item_id))
                    ->orderByDesc('transaction_date')
                    ->get();
                foreach ($txs as $tx) {
                    fputcsv($handle, [
                        $tx->transaction_date->format('Y-m-d'),
                        $tx->budgetItem?->name,
                        $tx->budgetItem?->category?->name,
                        $tx->amount / 100,
                        $tx->description,
                        $tx->reference_no,
                    ]);
                }
            }

            fclose($handle);
        }, 'anggaran-' . date('Ymd-His') . '.csv');
    }
}
