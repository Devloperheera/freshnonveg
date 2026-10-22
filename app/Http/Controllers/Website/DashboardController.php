<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private CustomerAccountService $account) {}

    public function index(): View
    {
        $user = $this->user();

        return view('Website.Dashboard.index', [
            'user' => $user,
            'stats' => $this->account->stats($user),
            'recentOrders' => $user->orders()->with('items.product.images')->latest('id')->limit(5)->get(),
            'recentTransactions' => $user->walletTransactions()->latest('id')->limit(4)->get(),
        ]);
    }

    public function orders(): View
    {
        $user = $this->user();

        return view('Website.Dashboard.orders', [
            'user' => $user,
            'orders' => $user->orders()->with('items.product.images')->latest('id')->paginate(10),
            'myReviews' => $user->productReviews()->get(['id', 'product_id', 'rating'])->keyBy('product_id'),
        ]);
    }

    public function showOrder(Order $order): View
    {
        $user = $this->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        $order->load(['items.product.images', 'user']);

        return view('Website.Dashboard.order-show', [
            'user' => $user,
            'order' => $order,
            'myReviews' => $user->productReviews()
                ->whereIn('product_id', $order->items->pluck('product_id')->filter())
                ->get()
                ->keyBy('product_id'),
        ]);
    }

    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        $user = $this->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        $data = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->account->cancelOrder($user, $order, $data['cancel_reason']);
        if (! $result['ok']) {
            return back()->withErrors(['cancel' => $result['message']]);
        }

        return redirect()
            ->route('dashboard.orders.show', $order)
            ->with('success', 'Order cancel ho gaya. ₹'.number_format($result['refund'], 2).' aapke wallet me refund kar diye gaye.');
    }

    public function wallet(): View
    {
        $user = $this->user();

        return view('Website.Dashboard.wallet', [
            'user' => $user,
            'transactions' => $user->walletTransactions()->latest('id')->paginate(15),
            'totalCredited' => (float) $user->walletTransactions()->whereIn('type', ['credit', 'refund'])->sum('amount'),
            'totalUsed' => (float) $user->walletTransactions()->where('type', 'debit')->sum('amount'),
        ]);
    }

    public function complaints(Request $request): View
    {
        $user = $this->user();

        return view('Website.Dashboard.complaints', [
            'user' => $user,
            'complaints' => $user->complaints()->with('order.items.product.images')->latest('id')->paginate(10),
            'orderOptions' => $user->orders()->latest('id')->limit(30)->get(['id', 'order_number', 'total', 'created_at']),
            'selectedOrder' => (int) $request->query('order', 0),
        ]);
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $user = $this->user();

        $data = $request->validate([
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where('user_id', $user->id)],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $this->account->storeComplaint($user, $data);

        return redirect()
            ->route('dashboard.complaints')
            ->with('success', 'Complaint register ho gayi. Team jald review karegi — sahi paaya gaya to wallet me amount credit hoga.');
    }

    public function ratings(): View
    {
        $user = $this->user();

        $purchased = $this->account->purchasedItems($user);

        $reviews = $user->productReviews()->get()->keyBy('product_id');

        return view('Website.Dashboard.ratings', [
            'user' => $user,
            'pending' => $purchased->reject(fn (OrderItem $line) => $reviews->has($line->product_id))->values(),
            'rated' => $purchased->filter(fn (OrderItem $line) => $reviews->has($line->product_id))->values(),
            'reviews' => $reviews,
        ]);
    }

    public function storeRating(Request $request, Product $product): RedirectResponse
    {
        $user = $this->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $this->account->storeRating($user, $product, $data);
        if (! $result['ok']) {
            return back()->withErrors(['rating' => $result['message']]);
        }

        return back()->with('success', $product->name.' ke liye rating dene ka shukriya!');
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
