<?php

namespace App\Services\Canteen;

use App\Models\Canteen\CanteenMenuItem;
use App\Models\Canteen\CanteenOrder;
use App\Models\Canteen\CanteenTopup;
use App\Models\Canteen\CanteenWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CanteenService
{
    public function getOrCreateWallet(int $schoolId, int $studentId): CanteenWallet
    {
        return CanteenWallet::firstOrCreate(
            ['school_id' => $schoolId, 'student_id' => $studentId],
            ['balance' => 0],
        );
    }

    public function topUp(int $schoolId, int $studentId, int $initiatorId, int $amountCents, ?int $paymentTransactionId = null): CanteenTopup
    {
        return DB::transaction(function () use ($schoolId, $studentId, $initiatorId, $amountCents, $paymentTransactionId) {
            $wallet = $this->getOrCreateWallet($schoolId, $studentId);

            $topup = CanteenTopup::create([
                'school_id'              => $schoolId,
                'canteen_wallet_id'      => $wallet->id,
                'initiated_by'           => $initiatorId,
                'payment_transaction_id' => $paymentTransactionId,
                'amount'                 => $amountCents,
                'status'                 => $paymentTransactionId ? 'pending' : 'completed',
            ]);

            if ($topup->status === 'completed') {
                $wallet->increment('balance', $amountCents);
            }

            return $topup;
        });
    }

    public function completeTopup(CanteenTopup $topup): CanteenTopup
    {
        return DB::transaction(function () use ($topup) {
            if ($topup->status === 'completed') return $topup;

            $topup->update(['status' => 'completed']);
            CanteenWallet::where('id', $topup->canteen_wallet_id)
                ->increment('balance', $topup->amount);

            return $topup->fresh();
        });
    }

    public function placeOrder(int $schoolId, int $studentId, array $items, string $source = 'walkin', ?\DateTimeInterface $pickupAt = null): CanteenOrder
    {
        return DB::transaction(function () use ($schoolId, $studentId, $items, $source, $pickupAt) {
            $wallet = CanteenWallet::where('school_id', $schoolId)
                ->where('student_id', $studentId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->is_locked) {
                throw new \RuntimeException('Wallet is locked by parent');
            }

            $orderItems = [];
            $total      = 0;

            foreach ($items as $line) {
                $menu = CanteenMenuItem::where('school_id', $schoolId)
                    ->where('id', $line['menu_item_id'])
                    ->firstOrFail();

                if (!$menu->is_available) {
                    throw new \RuntimeException("Menu {$menu->name} not available");
                }

                $blocked = (array) ($wallet->blocked_categories ?? []);
                if (in_array($menu->canteen_category_id, $blocked, true)) {
                    throw new \RuntimeException("Category for {$menu->name} blocked by parent");
                }

                $qty       = (int) ($line['qty'] ?? 1);
                $subtotal  = $menu->price * $qty;
                $total    += $subtotal;

                $orderItems[] = [
                    'menu_item_id' => $menu->id,
                    'name'         => $menu->name,
                    'price'        => $menu->price,
                    'qty'          => $qty,
                    'subtotal'     => $subtotal,
                ];

                if ($menu->stock_today !== null) {
                    if ($menu->stock_today < $qty) {
                        throw new \RuntimeException("Stock {$menu->name} habis");
                    }
                    $menu->decrement('stock_today', $qty);
                }
            }

            if ($wallet->balance < $total) {
                throw new \RuntimeException('Saldo tidak cukup');
            }

            if ($wallet->daily_limit > 0) {
                $todaySpent = CanteenOrder::where('canteen_wallet_id', $wallet->id)
                    ->whereDate('created_at', today())
                    ->where('status', '!=', 'cancelled')
                    ->sum('total');
                if ($todaySpent + $total > $wallet->daily_limit) {
                    throw new \RuntimeException('Daily limit exceeded');
                }
            }

            $order = CanteenOrder::create([
                'school_id'         => $schoolId,
                'student_id'        => $studentId,
                'canteen_wallet_id' => $wallet->id,
                'order_no'          => 'CO-' . strtoupper(Str::random(10)),
                'pickup_at'         => $pickupAt,
                'items'             => $orderItems,
                'total'             => $total,
                'source'            => $source,
                'status'            => 'pending',
            ]);

            $wallet->decrement('balance', $total);

            return $order;
        });
    }

    public function updateOrderStatus(CanteenOrder $order, string $status): CanteenOrder
    {
        $order->update(['status' => $status]);
        return $order->fresh();
    }
}
