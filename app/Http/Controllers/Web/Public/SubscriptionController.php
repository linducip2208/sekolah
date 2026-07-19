<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Platform\BillingAccount;
use App\Models\Platform\SchoolRegistration;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function pricing(): View
    {
        return view('public.pricing', [
            'plans' => Plan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function register(Request $request): View
    {
        $plan = $request->plan
            ? Plan::where('slug', $request->plan)->where('is_active', true)->firstOrFail()
            : Plan::where('is_active', true)->orderBy('price')->first();

        return view('public.register', [
            'plans'           => Plan::where('is_active', true)->orderBy('price')->get(),
            'selectedPlan'    => $plan,
            'billingAccounts' => BillingAccount::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name'    => 'required|string|max:200',
            'subdomain'      => 'required|alpha_dash|min:3|max:40|unique:school_registrations,subdomain|unique:schools,subdomain',
            'admin_name'     => 'required|string|max:200',
            'admin_email'    => 'required|email|max:200|unique:users,email|unique:school_registrations,admin_email',
            'admin_phone'    => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'plan_id'        => 'required|exists:plans,id',
            'billing_months' => 'required|integer|in:1,3,6,12',
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        $registration = SchoolRegistration::create([
            ...$data,
            'plan_price' => $plan->price * $data['billing_months'],
            'status'     => 'pending',
        ]);

        return redirect()->route('public.subscription.payment', $registration)
            ->with('success', 'Pendaftaran diterima. Lanjutkan ke instruksi pembayaran.');
    }

    public function payment(SchoolRegistration $registration): View
    {
        abort_if($registration->status === 'activated', 410, 'Pendaftaran ini sudah diaktifkan.');

        return view('public.payment', [
            'registration'    => $registration->load('plan'),
            'billingAccounts' => BillingAccount::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function uploadProof(Request $request, SchoolRegistration $registration): RedirectResponse
    {
        abort_if(in_array($registration->status, ['activated', 'rejected']), 410);

        $data = $request->validate([
            'payment_method'    => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'payment_proof'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $registration->update([
            'status'             => 'verifying',
            'payment_method'     => $data['payment_method'],
            'payment_reference'  => $data['payment_reference'] ?? null,
            'payment_proof_path' => $path,
            'paid_at'            => now(),
        ]);

        return redirect()->route('public.subscription.success', $registration);
    }

    public function success(SchoolRegistration $registration): View
    {
        return view('public.success', compact('registration'));
    }
}
