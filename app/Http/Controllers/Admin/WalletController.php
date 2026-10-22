<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WalletCreditMail;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class WalletController extends Controller
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

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = $this->filters($request);
        $view = $request->input('view') === 'balances' ? 'balances' : 'transactions';

        $cards = [
            'added' => (float) WalletTransaction::query()->source('credits')->sum('amount'),
            'used' => (float) WalletTransaction::query()->source('used')->sum('amount'),
            'balance' => (float) User::query()->sum('wallet_balance'),
            'direct' => (float) WalletTransaction::query()->source('direct')->sum('amount'),
            'complaint' => (float) WalletTransaction::query()->source('complaint')->sum('amount'),
            'refund' => (float) WalletTransaction::query()->source('refund')->sum('amount'),
            'users_with_balance' => User::query()->where('wallet_balance', '>', 0)->count(),
            'users_used' => WalletTransaction::query()->source('used')->distinct()->count('user_id'),
        ];

        $transactions = null;
        $balances = null;
        $filteredTotals = null;

        if ($view === 'balances') {
            $balances = User::query()
                ->where('wallet_balance', '>', 0)
                ->when($filters['q'] !== '', fn (Builder $q) => $this->searchUsers($q, $filters['q']))
                ->withSum(['walletTransactions as credited_sum' => fn (Builder $q) => $q->whereIn('type', ['credit', 'refund'])], 'amount')
                ->withSum(['walletTransactions as used_sum' => fn (Builder $q) => $q->where('type', 'debit')], 'amount')
                ->orderByDesc('wallet_balance')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $query = $this->filteredQuery($filters);
            $filteredTotals = [
                'count' => (clone $query)->count(),
                'credit' => (float) (clone $query)->whereIn('type', ['credit', 'refund'])->sum('amount'),
                'debit' => (float) (clone $query)->where('type', 'debit')->sum('amount'),
            ];
            $transactions = $query
                ->with(['user:id,name,phone,email,profile_image,wallet_balance', 'complaint:id,subject', 'order:id,order_number', 'admin:id,name'])
                ->latest('id')
                ->paginate($perPage)
                ->withQueryString();
        }

        $selectedUser = ctype_digit($filters['user_id']) ? User::find((int) $filters['user_id']) : null;

        return view('Admin.Wallet.index', compact(
            'cards', 'transactions', 'balances', 'filteredTotals', 'filters', 'perPage', 'view', 'selectedUser'
        ));
    }

    public function credit(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $wallet = (string) $request->input('wallet', '');

        $users = User::query()
            ->when($q !== '', fn (Builder $query) => $this->searchUsers($query, $q))
            ->when($status === 'active', fn (Builder $query) => $query->where('status', 1))
            ->when($status === 'inactive', fn (Builder $query) => $query->where('status', '!=', 1))
            ->when($wallet === 'yes', fn (Builder $query) => $query->where('wallet_balance', '>', 0))
            ->when($wallet === 'no', fn (Builder $query) => $query->where('wallet_balance', '<=', 0))
            ->withCount('orders')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $selected = $request->filled('user') ? User::query()->find((int) $request->input('user')) : null;

        return view('Admin.Wallet.credit', compact('users', 'q', 'status', 'wallet', 'perPage', 'selected'));
    }

    public function store(Request $request, WalletService $wallet): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'note' => ['nullable', 'string', 'max:255'],
            'notify_customer' => ['nullable', 'boolean'],
        ], [
            'user_id.required' => 'Pehle customer select karein.',
            'amount.min' => 'Amount kam se kam ₹1 hona chahiye.',
        ]);

        $user = User::query()->findOrFail($data['user_id']);
        $note = trim((string) ($data['note'] ?? ''));
        $description = 'FreshNonVeg wallet credit'.($note !== '' ? ' — '.$note : '');

        $txn = $wallet->credit($user, (float) $data['amount'], $description, null, null, 'credit', auth('admin')->id());

        $message = '₹'.number_format((float) $txn->amount, 2).' '.$user->name.' ke wallet me add ho gaye. Naya balance ₹'.number_format((float) $txn->balance_after, 2).'.';

        if ($request->boolean('notify_customer')) {
            $email = trim((string) $user->email);
            if ($email === '') {
                $message .= ' Customer ka email nahi hai, isliye mail nahi gaya.';
            } else {
                try {
                    Mail::to($email)->send(new WalletCreditMail($txn->load('user'), $note ?: null));
                    $message .= ' Customer ko email bhi chala gaya.';
                } catch (Throwable $e) {
                    Log::error('Wallet credit email failed', ['txn' => $txn->id, 'error' => $e->getMessage()]);
                    $message .= ' Lekin email nahi ja paya.';
                }
            }
        }

        return redirect()
            ->route('admin.wallet.credit', ['user' => $user->id, 'q' => $user->phone ?: $user->name])
            ->with('success', $message);
    }

    public function userSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $users = User::query()
            ->when($term !== '', fn (Builder $q) => $this->searchUsers($q, $term))
            ->orderByDesc('id')
            ->limit(15)
            ->get(['id', 'name', 'phone', 'email', 'profile_image', 'wallet_balance']);

        return response()->json($users->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'phone' => $u->phone,
            'email' => $u->email,
            'avatar' => $u->profile_image,
            'balance' => number_format((float) $u->wallet_balance, 2),
        ]));
    }

    private function searchUsers(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $query->where(fn (Builder $q) => $q->where('name', 'like', $like)
            ->orWhere('phone', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->when(ctype_digit($term), fn (Builder $q) => $q->orWhere('id', (int) $term)));
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'source' => (string) $request->input('source', ''),
            'user_id' => (string) $request->input('user_id', ''),
            'preset' => (string) $request->input('preset', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'amount_min' => (string) $request->input('amount_min', ''),
            'amount_max' => (string) $request->input('amount_max', ''),
        ];
    }

    /** @param  array<string, string>  $f */
    private function filteredQuery(array $f): Builder
    {
        $query = WalletTransaction::query();

        if ($f['q'] !== '') {
            $term = '%'.$f['q'].'%';
            $query->where(fn (Builder $q) => $q->where('description', 'like', $term)
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term))
                ->orWhereHas('order', fn (Builder $o) => $o->where('order_number', 'like', $term)));
        }

        if (in_array($f['source'], ['direct', 'complaint', 'refund', 'used', 'credits'], true)) {
            $query->source($f['source']);
        }
        if (ctype_digit($f['user_id'])) {
            $query->where('user_id', (int) $f['user_id']);
        }
        if (is_numeric($f['amount_min'])) {
            $query->where('amount', '>=', (float) $f['amount_min']);
        }
        if (is_numeric($f['amount_max'])) {
            $query->where('amount', '<=', (float) $f['amount_max']);
        }

        [$from, $to] = $this->dateRange($f);
        if ($from) {
            $query->whereDate('created_at', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to->toDateString());
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
            default => [$this->parseDate($f['date_from']), $this->parseDate($f['date_to'])],
        };
    }

    private function parseDate(string $value): ?Carbon
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : null;
    }
}
