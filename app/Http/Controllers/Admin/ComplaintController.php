<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ComplaintReplyMail;
use App\Models\Complaint;
use App\Services\WalletService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ComplaintController extends Controller
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
    public const WAITING_OPTIONS = [
        '1' => 'Waiting 1+ day',
        '3' => 'Waiting 3+ days',
        '7' => 'Waiting 7+ days',
    ];

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = $this->filters($request);

        $complaints = $this->filteredQuery($filters)
            ->with(['user:id,name,phone,email,wallet_balance,profile_image,status', 'order:id,order_number'])
            ->paginate($perPage)
            ->withQueryString();

        $base = $this->filteredQuery($filters, sorted: false, withStatus: false);
        $summary = [
            'all' => (clone $base)->count(),
            'open' => (clone $base)->where('status', 'open')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'resolved' => (clone $base)->where('status', 'resolved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'credited' => (float) (clone $base)->sum('credited_amount'),
        ];

        return view('Admin.Complaints.index', compact('complaints', 'filters', 'perPage', 'summary'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['user', 'order.items', 'walletTransactions']);

        return view('Admin.Complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint, WalletService $wallet): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Complaint::STATUSES))],
            'admin_reply' => ['nullable', 'string', 'max:3000'],
            'credit_amount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'notify_customer' => ['nullable', 'boolean'],
        ]);

        $credit = round((float) ($data['credit_amount'] ?? 0), 2);
        $adminId = auth('admin')->id();

        DB::transaction(function () use ($complaint, $data, $credit, $wallet, $adminId) {
            if ($credit > 0) {
                $wallet->credit(
                    $complaint->user,
                    $credit,
                    'Complaint #'.$complaint->id.' compensation — '.$complaint->subject,
                    $complaint,
                    $complaint->order,
                    'credit',
                    $adminId
                );
            }

            $complaint->update([
                'status' => $data['status'],
                'admin_reply' => $data['admin_reply'] ?? $complaint->admin_reply,
                'credited_amount' => round((float) $complaint->credited_amount + $credit, 2),
                'resolved_at' => in_array($data['status'], ['resolved', 'rejected'], true)
                    ? ($complaint->resolved_at ?? now())
                    : null,
            ]);
        });

        $message = $credit > 0
            ? 'Complaint update ho gayi aur ₹'.number_format($credit, 2).' user ke wallet me credit ho gaye.'
            : 'Complaint update ho gayi.';

        $complaint->refresh()->load(['user', 'order']);
        $shouldMail = (filled($complaint->admin_reply) || $credit > 0)
            && $request->boolean('notify_customer', true);
        if ($shouldMail) {
            $email = trim((string) $complaint->user?->email);
            if ($email === '') {
                $message .= ' Customer ka email nahi hai, isliye mail nahi gaya.';
            } else {
                try {
                    Mail::to($email)->send(new ComplaintReplyMail($complaint, $credit));
                    $complaint->forceFill(['reply_emailed_at' => now()])->save();
                    $message .= ' Reply '.$email.' par email ho gaya.';
                } catch (Throwable $e) {
                    Log::error('Complaint reply email failed', ['complaint' => $complaint->id, 'error' => $e->getMessage()]);
                    $message .= ' Lekin email nahi ja paya: '.$e->getMessage();
                }
            }
        }

        return redirect()->route('admin.complaints.show', $complaint)->with('success', $message);
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
            'user_id' => (string) $request->input('user_id', ''),
            'user_status' => (string) $request->input('user_status', ''),
            'has_order' => (string) $request->input('has_order', ''),
            'has_reply' => (string) $request->input('has_reply', ''),
            'emailed' => (string) $request->input('emailed', ''),
            'credited' => (string) $request->input('credited', ''),
            'waiting' => (string) $request->input('waiting', ''),
            'amount_min' => (string) $request->input('amount_min', ''),
            'amount_max' => (string) $request->input('amount_max', ''),
            'date_field' => $request->input('date_field') === 'resolved' ? 'resolved' : 'created',
            'preset' => (string) $request->input('preset', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];
    }

    /** @param  array<string, string>  $f */
    private function filteredQuery(array $f, bool $sorted = true, bool $withStatus = true): Builder
    {
        $query = Complaint::query();

        if ($f['q'] !== '') {
            $term = '%'.$f['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('subject', 'like', $term)
                    ->orWhere('message', 'like', $term)
                    ->orWhere('admin_reply', 'like', $term)
                    ->orWhereHas('order', fn (Builder $o) => $o->where('order_number', 'like', $term))
                    ->orWhereHas('user', function (Builder $u) use ($term) {
                        $u->where(function (Builder $u) use ($term) {
                            $u->where('name', 'like', $term)
                                ->orWhere('phone', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                    });
            });
        }

        if ($withStatus && array_key_exists($f['status'], Complaint::STATUSES)) {
            $query->where('status', $f['status']);
        }

        if (ctype_digit($f['user_id'])) {
            $query->where('user_id', (int) $f['user_id']);
        }

        if ($f['user_status'] === 'active') {
            $query->whereHas('user', fn (Builder $u) => $u->where('status', 1));
        } elseif ($f['user_status'] === 'inactive') {
            $query->whereHas('user', fn (Builder $u) => $u->where('status', '!=', 1));
        }

        if ($f['has_order'] === 'yes') {
            $query->whereNotNull('order_id');
        } elseif ($f['has_order'] === 'no') {
            $query->whereNull('order_id');
        }

        if ($f['has_reply'] === 'yes') {
            $query->whereNotNull('admin_reply')->where('admin_reply', '!=', '');
        } elseif ($f['has_reply'] === 'no') {
            $query->where(fn (Builder $q) => $q->whereNull('admin_reply')->orWhere('admin_reply', ''));
        }

        if ($f['emailed'] === 'sent') {
            $query->whereNotNull('reply_emailed_at');
        } elseif ($f['emailed'] === 'pending') {
            $query->whereNull('reply_emailed_at');
        }

        if ($f['credited'] === 'yes') {
            $query->where('credited_amount', '>', 0);
        } elseif ($f['credited'] === 'no') {
            $query->where(fn (Builder $q) => $q->whereNull('credited_amount')->orWhere('credited_amount', '<=', 0));
        }

        if (array_key_exists($f['waiting'], self::WAITING_OPTIONS)) {
            $query->whereIn('status', ['open', 'in_progress'])
                ->where(fn (Builder $q) => $q->whereNull('admin_reply')->orWhere('admin_reply', ''))
                ->where('created_at', '<=', now()->subDays((int) $f['waiting']));
        }

        if (is_numeric($f['amount_min'])) {
            $query->where('credited_amount', '>=', (float) $f['amount_min']);
        }
        if (is_numeric($f['amount_max'])) {
            $query->where('credited_amount', '<=', (float) $f['amount_max']);
        }

        [$from, $to] = $this->dateRange($f);
        $column = $f['date_field'] === 'resolved' ? 'resolved_at' : 'created_at';
        if ($from) {
            $query->whereDate($column, '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate($column, '<=', $to->toDateString());
        }

        if ($sorted) {
            match ($f['sort']) {
                'oldest' => $query->orderBy('id'),
                'credited_high' => $query->orderByDesc('credited_amount')->orderByDesc('id'),
                'waiting' => $query->orderBy('created_at')->orderBy('id'),
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
