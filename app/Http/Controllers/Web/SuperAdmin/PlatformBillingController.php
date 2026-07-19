<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubscriptionTransaction;
use App\Models\Plan;
use App\Models\Platform\BillingAccount;
use App\Models\Platform\PaymentGateway;
use App\Models\Platform\SchoolRegistration;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlatformBillingController extends Controller
{
    /**
     * Pendaftaran sekolah baru — list + filter status.
     */
    public function registrations(Request $request): View
    {
        $registrations = SchoolRegistration::query()
            ->with(['plan', 'activatedSchool'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(fn ($s) => $s
                ->where('school_name', 'like', "%{$request->search}%")
                ->orWhere('admin_email', 'like', "%{$request->search}%")
                ->orWhere('subdomain', 'like', "%{$request->search}%")))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = SchoolRegistration::query()
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return view('super-admin.registrations.index', compact('registrations', 'counts'));
    }

    public function showRegistration(SchoolRegistration $registration): View
    {
        return view('super-admin.registrations.show', [
            'registration' => $registration->load(['plan', 'activatedSchool']),
        ]);
    }

    /**
     * Konfirmasi pembayaran manual → buat School + admin user + record langganan + assign plan.
     */
    public function approveRegistration(Request $request, SchoolRegistration $registration): RedirectResponse
    {
        if ($registration->status === 'activated') {
            return back()->with('success', 'Pendaftaran ini sudah aktif.');
        }

        $request->validate([
            'admin_password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($registration, $request) {
            $plan = $registration->plan;

            $school = School::create([
                'name'            => $registration->school_name,
                'subdomain'       => $registration->subdomain,
                'email'           => $registration->admin_email,
                'phone'           => $registration->admin_phone,
                'address'         => $registration->address,
                'plan_id'         => $plan->id,
                'plan_expires_at' => now()->addMonths($registration->billing_months),
                'is_active'       => true,
                'settings'        => [],
            ]);

            $admin = User::create([
                'name'      => $registration->admin_name,
                'email'     => $registration->admin_email,
                'password'  => Hash::make($request->admin_password),
                'school_id' => $school->id,
                'phone'     => $registration->admin_phone,
                'is_active' => true,
            ]);
            $admin->assignRole('admin');

            SubscriptionTransaction::create([
                'school_id'      => $school->id,
                'plan_id'        => $plan->id,
                'amount'         => $registration->plan_price,
                'payment_method' => $registration->payment_method ?? 'manual_transfer',
                'reference'      => $registration->payment_reference ?? 'REG-'.$registration->id,
                'status'         => 'paid',
                'period_from'    => now()->toDateString(),
                'period_to'      => now()->addMonths($registration->billing_months)->toDateString(),
            ]);

            $registration->update([
                'status'              => 'activated',
                'activated_at'        => now(),
                'activated_school_id' => $school->id,
            ]);
        });

        return redirect()->route('super.registrations.show', $registration)
            ->with('success', 'Sekolah diaktifkan. Admin dapat login dengan password yang Anda set.');
    }

    public function rejectRegistration(Request $request, SchoolRegistration $registration): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $registration->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Pendaftaran ditolak.');
    }

    /**
     * Bank account / billing settings — kemana sekolah transfer.
     */
    public function billingAccounts(): View
    {
        return view('super-admin.billing.accounts', [
            'accounts' => BillingAccount::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function storeBillingAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:200',
            'sort_order'     => 'nullable|integer|min:0',
        ]);
        $data['is_active'] = true;
        BillingAccount::create($data);

        return back()->with('success', 'Rekening pembayaran ditambahkan.');
    }

    public function toggleBillingAccount(BillingAccount $account): RedirectResponse
    {
        $account->update(['is_active' => !$account->is_active]);
        return back()->with('success', 'Status rekening diubah.');
    }

    public function deleteBillingAccount(BillingAccount $account): RedirectResponse
    {
        $account->delete();
        return back()->with('success', 'Rekening dihapus.');
    }

    /**
     * Payment Gateways — daftar gateway online (Snap/VA/QRIS/eWallet) untuk billing langganan.
     * Format-agnostic: nama provider, URL, API key — semua user-input.
     */
    public function gateways(): View
    {
        return view('super-admin.billing.gateways', [
            'gateways' => PaymentGateway::orderBy('priority')->orderBy('name')->get(),
            'formats'  => PaymentGateway::FORMATS,
            'presets'  => $this->loadPresets(),
        ]);
    }

    /**
     * Load preset templates dari storage/app/payment-gateway-presets/.
     * Hanya untuk autofill convenience — code TIDAK PERNAH reference di runtime.
     */
    private function loadPresets(): array
    {
        $dir = storage_path('app/payment-gateway-presets');
        if (!is_dir($dir)) return [];

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
        $presets = [];
        foreach ($files as $file) {
            $json = @file_get_contents($file);
            if (!$json) continue;
            $data = json_decode($json, true);
            if (is_array($data) && !empty($data['key'])) {
                $presets[] = $data;
            }
        }
        usort($presets, fn ($a, $b) => ($a['suggested_priority'] ?? 99) <=> ($b['suggested_priority'] ?? 99));
        return $presets;
    }

    public function storeGateway(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'slug'           => 'required|alpha_dash|max:100|unique:platform_payment_gateways,slug',
            'api_format'     => ['required', 'in:'.implode(',', PaymentGateway::FORMATS)],
            'base_url'       => 'nullable|url|max:500',
            'api_key'        => 'nullable|string|max:500',
            'secret_key'     => 'nullable|string|max:500',
            'merchant_id'    => 'nullable|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
            'callback_url'   => 'nullable|url|max:500',
            'is_sandbox'     => 'nullable|boolean',
            'priority'       => 'nullable|integer|min:0|max:100',
        ]);

        $gateway = new PaymentGateway();
        $gateway->fill([
            'name'         => $data['name'],
            'slug'         => $data['slug'],
            'api_format'   => $data['api_format'],
            'base_url'     => $data['base_url'] ?? null,
            'callback_url' => $data['callback_url'] ?? null,
            'is_sandbox'   => (bool) ($data['is_sandbox'] ?? false),
            'is_active'    => true,
            'priority'     => $data['priority'] ?? 0,
        ]);
        $gateway->api_key = $data['api_key'] ?? null;
        $gateway->secret_key = $data['secret_key'] ?? null;
        $gateway->merchant_id = $data['merchant_id'] ?? null;
        $gateway->webhook_secret = $data['webhook_secret'] ?? null;
        $gateway->save();

        return redirect()->route('super.billing.gateways.index')
            ->with('success', "Gateway '{$gateway->name}' berhasil ditambahkan.");
    }

    public function editGateway(PaymentGateway $gateway): View
    {
        return view('super-admin.billing.gateway-edit', [
            'gateway' => $gateway,
            'formats' => PaymentGateway::FORMATS,
        ]);
    }

    public function updateGateway(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'api_format'     => ['required', 'in:'.implode(',', PaymentGateway::FORMATS)],
            'base_url'       => 'nullable|url|max:500',
            'api_key'        => 'nullable|string|max:500',
            'secret_key'     => 'nullable|string|max:500',
            'merchant_id'    => 'nullable|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
            'callback_url'   => 'nullable|url|max:500',
            'is_sandbox'     => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'priority'       => 'nullable|integer|min:0|max:100',
        ]);

        $gateway->fill([
            'name'         => $data['name'],
            'api_format'   => $data['api_format'],
            'base_url'     => $data['base_url'] ?? null,
            'callback_url' => $data['callback_url'] ?? null,
            'is_sandbox'   => (bool) ($data['is_sandbox'] ?? false),
            'is_active'    => (bool) ($data['is_active'] ?? false),
            'priority'     => $data['priority'] ?? 0,
        ]);

        // Only overwrite secrets if user supplied new values
        foreach (['api_key', 'secret_key', 'merchant_id', 'webhook_secret'] as $secret) {
            if (!empty($data[$secret])) {
                $gateway->{$secret} = $data[$secret];
            }
        }
        $gateway->save();

        return redirect()->route('super.billing.gateways.index')
            ->with('success', 'Gateway diperbarui.');
    }

    public function toggleGateway(PaymentGateway $gateway): RedirectResponse
    {
        $gateway->update(['is_active' => !$gateway->is_active]);
        return back()->with('success', 'Status gateway diubah.');
    }

    public function deleteGateway(PaymentGateway $gateway): RedirectResponse
    {
        $gateway->delete();
        return back()->with('success', 'Gateway dihapus.');
    }
}
