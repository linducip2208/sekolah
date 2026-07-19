<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\BudgetCategory;
use App\Models\Finance\ProcurementApproval;
use App\Models\Finance\ProcurementItem;
use App\Models\Finance\ProcurementRequest;
use App\Models\Finance\Supplier;
use App\Services\ProcurementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProcurementController extends Controller
{
    private int $schoolId;
    private ProcurementService $service;

    public function __construct()
    {
        $this->schoolId = auth()->user()->school_id;
        $this->service = new ProcurementService($this->schoolId);
    }

    public function index(Request $request): View
    {
        $query = ProcurementRequest::where('school_id', $this->schoolId)
            ->with(['requester:id,name', 'approvedBy:id,name', 'items'])
            ->withCount('items');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('request_number', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statusCounts = [
            'draft'     => ProcurementRequest::where('school_id', $this->schoolId)->where('status', 'draft')->count(),
            'submitted' => ProcurementRequest::where('school_id', $this->schoolId)->where('status', 'submitted')->count(),
            'approved'  => ProcurementRequest::where('school_id', $this->schoolId)->where('status', 'approved')->count(),
            'ordered'   => ProcurementRequest::where('school_id', $this->schoolId)->where('status', 'ordered')->count(),
            'received'  => ProcurementRequest::where('school_id', $this->schoolId)->where('status', 'received')->count(),
            'rejected'  => ProcurementRequest::where('school_id', $this->schoolId)->where('status', 'rejected')->count(),
        ];

        return view('school-admin.finance.procurement.index', compact(
            'requests', 'statusCounts', 'status', 'search'
        ));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('school_id', $this->schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $budgetCategories = BudgetCategory::where('school_id', $this->schoolId)
            ->orderBy('name')
            ->get();

        return view('school-admin.finance.procurement.create', compact('suppliers', 'budgetCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department'        => 'nullable|string|max:100',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'estimated_budget'  => 'required|numeric|min:0',
            'urgency'           => 'required|in:low,medium,high,urgent',
            'budget_category_id'=> 'nullable|exists:budget_categories,id',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.item_name'           => 'required|string|max:255',
            'items.*.quantity'            => 'required|numeric|min:0.01',
            'items.*.unit'                => 'nullable|string|max:50',
            'items.*.estimated_unit_price'=> 'required|numeric|min:0',
            'items.*.supplier_id'         => 'nullable|exists:suppliers,id',
            'items.*.supplier_name'       => 'nullable|string|max:255',
        ]);

        $data['estimated_budget'] = (int) ($data['estimated_budget'] * 100);
        $data['requester_id'] = auth()->id();

        foreach ($data['items'] as &$item) {
            $item['estimated_unit_price'] = (int) ($item['estimated_unit_price'] * 100);
            $item['quantity'] = (float) $item['quantity'];
        }

        $procurement = $this->service->create($data);

        return redirect()->route('admin.procurement.show', $procurement->id)
            ->with('success', "Permintaan pengadaan '{$procurement->request_number}' dibuat.");
    }

    public function show(ProcurementRequest $request): View
    {
        abort_unless($request->school_id === $this->schoolId, 403);
        $request->load(['requester:id,name', 'approvedBy:id,name', 'items.supplier', 'approvals.approver:id,name']);

        $userApproval = $request->approvals
            ->firstWhere('approver_id', auth()->id());

        return view('school-admin.finance.procurement.show', compact('request', 'userApproval'));
    }

    public function edit(ProcurementRequest $request): View
    {
        abort_unless($request->school_id === $this->schoolId, 403);
        abort_unless($request->status === 'draft', 400, 'Hanya draft yang bisa diedit.');

        $request->load('items');
        $suppliers = Supplier::where('school_id', $this->schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $budgetCategories = BudgetCategory::where('school_id', $this->schoolId)
            ->orderBy('name')
            ->get();

        return view('school-admin.finance.procurement.create', compact('request', 'suppliers', 'budgetCategories'));
    }

    public function update(Request $req, ProcurementRequest $request): RedirectResponse
    {
        abort_unless($request->school_id === $this->schoolId, 403);
        abort_unless($request->status === 'draft', 400, 'Hanya draft yang bisa diedit.');

        $data = $req->validate([
            'department'        => 'nullable|string|max:100',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'estimated_budget'  => 'required|numeric|min:0',
            'urgency'           => 'required|in:low,medium,high,urgent',
            'budget_category_id'=> 'nullable|exists:budget_categories,id',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.item_name'           => 'required|string|max:255',
            'items.*.quantity'            => 'required|numeric|min:0.01',
            'items.*.unit'                => 'nullable|string|max:50',
            'items.*.estimated_unit_price'=> 'required|numeric|min:0',
            'items.*.supplier_id'         => 'nullable|exists:suppliers,id',
            'items.*.supplier_name'       => 'nullable|string|max:255',
        ]);

        $data['estimated_budget'] = (int) ($data['estimated_budget'] * 100);
        foreach ($data['items'] as &$item) {
            $item['estimated_unit_price'] = (int) ($item['estimated_unit_price'] * 100);
            $item['quantity'] = (float) $item['quantity'];
        }

        $this->service->update($request, $data);

        return redirect()->route('admin.procurement.show', $request->id)
            ->with('success', 'Permintaan pengadaan diperbarui.');
    }

    public function destroy(ProcurementRequest $request): RedirectResponse
    {
        abort_unless($request->school_id === $this->schoolId, 403);
        $num = $request->request_number;
        $request->delete();
        return redirect()->route('admin.procurement.index')
            ->with('success', "Permintaan '{$num}' dihapus.");
    }

    public function submit(ProcurementRequest $request): RedirectResponse
    {
        abort_unless($request->school_id === $this->schoolId, 403);

        try {
            $this->service->submitForApproval($request);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', "Permintaan '{$request->request_number}' disubmit untuk persetujuan.");
    }

    public function approvals(Request $request): View
    {
        $approvals = ProcurementApproval::where('status', 'pending')
            ->whereHas('request', function ($q) {
                $q->where('school_id', $this->schoolId);
            })
            ->with(['request.requester:id,name', 'request.items', 'approver:id,name'])
            ->paginate(20);

        return view('school-admin.finance.procurement.approvals', compact('approvals'));
    }

    public function decideApproval(Request $req, ProcurementApproval $approval): RedirectResponse
    {
        $procReq = $approval->request;
        abort_unless($procReq->school_id === $this->schoolId, 403);
        abort_unless($approval->approver_id === auth()->id(), 403);

        $data = $req->validate([
            'decision' => 'required|in:approved,rejected',
            'notes'    => 'nullable|string',
        ]);

        try {
            if ($data['decision'] === 'approved') {
                $this->service->approveStep($approval, $data['notes'] ?? null);
            } else {
                $this->service->rejectStep($approval, $data['notes'] ?? null);
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $label = $data['decision'] === 'approved' ? 'disetujui' : 'ditolak';
        return back()->with('success', "Permintaan '{$procReq->request_number}' {$label}.");
    }

    public function markOrdered(ProcurementRequest $request): RedirectResponse
    {
        abort_unless($request->school_id === $this->schoolId, 403);

        try {
            $this->service->markAsOrdered($request);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Permintaan '{$request->request_number}' ditandai sebagai dipesan.");
    }

    public function receiveItems(Request $req, ProcurementRequest $request): RedirectResponse
    {
        abort_unless($request->school_id === $this->schoolId, 403);

        $receivedQtys = $req->validate([
            'received_qty' => 'required|array',
            'received_qty.*' => 'required|numeric|min:0',
        ])['received_qty'];

        try {
            $this->service->receiveItems($request, $receivedQtys);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Barang diterima untuk '{$request->request_number}'.");
    }

    public function suppliers(Request $req): View
    {
        $query = Supplier::where('school_id', $this->schoolId);

        if ($search = $req->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($category = $req->get('category')) {
            $query->where('category', $category);
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('school-admin.finance.procurement.suppliers', compact('suppliers', 'search', 'category'));
    }

    public function storeSupplier(Request $req): RedirectResponse
    {
        $data = $req->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'category'       => 'required|in:atk,elektronik,furniture,catering,maintenance,other',
            'is_active'      => 'boolean',
        ]);
        $data['school_id'] = $this->schoolId;

        Supplier::create($data);
        return back()->with('success', 'Supplier ditambahkan.');
    }

    public function updateSupplier(Request $req, Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->school_id === $this->schoolId, 403);

        $data = $req->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'category'       => 'required|in:atk,elektronik,furniture,catering,maintenance,other',
            'is_active'      => 'boolean',
        ]);

        $supplier->update($data);
        return back()->with('success', 'Supplier diperbarui.');
    }

    public function deleteSupplier(Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->school_id === $this->schoolId, 403);
        $supplier->delete();
        return back()->with('success', 'Supplier dihapus.');
    }
}
