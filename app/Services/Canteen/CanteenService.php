<?php

namespace App\Services\Canteen;

use App\Models\Academic\Student;
use App\Models\Canteen\CanteenMenuItem;
use App\Models\Canteen\CanteenOrder;
use App\Models\Canteen\CanteenOrderItem;
use App\Models\Canteen\CanteenTopup;
use App\Models\Canteen\CanteenWallet;
use App\Models\Canteen\WalletRefund;
use App\Models\Canteen\WalletTransaction;
use App\Services\Finance\AccountingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cashless canteen domain service.
 *
 * `canteen_wallets.balance` is a cache for fast reads. The immutable
 * wallet_transactions ledger is authoritative for every balance calculation.
 */
class CanteenService
{
    public function __construct(private readonly AccountingService $accounting) {}

    public function getOrCreateWallet(int $schoolId, int $studentId): CanteenWallet
    {
        $this->student($schoolId, $studentId);

        return CanteenWallet::withoutGlobalScopes()->firstOrCreate(
            ['school_id' => $schoolId, 'student_id' => $studentId],
            [
                'balance' => 0,
                'daily_limit' => 0,
                'monthly_limit' => 0,
                'low_balance_threshold' => 0,
                'allow_negative' => false,
                'transfer_enabled' => false,
            ],
        );
    }

    public function currentBalance(int $schoolId, int $studentId): int
    {
        return $this->ledgerBalance($this->getOrCreateWallet($schoolId, $studentId));
    }

    public function ledgerBalance(CanteenWallet $wallet): int
    {
        $credits = (int) WalletTransaction::withoutGlobalScopes()
            ->where('school_id', $wallet->school_id)
            ->where('canteen_wallet_id', $wallet->id)
            ->where('type', 'credit')->sum('amount');
        $debits = (int) WalletTransaction::withoutGlobalScopes()
            ->where('school_id', $wallet->school_id)
            ->where('canteen_wallet_id', $wallet->id)
            ->where('type', 'debit')->sum('amount');

        return $credits - $debits;
    }

