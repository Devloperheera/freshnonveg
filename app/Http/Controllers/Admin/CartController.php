<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CartController extends Controller
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
    public const IDLE_OPTIONS = [
        '1' => 'Idle 1+ day',
        '3' => 'Idle 3+ days',
        '7' => 'Idle 7+ days',
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
            'qty' => (int) (clone $summaryQuery)->sum('quantity'),
            'value' => (float) (clone $summaryQuery)->selectRaw('COALESCE(SUM(unit_price * quantity), 0) as total')->value('total'),
        ];

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $weights = CartItem::query()
            ->whereNotNull('weight_label')
            ->where('weight_label', '!=', '')
            ->distinct()
            ->orderBy('weight_label')
            ->pluck('weight_label');

        return view('Admin.Carts.index', [
            'items' => $items,
            'summary' => $summary,
            'filters' => $filters,
            'perPage' => $perPage,
            'categories' => $categories,
            'weights' => $weights,
        ]);
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'user_id' => (string) $request->input('user_id', ''),
            'category_id' => (string) $request->input('category_id', ''),
            'weight' => trim((string) $request->input('weight', '')),
            'user_status' => (string) $request->input('user_status', ''),
            'has_note' => (string) $request->input('has_note', ''),
            'product' => (string) $request->input('product', ''),
            'idle' => (string) $request->input('idle', ''),
            'qty_min' => (string) $request->input('qty_min', ''),
            'qty_max' => (string) $request->input('qty_max', ''),
            'amount_min' => (string) $request->input('amount_min', ''),
            'amount_max' => (string) $request->input('amount_max', ''),
            'date_field' => $request->input('date_field') === 'created' ? 'created' : 'updated',
            'preset' => (string) $request->input('preset', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];
    }

    /** @param  array<string, string>  $f */
    private function filteredQuery(array $f, bool $sorted = true): Builder
    {
        $query = CartItem::query();

        if ($f['q'] !== '') {
            $term = '%'.$f['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('product_name', 'like', $term)
                    ->orWhere('weight_label', 'like', $term)
                    ->orWhere('preference_note', 'like', $term)
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

        if ($f['weight'] !== '') {
            $query->where('weight_label', $f['weight']);
        }

        if ($f['user_status'] === 'active') {
            $query->whereHas('user', fn (Builder $u) => $u->where('status', 1));
        } elseif ($f['user_status'] === 'inactive') {
            $query->whereHas('user', fn (Builder $u) => $u->where('status', '!=', 1));
        }

        if ($f['has_note'] === 'yes') {
            $query->whereNotNull('preference_note')->where('preference_note', '!=', '');
        } elseif ($f['has_note'] === 'no') {
            $query->where(fn (Builder $q) => $q->whereNull('preference_note')->orWhere('preference_note', ''));
        }

        if ($f['product'] === 'linked') {
            $query->whereNotNull('product_id');
        } elseif ($f['product'] === 'missing') {
            $query->whereNull('product_id');
        }

        if (array_key_exists($f['idle'], self::IDLE_OPTIONS)) {
            $query->where('updated_at', '<=', now()->subDays((int) $f['idle']));
        }

        if (is_numeric($f['qty_min'])) {
            $query->where('quantity', '>=', (int) $f['qty_min']);
        }
        if (is_numeric($f['qty_max'])) {
            $query->where('quantity', '<=', (int) $f['qty_max']);
        }
        if (is_numeric($f['amount_min'])) {
            $query->whereRaw('(unit_price * quantity) >= ?', [(float) $f['amount_min']]);
        }
        if (is_numeric($f['amount_max'])) {
            $query->whereRaw('(unit_price * quantity) <= ?', [(float) $f['amount_max']]);
        }

        [$from, $to] = $this->dateRange($f);
        $column = $f['date_field'] === 'created' ? 'created_at' : 'updated_at';
        if ($from) {
            $query->whereDate($column, '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate($column, '<=', $to->toDateString());
        }

        if ($sorted) {
            match ($f['sort']) {
                'oldest' => $query->orderBy('updated_at')->orderBy('id'),
                'added_new' => $query->orderByDesc('created_at')->orderByDesc('id'),
                'qty_high' => $query->orderByDesc('quantity')->orderByDesc('id'),
                'amount_high' => $query->orderByRaw('(unit_price * quantity) DESC')->orderByDesc('id'),
                'amount_low' => $query->orderByRaw('(unit_price * quantity) ASC')->orderByDesc('id'),
                default => $query->orderByDesc('updated_at')->orderByDesc('id'),
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
