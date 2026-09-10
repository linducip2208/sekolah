<?php

namespace App\Http\Controllers\Api\Canteen;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Canteen\CanteenCategory;
use App\Models\Canteen\CanteenMenuItem;
use App\Models\Canteen\CanteenOrder;
use App\Models\Canteen\CanteenWallet;
use App\Services\Canteen\CanteenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CanteenController extends Controller
{
    public function __construct(private CanteenService $service) {}

    public function menu(Request $request): JsonResponse
    {
        $items = CanteenMenuItem::where('school_id', $request->user()->school_id)
            ->where('is_available', true)
            ->orderBy('canteen_category_id')->orderBy('name')
            ->get();

        $cats = CanteenCategory::where('school_id', $request->user()->school_id)->get()->keyBy('id');

        return response()->json([
            'categories' => $cats->values(),
            'items' => $items,
        ]);
    }

    public function wallet(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);
        $wallet = $this->service->getOrCreateWallet($request->user()->school_id, $studentId);

        return response()->json([
            'wallet' => $wallet,
            'balance' => $this->service->ledgerBalance($wallet),
        ]);
    }

    public function topup(Request $request, int $studentId): JsonResponse
    {
        // A student or linked parent may top up their own wallet. Management
        // permissions remain required for locking, refunds, and settlement.
        $this->authorizeStudent($request, $studentId);
        $data = $request->validate([
            'amount' => 'required|integer|min:100',
            'payment_transaction_id' => 'nullable|integer',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $topup = $this->service->topUp(
            $request->user()->school_id,
            $studentId,
            $request->user()->id,
            $data['amount'],
            $data['payment_transaction_id'] ?? null,
            $data['idempotency_key'] ?? $request->header('Idempotency-Key'),
        );

        return response()->json($topup, 201);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'source' => 'nullable|in:preorder,walkin',
            'pickup_at' => 'nullable|date',
            'idempotency_key' => 'nullable|string|max:120',
        ]);

        $this->authorizeStudent($request, (int) $data['student_id']);

        try {
            $order = $this->service->placeOrder(
                $request->user()->school_id,
                $data['student_id'],
                $data['items'],
                $data['source'] ?? 'walkin',
                isset($data['pickup_at']) ? new \DateTimeImmutable($data['pickup_at']) : null,
                $data['idempotency_key'] ?? $request->header('Idempotency-Key'),
            );

            return response()->json($order, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function ordersToday(Request $request): JsonResponse
    {
        abort_unless($this->canManage($request), 403, 'Tidak memiliki akses transaksi kantin.');
        $orders = CanteenOrder::where('school_id', $request->user()->school_id)
            ->whereDate('created_at', today())
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        abort_unless($this->canManage($request), 403, 'Tidak memiliki akses transaksi kantin.');
        $request->validate(['status' => 'required|in:pending,preparing,ready,picked_up,cancelled']);

        $order = CanteenOrder::where('school_id', $request->user()->school_id)->findOrFail($id);

        return response()->json($this->service->updateOrderStatus($order, $request->input('status')));
    }

    public function lockWallet(Request $request, int $walletId): JsonResponse
    {
        $request->validate(['is_locked' => 'required|boolean']);
        $wallet = CanteenWallet::where('school_id', $request->user()->school_id)->findOrFail($walletId);
        $this->authorizeStudent($request, (int) $wallet->student_id, true);
        $wallet->update(['is_locked' => $request->boolean('is_locked')]);

        return response()->json($wallet);
    }

    public function transactions(Request $request, int $studentId): JsonResponse
    {
        $this->authorizeStudent($request, $studentId);

        return response()->json($this->service->transactions(
            (int) $request->user()->school_id,
            $studentId,
            min((int) $request->input('per_page', 25), 100),
        ));
    }

    public function refund(Request $request, int $id): JsonResponse
    {
        abort_unless($this->canManage($request), 403, 'Tidak memiliki akses refund kantin.');
        $data = $request->validate([
            'amount' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'idempotency_key' => 'nullable|string|max:120',
        ]);
        $order = CanteenOrder::where('school_id', $request->user()->school_id)->findOrFail($id);

        return response()->json($this->service->refundOrder(
            $order,
            $data['amount'],
            $data['reason'],
            $data['idempotency_key'] ?? $request->header('Idempotency-Key'),
        ), 201);
    }

    private function authorizeStudent(Request $request, int $studentId, bool $managementAllowed = false): void
    {
        $user = $request->user();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->findOrFail($studentId);
        if ($managementAllowed && $this->canManage($request)) {
            return;
        }
        if ($this->canManage($request)) {
            return;
        }
        if ($user->hasRole('student') && (int) $student->user_id === (int) $user->id) {
            return;
        }
        if ($user->hasRole('parent') && $student->parents()->whereKey($user->id)->exists()) {
            return;
        }
        abort(403, 'Tidak boleh mengakses wallet siswa ini.');
    }

    private function canManage(Request $request): bool
    {
        $user = $request->user();

        return $user->can('canteen.manage') || $user->hasAnyRole([
            'admin', 'principal', 'accountant', 'canteen_manager', 'school_admin', 'super_admin',
        ]);
    }
}
