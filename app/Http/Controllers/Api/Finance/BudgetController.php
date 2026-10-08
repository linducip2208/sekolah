<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Api\Concerns\ConvertsRupiah;
use App\Http\Controllers\Controller;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\BudgetTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * RKAS / anggaran (docs §admin §3). Mobile contract: whole rupiah in/out.
 */
class BudgetController extends Controller
{
    use ConvertsRupiah;

    public function dashboard(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.view');
        $schoolId = $request->user()->school_id;

        $categories = BudgetCategory::where('school_id', $schoolId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('code')->get();

        $items = BudgetItem::where('school_id', $schoolId)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')->paginate(50);

        $planned = BudgetItem::where('school_id', $schoolId)->sum('planned_amount');
        $actual = BudgetItem::where('school_id', $schoolId)->sum('actual_amount');

        return response()->json($this->inRupiah([
            'categories' => $categories,
            'items' => $items,
            'planned_total' => (int) $planned,
            'actual_total' => (int) $actual,
        ]));
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.manage');
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20',
            'parent_id' => 'nullable|exists:budget_categories,id',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);
        $data['school_id'] = $request->user()->school_id;

        return response()->json(
            BudgetCategory::create($data)->fresh(), 201);
    }

    public function storeItem(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.manage');
        $data = $request->validate([
            'budget_category_id' => 'required|exists:budget_categories,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            // Mobile contract: whole rupiah.
            'planned_amount' => 'required|integer|min:0',
            'status' => 'required|in:planned,approved,revised',
        ]);

        $item = BudgetItem::create([
            'school_id' => $request->user()->school_id,
            'budget_category_id' => $data['budget_category_id'],
            'academic_year_id' => $data['academic_year_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'planned_amount' => $this->toCents((int) $data['planned_amount']),
            'actual_amount' => 0,
            'status' => $data['status'],
        ]);

        return response()->json($this->inRupiah($item->fresh()), 201);
    }

    public function storeTransaction(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.manage');
        $data = $request->validate([
            'budget_item_id' => 'required|exists:budget_items,id',
            'transaction_date' => 'required|date',
            // Mobile contract: whole rupiah.
            'amount' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string|max:100',
        ]);

        $tx = BudgetTransaction::create([
            'school_id' => $request->user()->school_id,
            'budget_item_id' => $data['budget_item_id'],
            'transaction_date' => $data['transaction_date'],
            'amount' => $this->toCents((int) $data['amount']),
            'description' => $data['description'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'recorded_by' => $request->user()->id,
        ]);

        return response()->json($this->inRupiah($tx->fresh()), 201);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless(
            $request->user()->hasRole('super_admin') || $request->user()->can($permission),
            403,
            'Tidak memiliki izin anggaran.'
        );
    }
}
