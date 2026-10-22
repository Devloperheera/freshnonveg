<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminOrdersExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /** @var array<string, string> */
    public const STATUS_LABELS = [
        'awaiting_payment' => 'Awaiting payment',
        'paid' => 'Paid',
        'confirmed' => 'Confirmed',
        'payment_failed' => 'Payment failed',
        'cancelled' => 'Cancelled',
    ];

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

    public function __construct(private AdminOrdersExportService $exporter) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = $this->filters($request);

        $orders = $this->filteredQuery($filters)
            ->with(['user:id,name,phone,email,profile_image', 'items.product.images'])
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = $this->filteredQuery($filters, sorted: false);
        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'revenue' => (float) (clone $summaryQuery)->whereIn('status', Order::PAID_STATUSES)->sum('total'),
            'paid' => (clone $summaryQuery)->whereIn('status', Order::PAID_STATUSES)->count(),
            'cancelled' => (clone $summaryQuery)->where('status', 'cancelled')->count(),
            'pending' => (clone $summaryQuery)->whereIn('status', ['awaiting_payment', 'payment_failed'])->count(),
        ];

        $cities = Order::query()->whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city');

        return view('Admin.Orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'summary' => $summary,
            'cities' => $cities,
            'perPage' => $perPage,
            'exportQuery' => http_build_query(array_filter($filters, fn ($v) => $v !== '')),
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        return $this->exporter->downloadExcel($this->exportRows($request));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        return $this->exporter->downloadCsv($this->exportRows($request));
    }

    public function show(Order $order): View
    {
        if ($order->admin_seen_at === null) {
            $order->forceFill(['admin_seen_at' => now()])->save();
        }

        $this->loadFull($order);

        $customerStats = $order->user_id ? [
            'orders' => Order::where('user_id', $order->user_id)->count(),
            'spent' => (float) Order::where('user_id', $order->user_id)->whereIn('status', Order::PAID_STATUSES)->sum('total'),
        ] : null;

        return view('Admin.Orders.show', compact('order', 'customerStats'));
    }

    public function billPdf(Order $order): Response
    {
        $this->loadFull($order);

        return Pdf::loadView('Admin.Orders.bill-pdf', compact('order'))
            ->setPaper('a4')
            ->download('bill-'.$order->order_number.'.pdf');
    }

    public function billExcel(Order $order): StreamedResponse
    {
        $this->loadFull($order);

        return $this->exporter->downloadExcel(collect([$order]), 'bill-'.$order->order_number);
    }

    private function loadFull(Order $order): void
    {
        $order->load(['user', 'items.product.images', 'items.product.category:id,name', 'coupon']);
    }

    /** @return Collection<int, Order> */
    private function exportRows(Request $request): Collection
    {
        return $this->filteredQuery($this->filters($request))
            ->with(['user', 'items.product.images'])
            ->get();
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
            'delivery_type' => (string) $request->input('delivery_type', ''),
            'city' => (string) $request->input('city', ''),
            'pincode' => trim((string) $request->input('pincode', '')),
            'user_id' => (string) $request->input('user_id', ''),
            'date_field' => $request->input('date_field') === 'delivery' ? 'delivery' : 'order',
            'preset' => (string) $request->input('preset', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'amount_min' => (string) $request->input('amount_min', ''),
            'amount_max' => (string) $request->input('amount_max', ''),
            'coupon' => (string) $request->input('coupon', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];
    }

    /** @param  array<string, string>  $f */
    private function filteredQuery(array $f, bool $sorted = true): Builder
    {
        $query = Order::query();

        if ($f['q'] !== '') {
            $term = '%'.$f['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('order_number', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('delivery_address', 'like', $term)
                    ->orWhere('cashfree_payment_id', 'like', $term)
                    ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term))
                    ->orWhereHas('items', fn (Builder $i) => $i->where('product_name', 'like', $term));
            });
        }

        if (array_key_exists($f['status'], self::STATUS_LABELS)) {
            $query->where('status', $f['status']);
        }
        if (in_array($f['delivery_type'], ['instant', 'scheduled'], true)) {
            $query->where('delivery_schedule_type', $f['delivery_type']);
        }
        if ($f['city'] !== '') {
            $query->where('city', $f['city']);
        }
        if ($f['pincode'] !== '') {
            $query->where('pincode', $f['pincode']);
        }
        if (ctype_digit($f['user_id'])) {
            $query->where('user_id', (int) $f['user_id']);
        }
        if ($f['coupon'] === 'yes') {
            $query->whereNotNull('coupon_code')->where('coupon_code', '!=', '');
        } elseif ($f['coupon'] === 'no') {
            $query->where(fn (Builder $q) => $q->whereNull('coupon_code')->orWhere('coupon_code', ''));
        }
        if (is_numeric($f['amount_min'])) {
            $query->where('total', '>=', (float) $f['amount_min']);
        }
        if (is_numeric($f['amount_max'])) {
            $query->where('total', '<=', (float) $f['amount_max']);
        }

        [$from, $to] = $this->dateRange($f);
        $column = $f['date_field'] === 'delivery' ? 'delivery_date' : 'created_at';
        if ($from) {
            $query->whereDate($column, '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate($column, '<=', $to->toDateString());
        }

        if ($sorted) {
            match ($f['sort']) {
                'oldest' => $query->orderBy('id'),
                'amount_high' => $query->orderByDesc('total')->orderByDesc('id'),
                'amount_low' => $query->orderBy('total')->orderByDesc('id'),
                'delivery_date' => $query->orderBy('delivery_date')->orderBy('delivery_time'),
                default => $query->orderByDesc('id'),
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
