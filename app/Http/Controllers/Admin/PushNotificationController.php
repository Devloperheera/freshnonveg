<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationGroup;
use App\Models\PushNotification;
use App\Services\PushNotificationDispatchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PushNotificationController extends Controller
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

    public function __construct(private PushNotificationDispatchService $dispatch) {}

    public function index(Request $request): View
    {
        $this->dispatch->dispatchDueScheduled();

        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        $filters = $this->filtersFromRequest($request);
        $groups = NotificationGroup::query()->where('status', 1)->orderBy('name')->get(['id', 'name']);

        $records = $this->filteredQuery($filters)
            ->with('group:id,name')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => PushNotification::query()->count(),
            'sent' => PushNotification::query()->where('status', PushNotification::STATUS_SENT)->count(),
            'pending' => PushNotification::query()->where('status', PushNotification::STATUS_PENDING)->count(),
            'failed' => PushNotification::query()->where('status', PushNotification::STATUS_FAILED)->count(),
        ];

        return view('Admin.PushNotifications.index', compact('records', 'filters', 'perPage', 'stats', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_group_id' => ['required', 'integer', 'exists:notification_groups,id'],
            'title' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'click_action' => ['nullable', 'string', 'max:500'],
            'schedule_later' => ['nullable', 'boolean'],
            'scheduled_at' => ['required_if:schedule_later,1', 'nullable', 'date', 'after:now'],
        ]);

        $scheduledAt = ! empty($data['schedule_later']) && ! empty($data['scheduled_at'])
            ? Carbon::parse($data['scheduled_at'])
            : null;

        $notification = PushNotification::query()->create([
            'notification_group_id' => (int) $data['notification_group_id'],
            'title' => trim($data['title']),
            'message' => trim($data['message']),
            'click_action' => filled($data['click_action'] ?? null) ? trim($data['click_action']) : null,
            'status' => PushNotification::STATUS_PENDING,
            'scheduled_at' => $scheduledAt,
        ]);

        if ($request->hasFile('image')) {
            $notification->update([
                'image' => $request->file('image')->store('push-notifications', 'public'),
            ]);
        }

        if ($scheduledAt === null) {
            $this->dispatch->dispatch($notification->fresh());
            $notification->refresh();
            $flash = $notification->status === PushNotification::STATUS_SENT
                ? 'Notification bhej di gayi.'
                : 'Notification send fail: '.($notification->error_summary ?? 'Unknown error');
            $type = $notification->status === PushNotification::STATUS_SENT ? 'success' : 'error';

            return redirect()->route('admin.notifications.index')->with($type, $flash);
        }

        return redirect()->route('admin.notifications.index')->with('success', 'Notification schedule ho gayi.');
    }

    public function resend(PushNotification $pushNotification): RedirectResponse
    {
        $clone = PushNotification::query()->create([
            'notification_group_id' => $pushNotification->notification_group_id,
            'title' => $pushNotification->title,
            'message' => $pushNotification->message,
            'image' => $pushNotification->image,
            'click_action' => $pushNotification->click_action,
            'status' => PushNotification::STATUS_PENDING,
            'scheduled_at' => null,
        ]);

        $this->dispatch->dispatch($clone);
        $clone->refresh();

        $flash = $clone->status === PushNotification::STATUS_SENT
            ? 'Notification dubara bhej di gayi.'
            : 'Resend fail: '.($clone->error_summary ?? 'Unknown error');

        return back()->with($clone->status === PushNotification::STATUS_SENT ? 'success' : 'error', $flash);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
            'group_id' => $request->input('group_id'),
            'preset' => (string) $request->input('preset', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'sort' => (string) $request->input('sort', 'newest'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = PushNotification::query();

        if ($filters['q'] !== '') {
            $like = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($like): void {
                $q->where('title', 'like', $like)->orWhere('message', 'like', $like);
            });
        }

        if ($filters['status'] !== '' && in_array($filters['status'], PushNotification::STATUSES, true)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['group_id'])) {
            $query->where('notification_group_id', (int) $filters['group_id']);
        }

        $this->applyDateFilters($query, $filters);

        return match ($filters['sort']) {
            'oldest' => $query->orderBy('id'),
            'scheduled' => $query->orderByDesc('scheduled_at')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDateFilters(Builder $query, array $filters): void
    {
        $tz = config('app.timezone');
        $preset = $filters['preset'];

        if ($preset === 'today') {
            $query->whereDate('created_at', today($tz));

            return;
        }
        if ($preset === 'yesterday') {
            $query->whereDate('created_at', today($tz)->subDay());

            return;
        }
        if ($preset === 'last_7') {
            $query->where('created_at', '>=', now($tz)->subDays(7));

            return;
        }
        if ($preset === 'last_30') {
            $query->where('created_at', '>=', now($tz)->subDays(30));

            return;
        }
        if ($preset === 'this_month') {
            $query->whereBetween('created_at', [now($tz)->startOfMonth(), now($tz)->endOfMonth()]);

            return;
        }
        if ($preset === 'last_month') {
            $start = now($tz)->subMonth()->startOfMonth();
            $query->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]);

            return;
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
