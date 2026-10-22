<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()->latest('id')->get();

        return view('Admin.Coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('Admin.Coupons.form', [
            'coupon' => new Coupon([
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_order_amount' => 0,
                'status' => 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Coupon::query()->create($this->validated($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon added. Customers checkout par ye code laga sakte hain.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('Admin.Coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }

    public function toggleStatus(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['status' => $coupon->isActive() ? 0 : 1]);

        return back()->with('success', 'Coupon status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'title' => ['nullable', 'string', 'max:120'],
            'discount_type' => ['required', 'in:percent,flat'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:0,1'],
        ]);

        if ($data['discount_type'] === 'percent' && (float) $data['discount_value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount_value' => 'Percent discount 100 se zyada nahi ho sakta.',
            ]);
        }

        $data['code'] = Coupon::normalizeCode($data['code']);
        $data['title'] = trim((string) ($data['title'] ?? '')) ?: null;
        $data['min_order_amount'] = (float) ($data['min_order_amount'] ?? 0);
        $data['max_discount'] = $data['discount_type'] === 'percent' && filled($data['max_discount'] ?? null)
            ? (float) $data['max_discount']
            : null;
        $data['usage_limit'] = filled($data['usage_limit'] ?? null) ? (int) $data['usage_limit'] : null;
        $data['starts_at'] = $data['starts_at'] ?: null;
        $data['expires_at'] = $data['expires_at'] ?: null;
        $data['status'] = (int) $data['status'];

        return $data;
    }
}
