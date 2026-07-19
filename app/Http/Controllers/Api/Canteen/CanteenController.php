<?php

namespace App\Http\Controllers\Api\Canteen;

use App\Http\Controllers\Controller;
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
            'items'      => $items,
        ]);
    }

    public function wallet(Request $request, int $studentId): JsonResponse
    {
        $wallet = $this->service->getOrCreateWallet($request->user()->school_id, $studentId);
        return response()->json($wallet);
    }

    public function topup(Request $request, int $studentId): JsonResponse
    {
        $data = $request->validate([
            'amount'                 => 'required|integer|min:100',
            'payment_transaction_id' => 'nullable|integer',
        ]);

        $topup = $this->service->topUp(
            $request->user()->school_id,
            $studentId,
            $request->user()->id,
            $data['amount'],
            $data['payment_transaction_id'] ?? null,
        );

        return response()->json($topup, 201);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'items'      => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|integer',
            'items.*.qty'          => 'required|integer|min:1',
            'source'     => 'nullable|in:preorder,walkin',
            'pickup_at'  => 'nullable|date',
        ]);

        try {
            $order = $this->service->placeOrder(
                $request->user()->school_id,
                $data['student_id'],
                $data['items'],
                $data['source'] ?? 'walkin',
                isset($data['pickup_at']) ? new \DateTimeImmutable($data['pickup_at']) : null,
            );
            return response()->json($order, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function ordersToday(Request $request): JsonResponse
    {
        $orders = CanteenOrder::where('school_id', $request->user()->school_id)
            ->whereDate('created_at', today())
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:pending,preparing,ready,picked_up,cancelled']);

        $order = CanteenOrder::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->updateOrderStatus($order, $request->input('status')));
    }

    public function lockWallet(Request $request, int $walletId): JsonResponse
    {
        $request->validate(['is_locked' => 'required|boolean']);
        $wallet = CanteenWallet::where('school_id', $request->user()->school_id)->findOrFail($walletId);
        $wallet->update(['is_locked' => $request->boolean('is_locked')]);
        return response()->json($wallet);
    }
}
