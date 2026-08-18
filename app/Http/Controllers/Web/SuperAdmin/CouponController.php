<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Saas\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $coupons = Coupon::query()
            ->when($request->search, fn ($q) => $q
                ->where('code', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%"))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:50|unique:coupons,code',
            'description'    => 'nullable|string|max:500',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer|min:0',
            'max_uses'       => 'nullable|integer|min:1',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'nullable|boolean',
        ]);

        Coupon::create($validated);

        return back()->with('success', 'Kupon berhasil ditambahkan.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'description'    => 'nullable|string|max:500',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer|min:0',
            'max_uses'       => 'nullable|integer|min:1',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'nullable|boolean',
        ]);

        $coupon->update($validated);

        return back()->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();
        return back()->with('success', 'Kupon berhasil dihapus.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', 'Status kupon diubah.');
    }

    public function validateCoupon(Request $request): RedirectResponse
    {
        $request->validate([
            'code'   => 'required|string|max:50',
            'amount' => 'required|integer|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return back()->with('error', 'Kupon tidak ditemukan.');
        }

        if (!$coupon->isValid()) {
            return back()->with('error', 'Kupon tidak aktif atau sudah kedaluwarsa.');
        }

        $discounted = $coupon->applyDiscount($request->amount);
        $discountAmount = $request->amount - $discounted;

        return back()->with('success', "Kupon valid! Diskon: {$coupon->discount_label} = Rp " . number_format($discountAmount / 100, 0, ',', '.'));
    }
}
