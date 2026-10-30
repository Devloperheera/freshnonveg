<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationGroupController extends Controller
{
    public function index(): View
    {
        $groups = NotificationGroup::query()
            ->withCount('users')
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total' => NotificationGroup::query()->count(),
            'active' => NotificationGroup::query()->where('status', 1)->count(),
            'members' => (int) NotificationGroup::query()
                ->withCount('users')
                ->get()
                ->sum('users_count'),
        ];

        return view('Admin.NotificationGroups.index', compact('groups', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedGroup($request);
        $group = NotificationGroup::query()->create($data);

        if ($request->hasFile('icon')) {
            $group->update(['icon' => $request->file('icon')->store('notification-groups', 'public')]);
        }

        return redirect()->route('admin.notifications.groups.index')->with('success', 'Group create ho gaya.');
    }

    public function update(Request $request, NotificationGroup $notificationGroup): RedirectResponse
    {
        $data = $this->validatedGroup($request);
        $notificationGroup->update($data);

        if ($request->hasFile('icon')) {
            $notificationGroup->deleteIconFile();
            $notificationGroup->update(['icon' => $request->file('icon')->store('notification-groups', 'public')]);
        }

        return redirect()->route('admin.notifications.groups.index')->with('success', 'Group update ho gaya.');
    }

    public function destroy(NotificationGroup $notificationGroup): RedirectResponse
    {
        $notificationGroup->deleteIconFile();
        $notificationGroup->delete();

        return redirect()->route('admin.notifications.groups.index')->with('success', 'Group delete ho gaya.');
    }

    public function usersPage(Request $request, NotificationGroup $notificationGroup): View
    {
        $memberSearch = trim((string) $request->input('member_q', ''));
        $availableSearch = trim((string) $request->input('available_q', ''));

        $membersQuery = $notificationGroup->users()
            ->withCount(['orders as paid_orders_count' => fn (Builder $q) => $q->whereIn('status', ['paid', 'confirmed'])]);
        $this->applyUserSearch($membersQuery, $memberSearch);
        $members = $membersQuery->orderBy('users.name')->paginate(15, ['*'], 'member_page');

        $memberIds = $notificationGroup->users()->pluck('users.id');

        $availableQuery = User::query()
            ->whereNotIn('id', $memberIds)
            ->withCount(['orders as paid_orders_count' => fn (Builder $q) => $q->whereIn('status', ['paid', 'confirmed'])]);
        $this->applyUserSearch($availableQuery, $availableSearch);
        $availableUsers = $availableQuery->orderBy('users.name')->paginate(15, ['*'], 'available_page');

        return view('Admin.NotificationGroups.users', [
            'group' => $notificationGroup,
            'members' => $members,
            'availableUsers' => $availableUsers,
            'memberSearch' => $memberSearch,
            'availableSearch' => $availableSearch,
            'availableTotal' => User::query()->whereNotIn('id', $memberIds)->count(),
        ]);
    }

    public function addMembers(Request $request, NotificationGroup $notificationGroup): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $notificationGroup->users()->syncWithoutDetaching($data['user_ids']);

        return back()->with('success', count($data['user_ids']).' user(s) group mein add ho gaye.');
    }

    public function removeMember(NotificationGroup $notificationGroup, User $user): RedirectResponse
    {
        $notificationGroup->users()->detach($user->id);

        return back()->with('success', 'User group se hata diya gaya.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedGroup(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'status' => ['required', 'in:0,1'],
        ]);

        return [
            'name' => trim($data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'status' => (int) $data['status'],
        ];
    }

    private function applyUserSearch(Builder|BelongsToMany $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $like = '%'.$search.'%';
        $query->where(function (Builder $q) use ($like, $search): void {
            $q->where('users.name', 'like', $like)
                ->orWhere('users.email', 'like', $like)
                ->orWhere('users.phone', 'like', $like);

            if (ctype_digit($search)) {
                $q->orWhere('users.id', (int) $search);
            }
        });
    }
}
