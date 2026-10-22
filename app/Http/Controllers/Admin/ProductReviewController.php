<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $rating = (int) $request->query('rating', 0);
        $status = (string) $request->query('status', '');

        $reviews = ProductReview::query()
            ->with([
                'user:id,name,phone,email,profile_image',
                'product:id,name,category_id',
                'product.images',
                'product.category:id,name',
                'order:id,order_number',
            ])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', (int) $request->input('user_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($q) use ($term) {
                    $q->where('review', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('phone', 'like', $term))
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term));
                });
            })
            ->when($rating >= 1 && $rating <= 5, fn ($q) => $q->where('rating', $rating))
            ->when($status === 'active', fn ($q) => $q->where('status', 1))
            ->when($status === 'inactive', fn ($q) => $q->where('status', 0))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('Admin.ProductReviews.index', compact('reviews', 'rating', 'status'));
    }

    public function show(ProductReview $productReview): View
    {
        $productReview->load(['user', 'product.images', 'product.category:id,name', 'order.items']);

        return view('Admin.ProductReviews.show', ['review' => $productReview]);
    }

    public function edit(ProductReview $productReview): View
    {
        $productReview->load(['user', 'product.images', 'order:id,order_number']);

        return view('Admin.ProductReviews.form', ['review' => $productReview]);
    }

    public function update(Request $request, ProductReview $productReview): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:0,1'],
        ]);

        $productReview->update($data);

        return redirect()
            ->route('admin.product-reviews.show', $productReview)
            ->with('success', 'Rating update ho gayi.');
    }

    public function toggleStatus(ProductReview $productReview): RedirectResponse
    {
        $productReview->update(['status' => $productReview->isActive() ? 0 : 1]);

        return back()->with('success', $productReview->isActive() ? 'Rating ab product page par dikhegi.' : 'Rating product page se chhupa di gayi.');
    }

    public function destroy(ProductReview $productReview): RedirectResponse
    {
        $productReview->delete();

        return redirect()->route('admin.product-reviews.index')->with('success', 'Rating delete ho gayi.');
    }
}