    public function topUp(
        int $schoolId,
        int $studentId,
        int $initiatorId,
        int $amountCents,
        ?int $paymentTransactionId = null,
        ?string $idempotencyKey = null,
    ): CanteenTopup {
        abort_if($amountCents <= 0, 422, 'Nominal top up harus lebih besar dari nol.');
        $this->student($schoolId, $studentId);

        return DB::transaction(function () use ($schoolId, $studentId, $initiatorId, $amountCents, $paymentTransactionId, $idempotencyKey) {
            $wallet = $this->lockedWallet($schoolId, $studentId);

            if ($idempotencyKey) {
                $existing = CanteenTopup::withoutGlobalScopes()
                    ->where('school_id', $schoolId)->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $completed = $paymentTransactionId === null;
            $topup = CanteenTopup::withoutGlobalScopes()->create([
                'school_id' => $schoolId,
                'canteen_wallet_id' => $wallet->id,
                'initiated_by' => $initiatorId,
                'payment_transaction_id' => $paymentTransactionId,
                'amount' => $amountCents,
                'status' => $completed ? 'completed' : 'pending',
                'idempotency_key' => $idempotencyKey,
                'completed_at' => $completed ? now() : null,
            ]);

            if ($completed) {
                $this->appendLedger($wallet, 'credit', 'topup', $amountCents, "topup:{$topup->id}", $initiatorId, [
                    'topup_id' => $topup->id,
                    'payment_transaction_id' => $paymentTransactionId,
                ]);
                $this->syncCachedBalance($wallet);
                $this->accounting->postCanteenTopUp($schoolId, $amountCents, "CANTEEN-TOPUP-{$topup->id}");
            }

            return $topup->fresh();
        });
    }

    public function completeTopup(CanteenTopup $topup): CanteenTopup
    {
        return DB::transaction(function () use ($topup) {
            $topup = CanteenTopup::withoutGlobalScopes()->whereKey($topup->id)->lockForUpdate()->firstOrFail();
            if ($topup->status === 'completed') {
                return $topup;
            }

            $wallet = CanteenWallet::withoutGlobalScopes()->whereKey($topup->canteen_wallet_id)->lockForUpdate()->firstOrFail();
            $topup->update(['status' => 'completed', 'completed_at' => now()]);
            $this->appendLedger($wallet, 'credit', 'topup', (int) $topup->amount, "topup:{$topup->id}", auth()->id(), [
                'topup_id' => $topup->id,
                'payment_transaction_id' => $topup->payment_transaction_id,
            ]);
            $this->syncCachedBalance($wallet);
            $this->accounting->postCanteenTopUp($topup->school_id, (int) $topup->amount, "CANTEEN-TOPUP-{$topup->id}");

            return $topup->fresh();
        });
    }

    public function placeOrder(
        int $schoolId,
        int $studentId,
        array $items,
        string $source = 'walkin',
        ?\DateTimeInterface $pickupAt = null,
        ?string $idempotencyKey = null,
    ): CanteenOrder {
        $this->student($schoolId, $studentId);

        return DB::transaction(function () use ($schoolId, $studentId, $items, $source, $pickupAt, $idempotencyKey) {
            if ($idempotencyKey) {
                $existing = CanteenOrder::withoutGlobalScopes()
                    ->where('school_id', $schoolId)->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing->load('orderItems');
                }
            }

            $wallet = $this->lockedWallet($schoolId, $studentId);
            if ($wallet->is_locked) {
                throw new \RuntimeException('Wallet dikunci oleh wali siswa.');
            }

            $orderItems = [];
            $total = 0;
            $merchantId = null;

            foreach ($items as $line) {
                $menu = CanteenMenuItem::withoutGlobalScopes()
                    ->where('school_id', $schoolId)->whereKey((int) $line['menu_item_id'])
                    ->lockForUpdate()->firstOrFail();
                if (! $menu->is_available) {
                    throw new \RuntimeException("Menu {$menu->name} tidak tersedia.");
                }

                $blocked = array_map('intval', (array) ($wallet->blocked_categories ?? []));
                if (in_array((int) $menu->canteen_category_id, $blocked, true)) {
                    throw new \RuntimeException("Kategori {$menu->name} diblokir oleh wali.");
                }

                $qty = (int) ($line['qty'] ?? 1);
                $subtotal = (int) $menu->price * $qty;
                if ($menu->stock_today !== null && $menu->stock_today < $qty) {
                    throw new \RuntimeException("Stok {$menu->name} habis.");
                }
                if ($menu->stock_today !== null) {
                    $menu->decrement('stock_today', $qty);
                }

                $merchantId ??= $menu->canteen_merchant_id;
                $total += $subtotal;
                $orderItems[] = [
                    'menu_item_id' => $menu->id,
                    'name' => $menu->name,
                    'price' => (int) $menu->price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $ledgerBalance = $this->ledgerBalance($wallet);
            if (! $wallet->allow_negative && $ledgerBalance < $total) {
                throw new \RuntimeException('Saldo tidak cukup.');
            }

            $todaySpent = $this->spentSince($wallet, today()->startOfDay());
            if ($wallet->daily_limit > 0 && $todaySpent + $total > $wallet->daily_limit) {
                throw new \RuntimeException('Batas belanja harian terlampaui.');
            }
            $monthSpent = $this->spentSince($wallet, now()->startOfMonth());
            if ($wallet->monthly_limit > 0 && $monthSpent + $total > $wallet->monthly_limit) {
                throw new \RuntimeException('Batas belanja bulanan terlampaui.');
            }

            $order = CanteenOrder::withoutGlobalScopes()->create([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'canteen_wallet_id' => $wallet->id,
                'canteen_merchant_id' => $merchantId,
                'order_no' => 'CO-'.strtoupper(Str::random(10)),
                'idempotency_key' => $idempotencyKey,
                'pickup_at' => $pickupAt,
                'items' => $orderItems,
                'total' => $total,
                'source' => $source,
                'status' => 'pending',
            ]);

            foreach ($orderItems as $line) {
                CanteenOrderItem::withoutGlobalScopes()->create([
                    'school_id' => $schoolId,
                    'canteen_order_id' => $order->id,
                    'canteen_menu_item_id' => $line['menu_item_id'],
                    'name' => $line['name'],
                    'unit_price' => $line['price'],
                    'quantity' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            $this->appendLedger($wallet, 'debit', 'purchase', $total, "order:{$order->id}", auth()->id(), [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
            ]);
            $this->syncCachedBalance($wallet);
            $this->accounting->postCanteenSale($schoolId, $total, "CANTEEN-ORDER-{$order->id}");

            return $order->load('orderItems');
        });
    }

    public function refundOrder(CanteenOrder $order, int $amountCents, string $reason, ?string $idempotencyKey = null): WalletRefund
    {
        abort_if($amountCents <= 0, 422, 'Nominal refund harus lebih besar dari nol.');

        return DB::transaction(function () use ($order, $amountCents, $reason, $idempotencyKey) {
            $order = CanteenOrder::withoutGlobalScopes()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $existing = $idempotencyKey
                ? WalletRefund::withoutGlobalScopes()->where('school_id', $order->school_id)->where('idempotency_key', $idempotencyKey)->first()
                : null;
            if ($existing) {
                return $existing;
            }

            $refunded = (int) WalletRefund::withoutGlobalScopes()
                ->where('canteen_order_id', $order->id)->where('status', 'completed')->sum('amount');
            abort_if($refunded + $amountCents > (int) $order->total, 422, 'Nominal refund melebihi nilai order.');

            $wallet = CanteenWallet::withoutGlobalScopes()->whereKey($order->canteen_wallet_id)->lockForUpdate()->firstOrFail();
            $refund = WalletRefund::withoutGlobalScopes()->create([
                'school_id' => $order->school_id,
                'canteen_wallet_id' => $wallet->id,
                'canteen_order_id' => $order->id,
                'amount' => $amountCents,
                'reason' => $reason,
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
                'requested_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            $transaction = $this->appendLedger($wallet, 'credit', 'refund', $amountCents, "refund:{$refund->id}", auth()->id(), [
                'refund_id' => $refund->id,
                'order_id' => $order->id,
            ]);
            $refund->update(['wallet_transaction_id' => $transaction->id]);
            $this->syncCachedBalance($wallet);
            $this->accounting->postCanteenRefund($order->school_id, $amountCents, "CANTEEN-REFUND-{$refund->id}");

            if ($refunded + $amountCents === (int) $order->total) {
                $order->update(['status' => 'cancelled']);
            }

            return $refund->fresh();
        });
    }

    public function updateOrderStatus(CanteenOrder $order, string $status): CanteenOrder
    {
        abort_unless(in_array($status, ['pending', 'preparing', 'ready', 'picked_up', 'cancelled'], true), 422, 'Status order tidak valid.');
        abort_if($order->status === 'picked_up' && $status !== 'picked_up', 422, 'Order yang sudah diambil tidak dapat mundur status.');
        $order->update(['status' => $status]);

        return $order->fresh('orderItems');
    }

    public function transactions(int $schoolId, int $studentId, int $perPage = 25)
    {
        $wallet = $this->getOrCreateWallet($schoolId, $studentId);

        return WalletTransaction::withoutGlobalScopes()
            ->where('school_id', $schoolId)->where('canteen_wallet_id', $wallet->id)
            ->latest('id')->paginate($perPage);
    }

    private function lockedWallet(int $schoolId, int $studentId): CanteenWallet
    {
        return CanteenWallet::withoutGlobalScopes()->where('school_id', $schoolId)
            ->where('student_id', $studentId)->lockForUpdate()->first()
            ?? $this->getOrCreateWallet($schoolId, $studentId);
    }

    private function student(int $schoolId, int $studentId): Student
    {
        $student = Student::withoutGlobalScopes()->where('school_id', $schoolId)->find($studentId);
        if (! $student) {
            throw (new ModelNotFoundException)->setModel(Student::class, [$studentId]);
        }

        return $student;
    }

    private function spentSince(CanteenWallet $wallet, \DateTimeInterface $from): int
    {
        return (int) WalletTransaction::withoutGlobalScopes()->where('school_id', $wallet->school_id)
            ->where('canteen_wallet_id', $wallet->id)->where('type', 'debit')
            ->where('transaction_type', 'purchase')->where('created_at', '>=', $from)->sum('amount');
    }

    private function appendLedger(
        CanteenWallet $wallet,
        string $type,
        string $transactionType,
        int $amount,
        string $idempotencyKey,
        ?int $initiatedBy,
        array $metadata = [],
    ): WalletTransaction {
        $existing = WalletTransaction::withoutGlobalScopes()->where('school_id', $wallet->school_id)
            ->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $balance = $this->ledgerBalance($wallet);
        $balanceAfter = $type === 'credit' ? $balance + $amount : $balance - $amount;

        return WalletTransaction::withoutGlobalScopes()->create([
            'school_id' => $wallet->school_id,
            'student_id' => $wallet->student_id,
            'canteen_wallet_id' => $wallet->id,
            'type' => $type,
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'idempotency_key' => $idempotencyKey,
            'description' => ucfirst($transactionType),
            'initiated_by' => $initiatedBy,
            'metadata' => $metadata,
        ]);
    }

    private function syncCachedBalance(CanteenWallet $wallet): void
    {
        $wallet->update(['balance' => $this->ledgerBalance($wallet)]);
    }
}
