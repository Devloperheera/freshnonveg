<?php

namespace App\Support;

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Website\ShopController;
use App\Models\AdvantageItem;
use App\Models\Banner;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Coupon;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStep;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductWeight;
use App\Models\SiteStat;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SpeedCache
{
    public const SIDEBAR = 'speed.sidebar_counts';

    public const HOME = 'speed.home';

    public const DASHBOARD = 'speed.dashboard';

    /**
     * @return array<string, int|float>
     */
    public static function sidebarCounts(): array
    {
        return self::remember(self::SIDEBAR, 45, function () {
            return [
                'users' => User::count(),
                'orders' => Order::count(),
                'cart_items' => CartItem::count(),
                'wishlist_items' => WishlistItem::count(),
                'wallet_balance' => (float) User::sum('wallet_balance'),
                'banners' => Banner::count(),
                'categories' => Category::count(),
                'products' => Product::count(),
                'pages' => Page::count(),
                'page_contents' => PageContent::count(),
                'testimonials' => Testimonial::count(),
                'faqs' => Faq::count(),
                'advantage_items' => AdvantageItem::count(),
                'order_steps' => OrderStep::count(),
                'site_stats' => SiteStat::count(),
                'delivery_slots' => DeliverySlot::count(),
                'coupons' => Coupon::count(),
                'product_reviews' => ProductReview::count(),
                'delivery_zones' => DeliveryZone::count(),
                'open_complaints' => Complaint::query()->whereIn('status', ['open', 'in_progress'])->count(),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function home(Request $request): array
    {
        $ids = self::remember(self::HOME, 60, function () use ($request) {
            $catalogQuery = ShopController::catalogQuery($request);

            return [
                'banner_ids' => Banner::query()->where('status', 1)->orderByDesc('id')->pluck('id')->all(),
                'category_ids' => Category::query()->where('status', 1)->orderBy('name')->pluck('id')->all(),
                'product_ids' => (clone $catalogQuery)->orderByDesc('is_featured')->orderByDesc('id')->limit(20)->pluck('id')->all(),
                'total_products' => (clone $catalogQuery)->count(),
                'testimonial_ids' => Testimonial::query()->where('status', 1)->orderByDesc('id')->pluck('id')->all(),
                'faq_ids' => Faq::query()->where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('id')->all(),
                'advantage_ids' => AdvantageItem::query()->where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('id')->all(),
                'step_ids' => OrderStep::query()->where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('id')->all(),
                'stat_ids' => SiteStat::query()->where('status', 1)->orderBy('sort_order')->orderBy('id')->pluck('id')->all(),
            ];
        });

            $productIds = $ids['product_ids'];

        return [
            'banners' => self::byIds(Banner::query(), $ids['banner_ids']),
            'categories' => self::byIds(Category::query(), $ids['category_ids']),
            'products' => $productIds === []
                ? Product::query()->whereRaw('1 = 0')->get()
                : Product::query()
                    ->with(['images', 'weights', 'tags', 'category'])
                    ->whereIn('id', $productIds)
                    ->orderByDesc('is_featured')
                    ->orderByDesc('id')
                    ->get(),
            'hasMoreProducts' => $ids['total_products'] > 20,
            'totalProducts' => $ids['total_products'],
            'testimonials' => self::byIds(Testimonial::query(), $ids['testimonial_ids']),
            'faqs' => self::byIds(Faq::query()->orderBy('sort_order')->orderBy('id'), $ids['faq_ids']),
            'advantageItems' => self::byIds(AdvantageItem::query()->orderBy('sort_order')->orderBy('id'), $ids['advantage_ids']),
            'advantagesSection' => HomeSection::forKey(HomeSection::KEY_ADVANTAGES),
            'orderSteps' => self::byIds(OrderStep::query()->orderBy('sort_order')->orderBy('id'), $ids['step_ids']),
            'orderWaySection' => HomeSection::forKey(HomeSection::KEY_ORDER_WAY),
            'siteStats' => self::byIds(SiteStat::query()->orderBy('sort_order')->orderBy('id'), $ids['stat_ids']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        $payload = self::remember(self::DASHBOARD, 20, function () {
            $today = Carbon::today();
            $from = $today->copy()->subDays(13);
            $paid = Order::PAID_STATUSES;

            $userCount = User::count();
            $orderCount = Order::count();
            $productCount = Product::count();
            $complaintCount = Complaint::count();

            $cards = [
                [
                    'label' => 'Users',
                    'value' => number_format($userCount),
                    'sub' => User::whereDate('created_at', $today)->count().' aaj naye',
                    'icon' => 'bi-people',
                    'tone' => 'green',
                    'url' => route('admin.users.index', [], false),
                ],
                [
                    'label' => 'Orders',
                    'value' => number_format($orderCount),
                    'sub' => Order::whereDate('created_at', $today)->count().' aaj',
                    'icon' => 'bi-bag-check',
                    'tone' => 'orange',
                    'url' => route('admin.orders.index', [], false),
                ],
                [
                    'label' => 'Paid revenue',
                    'value' => '₹'.number_format((float) Order::query()->whereIn('status', $paid)->sum('total'), 0),
                    'sub' => '₹'.number_format((float) Order::query()->whereIn('status', $paid)->whereDate('created_at', $today)->sum('total'), 0).' aaj',
                    'icon' => 'bi-currency-rupee',
                    'tone' => 'blue',
                    'url' => route('admin.orders.index', ['status' => 'paid'], false),
                ],
                [
                    'label' => 'Products',
                    'value' => number_format($productCount),
                    'sub' => Product::where('status', 1)->count().' active',
                    'icon' => 'bi-box-seam',
                    'tone' => 'red',
                    'url' => route('admin.products.index', [], false),
                ],
                [
                    'label' => 'Cart items',
                    'value' => number_format(CartItem::count()),
                    'sub' => User::whereHas('cartItems')->count().' customers',
                    'icon' => 'bi-cart3',
                    'tone' => 'purple',
                    'url' => route('admin.carts.index', [], false),
                ],
                [
                    'label' => 'Wishlist',
                    'value' => number_format(WishlistItem::count()),
                    'sub' => 'saved products',
                    'icon' => 'bi-heart',
                    'tone' => 'pink',
                    'url' => route('admin.wishlists.index', [], false),
                ],
                [
                    'label' => 'Wallet balance',
                    'value' => '₹'.number_format((float) User::sum('wallet_balance'), 0),
                    'sub' => 'users ke pass bacha',
                    'icon' => 'bi-wallet2',
                    'tone' => 'teal',
                    'url' => route('admin.wallet.index', [], false),
                ],
                [
                    'label' => 'Open complaints',
                    'value' => number_format(Complaint::query()->whereIn('status', ['open', 'in_progress'])->count()),
                    'sub' => $complaintCount.' total',
                    'icon' => 'bi-chat-left-dots',
                    'tone' => 'rose',
                    'url' => route('admin.complaints.index', ['status' => 'open'], false),
                ],
                [
                    'label' => 'Product reviews',
                    'value' => number_format(ProductReview::count()),
                    'sub' => number_format((float) ProductReview::avg('rating'), 1).' avg stars',
                    'icon' => 'bi-star',
                    'tone' => 'gold',
                    'url' => route('admin.product-reviews.index', [], false),
                ],
                [
                    'label' => 'Coupons',
                    'value' => number_format(Coupon::count()),
                    'sub' => Coupon::where('status', 1)->count().' active',
                    'icon' => 'bi-ticket-perforated',
                    'tone' => 'lime',
                    'url' => route('admin.coupons.index', [], false),
                ],
            ];

            $days = collect(range(0, 13))->map(fn (int $i) => $from->copy()->addDays($i));
            $labels = $days->map(fn (Carbon $d) => $d->format('d M'))->all();
            $dayKeys = $days->map(fn (Carbon $d) => $d->toDateString());

            $orderRows = Order::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, COALESCE(SUM(CASE WHEN status IN (?, ?) THEN total ELSE 0 END), 0) as revenue', $paid)
                ->where('created_at', '>=', $from->copy()->startOfDay())
                ->groupByRaw('DATE(created_at)')
                ->get()
                ->keyBy('day');

            $userRows = User::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
                ->where('created_at', '>=', $from->copy()->startOfDay())
                ->groupByRaw('DATE(created_at)')
                ->pluck('c', 'day');

            $statusCounts = self::orderStatusCounts();

            return [
                'cards' => $cards,
                'charts' => [
                    'labels' => $labels,
                    'orders' => $dayKeys->map(fn (string $d) => (int) ($orderRows[$d]->orders ?? 0))->values()->all(),
                    'revenue' => $dayKeys->map(fn (string $d) => round((float) ($orderRows[$d]->revenue ?? 0), 2))->values()->all(),
                    'users' => $dayKeys->map(fn (string $d) => (int) ($userRows[$d] ?? 0))->values()->all(),
                    'status' => [
                        'labels' => array_values(array_map(fn ($s) => str_replace('_', ' ', $s), array_keys($statusCounts))),
                        'data' => array_values($statusCounts),
                    ],
                ],
            ];
        });

        $payload['recentOrders'] = Order::query()
            ->with('user:id,name,phone')
            ->latest('id')
            ->limit(6)
            ->get(['id', 'user_id', 'order_number', 'total', 'status', 'created_at']);

        return $payload;
    }

    /**
     * @return array{wishlist: int, cart: int}
     */
    public static function headerCounts(int $userId): array
    {
        return self::remember(self::headerKey($userId), 20, function () use ($userId) {
            return [
                'wishlist' => WishlistItem::query()->where('user_id', $userId)->count(),
                'cart' => (int) CartItem::query()->where('user_id', $userId)->sum('quantity'),
            ];
        });
    }

    public static function flush(?int $userId = null): void
    {
        Cache::forget(self::SIDEBAR);
        Cache::forget(self::HOME);
        Cache::forget(self::DASHBOARD);

        if ($userId) {
            Cache::forget(self::headerKey($userId));
        }
    }

    public static function registerInvalidation(): void
    {
        $models = [
            Product::class,
            ProductImage::class,
            ProductWeight::class,
            Category::class,
            Banner::class,
            Tag::class,
            Order::class,
            OrderItem::class,
            CartItem::class,
            WishlistItem::class,
            Complaint::class,
            User::class,
            Coupon::class,
            ProductReview::class,
            Faq::class,
            Testimonial::class,
            AdvantageItem::class,
            OrderStep::class,
            SiteStat::class,
            HomeSection::class,
            DeliverySlot::class,
            DeliveryZone::class,
            Page::class,
            PageContent::class,
            WalletTransaction::class,
        ];

        foreach ($models as $model) {
            $model::saved(function (object $record): void {
                self::flush(self::userIdFrom($record));
            });
            $model::deleted(function (object $record): void {
                self::flush(self::userIdFrom($record));
            });
        }
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function remember(string $key, int $seconds, callable $callback): mixed
    {
        return Cache::remember($key, $seconds, $callback);
    }

    /**
     * @param  list<int>  $ids
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private static function byIds(\Illuminate\Database\Eloquent\Builder $query, array $ids)
    {
        if ($ids === []) {
            return $query->getModel()->newCollection();
        }

        return $query->whereIn('id', $ids)->get()->sortBy(fn ($model) => array_search($model->id, $ids, true))->values();
    }

    private static function headerKey(int $userId): string
    {
        return 'speed.header.'.$userId;
    }

    private static function userIdFrom(object $record): ?int
    {
        if ($record instanceof User) {
            return (int) $record->id;
        }

        if (isset($record->user_id) && $record->user_id) {
            return (int) $record->user_id;
        }

        return null;
    }

    /** @return array<string, int> */
    private static function orderStatusCounts(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $out = [];
        foreach (array_keys(OrderController::STATUS_LABELS) as $status) {
            if (isset($counts[$status])) {
                $out[$status] = (int) $counts[$status];
            }
        }
        foreach ($counts as $status => $c) {
            if (! isset($out[$status])) {
                $out[$status] = (int) $c;
            }
        }

        return $out;
    }
}
