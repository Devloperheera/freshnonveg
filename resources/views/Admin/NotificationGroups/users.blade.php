@extends('Admin.Layout.master')

@section('title', $group->name.' — Group Members')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.ng-users-shell { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media (max-width:991px) { .ng-users-shell { grid-template-columns:1fr; } }
.ng-panel { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:14px; }
.ng-panel-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:12px; }
.ng-panel-head h5 { margin:0; font-weight:800; font-size:1rem; }
.ng-badge { font-size:12px; font-weight:700; padding:4px 10px; border-radius:999px; }
.ng-badge-blue { background:#dbeafe; color:#1d4ed8; }
.ng-badge-green { background:#dcfce7; color:#15803d; }
.ng-select-bar { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:8px 12px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; font-size:13px; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0">
                <i class="bi bi-people me-2"></i> {{ $group->name }}
            </h2>
            <p class="small text-muted mb-0 mt-1">Group members manage karein — push notification ke liye</p>
        </div>
        <a href="{{ route('admin.notifications.groups.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Groups</a>
    </div>

    <div class="ng-users-shell">
        {{-- Left: members --}}
        <div class="ng-panel">
            <div class="ng-panel-head">
                <h5>Group Members</h5>
                <span class="ng-badge ng-badge-blue">{{ $members->total() }}</span>
            </div>
            <form method="GET" class="row g-2 mb-2">
                <input type="hidden" name="available_q" value="{{ $availableSearch }}">
                <div class="col-8">
                    <input type="search" name="member_q" value="{{ $memberSearch }}" class="form-control form-control-sm" placeholder="Name, Email, Phone...">
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-2 adm-catalog-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Online</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $user)
                            <tr>
                                <td>@include('Admin.partials.user-cell', ['user' => $user])</td>
                                <td class="small">{{ $user->phone ?: ($user->email ?: '—') }}</td>
                                <td><span class="badge {{ $user->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ $user->isActive() ? 'Active' : 'Inactive' }}</span></td>
                                <td class="small">
                                    @if($user->is_logged_in)
                                        <span class="text-success"><i class="bi bi-circle-fill" style="font-size:8px"></i> Online</span>
                                    @else
                                        <span class="text-muted">Offline</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.notifications.groups.members.remove', [$group, $user]) }}" onsubmit="return confirm('Remove from group?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-person-dash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-person-x d-block fs-4 mb-1"></i>
                                    Koi member nahi mila
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $members->withQueryString()->links() }}
        </div>

        {{-- Right: add users --}}
        <div class="ng-panel">
            <div class="ng-panel-head">
                <h5>Users Add Karo</h5>
                <span class="ng-badge ng-badge-green">{{ number_format($availableTotal) }} Available</span>
            </div>
            <form method="GET" class="row g-2 mb-2">
                <input type="hidden" name="member_q" value="{{ $memberSearch }}">
                <div class="col-8">
                    <input type="search" name="available_q" value="{{ $availableSearch }}" class="form-control form-control-sm" placeholder="Name, Email, Phone...">
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
                </div>
            </form>
            <p class="small text-muted mb-2">Jo users pehle se group mein hain woh yahan nahi dikhenge</p>

            <form method="POST" action="{{ route('admin.notifications.groups.members.add', $group) }}" id="addMembersForm">
                @csrf
                <div class="ng-select-bar">
                    <label class="mb-0 d-flex align-items-center gap-2">
                        <input type="checkbox" id="selectAllAvailable" class="form-check-input m-0">
                        <span>Select All (Current Page)</span>
                    </label>
                    <span id="selectedCountLabel">0 Selected</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-2 adm-catalog-table">
                        <thead>
                            <tr>
                                <th style="width:36px"></th>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Wallet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($availableUsers as $user)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input available-user-cb" name="user_ids[]" value="{{ $user->id }}"></td>
                                    <td>@include('Admin.partials.user-cell', ['user' => $user])</td>
                                    <td class="small">{{ $user->phone ?: ($user->email ?: '—') }}</td>
                                    <td>
                                        <span class="badge {{ $user->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ $user->isActive() ? 'Active' : 'Inactive' }}</span>
                                        @if($user->is_logged_in)
                                            <span class="text-success small d-block"><i class="bi bi-circle-fill" style="font-size:7px"></i> Online</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        ₹{{ number_format((float) $user->wallet_balance, 2) }}<br>
                                        <span class="text-muted">{{ (int) $user->paid_orders_count }} orders</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">Koi user available nahi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100" id="addMembersBtn" disabled>
                    <i class="bi bi-person-plus me-1"></i> Add Selected to Group
                </button>
            </form>
            {{ $availableUsers->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
(function () {
    var selectAll = document.getElementById('selectAllAvailable');
    var boxes = document.querySelectorAll('.available-user-cb');
    var countLabel = document.getElementById('selectedCountLabel');
    var addBtn = document.getElementById('addMembersBtn');

    function refreshCount() {
        var n = document.querySelectorAll('.available-user-cb:checked').length;
        countLabel.textContent = n + ' Selected';
        if (addBtn) addBtn.disabled = n === 0;
        if (selectAll) {
            selectAll.checked = boxes.length > 0 && n === boxes.length;
            selectAll.indeterminate = n > 0 && n < boxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            boxes.forEach(function (cb) { cb.checked = selectAll.checked; });
            refreshCount();
        });
    }
    boxes.forEach(function (cb) { cb.addEventListener('change', refreshCount); });
    refreshCount();
})();
</script>
@endsection
