@extends('Admin.Layout.master')

@section('title', 'Notification Groups — FreshNonVeg Admin')

@section('custom_css')
<style>
.ng-stat-card { background:#fff; border-radius:10px; padding:14px 16px; box-shadow:0 4px 6px rgba(30,142,62,0.08); border-left:4px solid #1E8E3E; }
.ng-stat-card.blue { border-left-color:#2563eb; }
.ng-stat-card.orange { border-left-color:#F57C00; }
.ng-stat-card .label { font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; }
.ng-stat-card .value { font-size:1.65rem; font-weight:800; color:#0f172a; line-height:1.2; margin-top:4px; }
.ng-stat-card .icon { float:right; font-size:1.35rem; opacity:.35; }
.ng-icon-thumb { width:40px; height:40px; border-radius:8px; object-fit:cover; background:#f1f5f9; }
.ng-modal-header { background:#1e293b; color:#fff; }
.ng-modal-header .btn-close { filter:invert(1); }
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
                <i class="bi bi-bell me-2"></i> Notification Groups
            </h2>
            <p class="small text-muted mb-0 mt-1">User groups banao aur push notifications target karo</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGroupModal">
            <i class="bi bi-plus-lg me-1"></i> Create Group
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="ng-stat-card blue">
                <i class="bi bi-stack ng-stat-card icon"></i>
                <div class="label">Total Groups</div>
                <div class="value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ng-stat-card">
                <i class="bi bi-person-check ng-stat-card icon"></i>
                <div class="label">Active Groups</div>
                <div class="value">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ng-stat-card orange">
                <i class="bi bi-people ng-stat-card icon"></i>
                <div class="label">Total Members</div>
                <div class="value">{{ number_format($stats['members']) }}</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <h6 class="fw-bold mb-2 px-1">Groups Records</h6>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Group Name</th>
                        <th>Description</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td>
                                @if($group->iconUrl())
                                    <img src="{{ $group->iconUrl() }}" alt="" class="ng-icon-thumb">
                                @else
                                    <span class="ng-icon-thumb d-inline-flex align-items-center justify-content-center text-muted"><i class="bi bi-people"></i></span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $group->name }}</td>
                            <td class="small text-muted" style="max-width:220px;">{{ $group->description ? Str::limit($group->description, 80) : '—' }}</td>
                            <td><span class="badge bg-primary rounded-pill">{{ $group->users_count }}</span></td>
                            <td>{{ $group->isActive() ? 'Active' : 'Inactive' }}</td>
                            <td class="text-nowrap small">{{ $group->created_at?->format('d M Y') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.notifications.groups.users', $group) }}" class="btn btn-sm btn-outline-primary" title="Members"><i class="bi bi-person-lines-fill"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-warning edit-group-btn" title="Edit"
                                    data-bs-toggle="modal" data-bs-target="#editGroupModal"
                                    data-action="{{ route('admin.notifications.groups.update', $group) }}"
                                    data-name="{{ e($group->name) }}"
                                    data-description="{{ e(str_replace(["\r", "\n"], ' ', (string) ($group->description ?? ''))) }}"
                                    data-status="{{ $group->status }}"
                                    data-icon="{{ $group->iconUrl() }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.notifications.groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('Delete this group?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Abhi koi group nahi. Create Group dabayein.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.notifications.groups.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header ng-modal-header">
                    <h5 class="modal-title">Create Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="120">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control" maxlength="2000" placeholder="Group ke baare mein kuch likho..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Group Icon</label>
                        <input type="file" name="icon" class="form-control" accept=".jpg,.jpeg,.png">
                        <div class="form-text">Optional. Max 2MB. JPG, PNG</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editGroupForm" action="#" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header ng-modal-header">
                    <h5 class="modal-title">Edit Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2 text-center" id="editGroupIconWrap" hidden>
                        <img src="" alt="" id="editGroupIconPreview" class="ng-icon-thumb mb-1">
                        <div class="small text-muted">Current icon</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editGroupName" class="form-control" required maxlength="120">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editGroupDescription" rows="3" class="form-control" maxlength="2000" placeholder="Group ke baare mein kuch likho..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Group Icon</label>
                        <input type="file" name="icon" class="form-control" accept=".jpg,.jpeg,.png">
                        <div class="form-text">Optional. Max 2MB. JPG, PNG</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" id="editGroupStatus" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
document.querySelectorAll('.edit-group-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var form = document.getElementById('editGroupForm');
        form.action = btn.dataset.action || '#';
        document.getElementById('editGroupName').value = btn.dataset.name || '';
        document.getElementById('editGroupDescription').value = btn.dataset.description || '';
        document.getElementById('editGroupStatus').value = btn.dataset.status || '1';
        var wrap = document.getElementById('editGroupIconWrap');
        var img = document.getElementById('editGroupIconPreview');
        if (btn.dataset.icon) {
            img.src = btn.dataset.icon;
            wrap.hidden = false;
        } else {
            wrap.hidden = true;
            img.removeAttribute('src');
        }
    });
});
</script>
@endsection
