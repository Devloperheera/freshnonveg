<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminUsersExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [25, 50, 100, 200, 500];

    public function __construct(private AdminUsersExportService $exportService) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = $this->filtersFromRequest($request);

        $users = $this->filteredQuery($filters)
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 1)->count(),
            'inactive' => User::where('status', '!=', 1)->count(),
            'purchased' => User::whereHas('orders')->count(),
            'with_cart' => User::whereHas('cartItems')->count(),
            'with_wishlist' => User::whereHas('wishlistItems')->count(),
        ];

        $sources = User::query()
            ->whereNotNull('registration_source')
            ->distinct()
            ->orderBy('registration_source')
            ->pluck('registration_source');

        return view('Admin.Users.index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $filters,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'sources' => $sources,
            'exportQuery' => http_build_query(array_filter($filters, fn ($v) => $v !== '' && $v !== null)),
        ]);
    }

    public function show(User $user): View
    {
        $user->loadCount(['orders', 'cartItems', 'wishlistItems', 'complaints', 'productReviews']);

        $recentOrders = $user->orders()
            ->with(['items.product.images'])
            ->latest('id')
            ->limit(5)
            ->get();

        $complaints = $user->complaints()
            ->with('order:id,order_number')
            ->latest('id')
            ->get();

        $ratings = $user->productReviews()
            ->with('product.images')
            ->latest('id')
            ->get();

        $cartItems = $user->cartItems()
            ->with('product.images')
            ->latest('updated_at')
            ->get();

        $wishlistItems = $user->wishlistItems()
            ->with('product.images')
            ->latest('id')
            ->get();

        $shippingAddresses = $this->shippingAddressesFor($user);

        return view('Admin.Users.show', compact(
            'user',
            'recentOrders',
            'complaints',
            'ratings',
            'cartItems',
            'wishlistItems',
            'shippingAddresses',
        ));
    }

    /** @return \Illuminate\Support\Collection<int, array{label: string, line: string, meta: string, when: ?\Illuminate\Support\Carbon}> */
    private function shippingAddressesFor(User $user): \Illuminate\Support\Collection
    {
        $rows = collect();

        if (filled($user->address)) {
            $rows->push([
                'label' => 'Profile',
                'line' => $user->address,
                'meta' => collect([
                    $user->latitude !== null ? 'Lat '.$user->latitude : null,
                    $user->longitude !== null ? 'Lng '.$user->longitude : null,
                ])->filter()->implode(' · '),
                'when' => null,
            ]);
        }

        $user->orders()
            ->whereNotNull('delivery_address')
            ->latest('id')
            ->get(['delivery_address', 'pincode', 'city', 'state', 'phone', 'created_at'])
            ->unique(fn ($o) => mb_strtolower(trim((string) $o->delivery_address)).'|'.$o->pincode)
            ->each(function ($order) use ($rows) {
                $rows->push([
                    'label' => 'Delivery',
                    'line' => $order->delivery_address,
                    'meta' => collect([$order->city, $order->state, $order->pincode, $order->phone])->filter()->implode(' · '),
                    'when' => $order->created_at,
                ]);
            });

        return $rows;
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $filters = $this->filtersFromRequest($request);
        $users = $this->filteredQuery($filters)->get();

        return $this->exportService->downloadExcel($users);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->filtersFromRequest($request);
        $users = $this->filteredQuery($filters)->get();

        return $this->exportService->downloadCsv($users);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update([
            'status' => $user->isActive() ? 0 : 1,
        ]);

        return back()->with('success', 'User status updated.');
    }

    /**
     * @return array<string, string>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
            'source' => (string) $request->input('source', ''),
            'has_orders' => (string) $request->input('has_orders', ''),
            'has_cart' => (string) $request->input('has_cart', ''),
            'has_wishlist' => (string) $request->input('has_wishlist', ''),
            'app_installed' => (string) $request->input('app_installed', ''),
            'registered_from' => (string) $request->input('registered_from', ''),
            'registered_to' => (string) $request->input('registered_to', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = User::query()
            ->withCount(['orders', 'cartItems', 'wishlistItems']);

        $this->applyFilters($query, $filters);

        return $query;
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('address', 'like', $term);
            });
        }

        if ($filters['status'] === 'active') {
            $query->where('status', 1);
        } elseif ($filters['status'] === 'inactive') {
            $query->where('status', '!=', 1);
        }

        if ($filters['source'] !== '') {
            $query->where('registration_source', $filters['source']);
        }

        if ($filters['has_orders'] === 'yes') {
            $query->whereHas('orders');
        } elseif ($filters['has_orders'] === 'no') {
            $query->whereDoesntHave('orders');
        }

        if ($filters['has_cart'] === 'yes') {
            $query->whereHas('cartItems');
        } elseif ($filters['has_cart'] === 'no') {
            $query->whereDoesntHave('cartItems');
        }

        if ($filters['has_wishlist'] === 'yes') {
            $query->whereHas('wishlistItems');
        } elseif ($filters['has_wishlist'] === 'no') {
            $query->whereDoesntHave('wishlistItems');
        }

        if ($filters['app_installed'] === 'yes') {
            $query->where('is_app_installed', true);
        } elseif ($filters['app_installed'] === 'no') {
            $query->where(function (Builder $q) {
                $q->where('is_app_installed', false)->orWhereNull('is_app_installed');
            });
        }

        if ($filters['registered_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['registered_from']);
        }

        if ($filters['registered_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['registered_to']);
        }

        match ($filters['sort']) {
            'oldest' => $query->orderBy('id'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'logins' => $query->orderByDesc('login_count'),
            default => $query->orderByDesc('id'),
        };
    }
}
