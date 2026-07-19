<?php

namespace App\Http\Controllers\Web\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodWebController extends Controller
{
    public function index(): View
    {
        $methods = PaymentMethod::with('provider')
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('sort_order')
            ->get();

        return view('school-admin.payment.methods.index', compact('methods'));
    }

    public function create(): View
    {
        $providers = PaymentProvider::where('school_id', auth()->user()->school_id)
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        return view('school-admin.payment.methods.create', compact('providers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateMethod($request);
        $data['school_id'] = auth()->user()->school_id;

        PaymentProvider::where('school_id', $data['school_id'])->findOrFail($data['payment_provider_id']);

        PaymentMethod::create($data);

        return redirect()
            ->route('admin.payment.methods.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $method = PaymentMethod::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $providers = PaymentProvider::where('school_id', auth()->user()->school_id)
            ->orderBy('priority')->get();
        return view('school-admin.payment.methods.edit', compact('method', 'providers'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $method = PaymentMethod::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $method->update($this->validateMethod($request, true));
        return redirect()
            ->route('admin.payment.methods.index')
            ->with('success', 'Metode pembayaran diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $method = PaymentMethod::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $method->delete();
        return redirect()
            ->route('admin.payment.methods.index')
            ->with('success', 'Metode pembayaran dihapus.');
    }

    protected function validateMethod(Request $request, bool $update = false): array
    {
        return $request->validate([
            'payment_provider_id'   => ($update ? 'sometimes|' : '') . 'required|integer|exists:payment_providers,id',
            'code'                  => ($update ? 'sometimes|' : '') . 'required|string|max:50',
            'display_name'          => ($update ? 'sometimes|' : '') . 'required|string|max:200',
            'logo_url'              => 'nullable|url|max:500',
            'instruction_template'  => 'nullable|string|max:5000',
            'fee_flat'              => 'nullable|integer|min:0',
            'fee_percent_bp'        => 'nullable|integer|min:0|max:10000',
            'fee_borne_by'          => 'nullable|integer|in:0,1',
            'min_amount'            => 'nullable|integer|min:0',
            'max_amount'            => 'nullable|integer|min:0',
            'expiry_minutes'        => 'nullable|integer|min:1|max:43200',
            'is_active'             => 'nullable|in:0,1,true,false',
            'sort_order'            => 'nullable|integer|min:0|max:1000',
        ]);
    }
}
