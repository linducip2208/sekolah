# Module 13 — Subscription & SaaS Billing

## Depends On
Module 01 (multi-tenant foundation — plans table, schools table)

## What to Build
Manajemen langganan sekolah. Super admin kelola plan, perpanjang/upgrade/suspend sekolah.
Notifikasi expired, grace period, dan read-only mode untuk sekolah expired.

---

## Database Schema

```php
// subscription_transactions table
Schema::create('subscription_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('plan_id')->constrained();
    $table->string('transaction_no')->unique();
    $table->unsignedInteger('amount');          // integer cents
    $table->string('payment_mode');             // manual | transfer | gateway
    $table->string('reference')->nullable();    // bukti transfer / transaction ID
    $table->string('months')->default(1);       // durasi berlangganan
    $table->date('start_date');
    $table->date('end_date');
    $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
    $table->text('note')->nullable();
    $table->foreignId('processed_by')->nullable()->constrained('users'); // super_admin
    $table->timestamps();
    $table->index(['school_id', 'status']);
});
```

---

## Middleware: SubscriptionCheck

```php
// app/Http/Middleware/EnsureActiveSubscription.php
class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = app('current_school');

        // Super admin bypass
        if ($request->user()?->hasRole('super_admin')) {
            return $next($request);
        }

        // Cek expired
        if ($school->plan_expires_at && $school->plan_expires_at->isPast()) {
            // Grace period: 7 hari setelah expired masih bisa akses read-only
            $gracePeriodEnd = $school->plan_expires_at->addDays(7);

            if (now()->isAfter($gracePeriodEnd)) {
                return response()->json([
                    'message' => __('subscription.expired'),
                    'expired_at' => $school->plan_expires_at,
                ], 402);
            }

            // Dalam grace period: tambahkan header warning
            $response = $next($request);
            $response->headers->set('X-Subscription-Warning', 'grace_period');
            $response->headers->set('X-Grace-Period-Ends', $gracePeriodEnd->toIso8601String());
            return $response;
        }

        return $next($request);
    }
}
```

---

## API Endpoints

| Method | URI                                                | Role        | Deskripsi                          |
|--------|----------------------------------------------------|-------------|------------------------------------|
| GET    | `/api/v1/subscription/current`                     | admin       | Info langganan aktif sekolah ini   |
| GET    | `/api/v1/subscription/plans`                       | public      | List plan yang tersedia            |
| POST   | `/api/v1/super/schools/{id}/subscription/extend`   | super_admin | Perpanjang langganan               |
| POST   | `/api/v1/super/schools/{id}/subscription/upgrade`  | super_admin | Upgrade plan                       |
| POST   | `/api/v1/super/schools/{id}/suspend`               | super_admin | Suspend sekolah                    |
| POST   | `/api/v1/super/schools/{id}/activate`              | super_admin | Aktifkan kembali                   |
| GET    | `/api/v1/super/subscriptions`                      | super_admin | List semua transaksi langganan     |
| POST   | `/api/v1/super/subscriptions`                      | super_admin | Catat transaksi baru               |

---

## SubscriptionService Implementation

```php
// app/Services/SubscriptionService.php
class SubscriptionService
{
    public function extend(School $school, int $planId, int $months, array $paymentData): School
    {
        return DB::transaction(function () use ($school, $planId, $months, $paymentData) {
            $plan = Plan::findOrFail($planId);

            // Jika sudah expired, mulai dari hari ini; jika belum, extend dari sisa
            $startDate = $school->plan_expires_at && $school->plan_expires_at->isFuture()
                ? $school->plan_expires_at
                : today();

            $endDate = $startDate->copy()->addMonths($months);

            SubscriptionTransaction::create([
                'school_id'      => $school->id,
                'plan_id'        => $planId,
                'transaction_no' => $this->generateTransactionNo(),
                'amount'         => $plan->price * $months,
                'payment_mode'   => $paymentData['payment_mode'],
                'reference'      => $paymentData['reference'] ?? null,
                'months'         => $months,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'status'         => 'completed',
                'processed_by'   => auth()->id(),
            ]);

            $school->update([
                'plan_id'          => $planId,
                'plan_expires_at'  => $endDate,
                'is_active'        => true,
            ]);

            // Notify school admin
            NotifySubscriptionExtendedJob::dispatch($school, $endDate);

            return $school->fresh('plan');
        });
    }
}
```

---

## Scheduler: Subscription Expiry Alerts

```php
// app/Console/Commands/CheckSubscriptionExpiry.php
// Runs daily at 08:00
// - 7 hari sebelum expired: kirim notifikasi ke school admin
// - 3 hari sebelum expired: kirim notifikasi ke school admin + super admin
// - Hari H expired: suspend fitur write, set read-only mode
// - 7 hari setelah expired: suspend akses penuh

$schedule->command('subscription:check-expiry')->dailyAt('08:00');
```

---

## Plan Feature Gates

```php
// app/Services/PlanFeatureService.php
class PlanFeatureService
{
    public function can(School $school, string $feature): bool
    {
        $plan = $school->plan;
        if (!$plan) return false;

        $features = $plan->features;
        return in_array('*', $features) || in_array($feature, $features);
    }
}

// Usage dalam controller:
if (!app(PlanFeatureService::class)->can($school, 'library')) {
    abort(403, __('subscription.feature_not_available'));
}
```

---

## Acceptance Criteria

- [ ] Extend langganan memperpanjang `plan_expires_at` dari sisa masa aktif
- [ ] Sekolah expired mendapat grace period 7 hari (read-only)
- [ ] Setelah grace period: seluruh akses ditolak dengan HTTP 402
- [ ] Notifikasi dikirim H-7 dan H-3 sebelum expired
- [ ] Feature gate mencegah akses fitur yang tidak ada di plan
- [ ] Super admin bisa suspend/aktifkan sekolah secara manual

## Tests to Write

```
tests/Feature/Subscription/
  ExtendSubscriptionTest.php
  GracePeriodTest.php
  ExpiredAccessTest.php
  FeatureGateTest.php
  SuspendSchoolTest.php
  ExpiryNotificationTest.php
```
