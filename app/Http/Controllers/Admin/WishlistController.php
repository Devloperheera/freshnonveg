<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /** @var list<int> */
    public const PER_PAGE_OPTIONS = [25, 50, 100, 200, 500];

    /** @var array<string, string> */
    public const DATE_PRESETS = [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'last_7' => 'Last 7 days',
        'last_30' => 'Last 30 days',
        'this_month' => 'This month',
        'last_month' => 'Last month',
    ];

    /** @var array<string, string> */
    public const SITTING_OPTIONS = [
        '1' => 'Sitting 1+ day',
        '3' => 'Sitting 3+ days',
        '7' => 'Sitting 7+ days',
        '30' => 'Sitting 30+ days',
    ];

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);

        $items = (clone $query)
            ->with([
                'user:id,name,phone,email,profile_image,status',
                'product.images',
                'product.category:id,name',
            ])
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = $this->filteredQuery($filters, sorted: false);
        $summary = [
            'items' => (clone $summaryQuery)->count(),
            'users' => (clone $summaryQuery)->distinct()->count('user_id'),
            'missing' => (clone $summaryQuery)->whereNull('product_id')->count(),
            'sitting' => (clone $summaryQuery)->where('created_at', '<=', now()->subDays(7))->count(),
        ];

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('Admin.Wishlists.index', [
            'items' => $items,
            'summary' => $summary,
            'filters' => $filters,
            'perPage' => $perPage,
            'categories' => $categories,
        ]);
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'user_id' => (string) $request->input('user_id', ''),
            'category_id' => (string) $request->input('category_id', ''),
            'user_status' => (string) $request->input('user_status', ''),
            'product' => (string) $request->input('product', ''),
            'product_status' => (string) $request->input('product_status', ''),
            'featured' => (string) $request->input('featured', ''),
            'sitting' => (string) $request->input('sitting', ''),
            'preset' => (string) $request->input('preset', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];
    }

    /** @param  array<string, string>  $f */
    private function filteredQuery(array $f, bool $sorted = true): Builder
    {
        $query = WishlistItem::query();

        if ($f['q'] !== '') {
            $term = '%'.$f['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('product_name', 'like', $term)
                    ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', $term))
                    ->orWhereHas('user', function (Builder $u) use ($term) {
                        $u->where(function (Builder $u) use ($term) {
                            $u->where('name', 'like', $term)
                                ->orWhere('phone', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                    });
            });
        }

        if (ctype_digit($f['user_id'])) {
            $query->where('user_id', (int) $f['user_id']);
        }

        if (ctype_digit($f['category_id'])) {
            $query->whereHas('product', fn (Builder $p) => $p->where('category_id', (int) $f['category_id']));
        }

        if ($f['user_status'] === 'active') {
            $query->whereHas('user', fn (Builder $u) => $u->where('status', 1));
        } elseif ($f['user_status'] === 'inactive') {
            $query->whereHas('user', fn (Builder $u) => $u->where('status', '!=', 1));
        }

        if ($f['product'] === 'linked') {
            $query->whereNotNull('product_id');
        } elseif ($f['product'] === 'missing') {
            $query->whereNull('product_id');
        }

        if ($f['product_status'] === 'active') {
            $query->whereHas('product', fn (Builder $p) => $p->where('status', 1));
        } elseif ($f['product_status'] === 'inactive') {
            $query->whereHas('product', fn (Builder $p) => $p->where('status', '!=', 1));
        }

        if ($f['featured'] === 'yes') {
            $query->whereHas('product', fn (Builder $p) => $p->where('is_featured', 1));
        } elseif ($f['featured'] === 'no') {
            $query->whereHas('product', fn (Builder $p) => $p->where('is_featured', '!=', 1));
        }

        if (array_key_exists($f['sitting'], self::SITTING_OPTIONS)) {
            $query->where('created_at', '<=', now()->subDays((int) $f['sitting']));
        }

        [$from, $to] = $this->dateRange($f);
        if ($from) {
            $query->whereDate('created_at', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to->toDateString());
        }

        if ($sorted) {
            match ($f['sort']) {
                'oldest' => $query->orderBy('created_at')->orderBy('id'),
                'product' => $query->orderBy('product_name')->orderByDesc('id'),
                default => $query->orderByDesc('created_at')->orderByDesc('id'),
            };
        }

        return $query;
    }

    /**
     * @param  array<string, string>  $f
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function dateRange(array $f): array
    {
        $today = Carbon::today();

        return match ($f['preset']) {
            'today' => [$today, $today],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            'last_7' => [$today->copy()->subDays(6), $today],
            'last_30' => [$today->copy()->subDays(29), $today],
            'this_month' => [$today->copy()->startOfMonth(), $today],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            default => [
                $this->parseDate($f['date_from']),
                $this->parseDate($f['date_to']),
            ],
        };
    }

    private function parseDate(string $value): ?Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
    }
}
