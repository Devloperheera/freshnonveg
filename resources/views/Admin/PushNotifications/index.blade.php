@extends('Admin.Layout.master')

@section('title', 'Notifications — FreshNonVeg Admin')

@section('custom_css')
<style>
.pn-stat { background:#fff; border-radius:10px; padding:14px 16px; box-shadow:0 4px 6px rgba(30,142,62,0.08); border-left:4px solid #2563eb; }
.pn-stat.sent { border-left-color:#1E8E3E; }
.pn-stat.pending { border-left-color:#F57C00; }
.pn-stat.failed { border-left-color:#D32F2F; }
.pn-stat .label { font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; }
.pn-stat .value { font-size:1.65rem; font-weight:800; color:#0f172a; margin-top:4px; }
.pn-stat .icon { float:right; font-size:1.35rem; opacity:.35; }
.pn-thumb { width:48px; height:48px; border-radius:8px; object-fit:cover; background:#f1f5f9; }
.pn-modal-header { background:#1e293b; color:#fff; }
.pn-modal-header .btn-close { filter:invert(1); }
</style>
@endsection

@section('content')
@php
    $f = $filters;
@endphp
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0">
                <i class="bi bi-bell me-2"></i> Notifications
            </h2>
            <p class="small text-muted mb-0 mt-1">Manage and track all push notifications</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.notifications.groups.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-people me-1"></i> Groups
            </a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#sendPushModal">
                <i class="bi bi-send me-1"></i> Send Notification
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="pn-stat">
                <i class="bi bi-stack pn-stat icon"></i>
                <div class="label">Total</div>
                <div class="value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pn-stat sent">
                <i class="bi bi-check-circle pn-stat icon"></i>
                <div class="label">Sent</div>
                <div class="value">{{ number_format($stats['sent']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pn-stat pending">
                <i class="bi bi-clock pn-stat icon"></i>
                <div class="label">Pending</div>
                <div class="value">{{ number_format($stats['pending']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pn-stat failed">
                <i class="bi bi-x-circle pn-stat icon"></i>
                <div class="label">Failed</div>
                <div class="value">{{ number_format($stats['failed']) }}</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <form method="GET" class="filter-panel mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Search</label>
                    <input type="search" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" placeholder="Title ya message">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="sent" @selected($f['status'] === 'sent')>Sent</option>
                        <option value="pending" @selected($f['status'] === 'pending')>Pending</option>
                        <option value="failed" @selected($f['status'] === 'failed')>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Group</label>
                    <select name="group_id" class="form-select form-select-sm">
                        <option value="">All groups</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected((string) $f['group_id'] === (string) $g->id)>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Date preset</label>
                    <select name="preset" class="form-select form-select-sm">
                        <option value="">Custom / All</option>
                        @foreach(\App\Http\Controllers\Admin\PushNotificationController::DATE_PRESETS as $key => $label)
                            <option value="{{ $key }}" @selected($f['preset'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">From</label>
                    <input type="date" name="date_from" value="{{ $f['date_from'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">To</label>
                    <input type="date" name="date_to" value="{{ $f['date_to'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Sort</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($f['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($f['sort'] === 'oldest')>Oldest first</option>
                        <option value="scheduled" @selected($f['sort'] === 'scheduled')>Scheduled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Per page</label>
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach(\App\Http\Controllers\Admin\PushNotificationController::PER_PAGE_OPTIONS as $n)
                            <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex gap-2 mt-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Apply filters</button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <h6 class="fw-bold mb-2 px-1">Notifications Records</h6>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Title &amp; Message</th>
                        <th>Group</th>
                        <th>Recipients</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Sent At</th>
                        <th>Scheduled</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>
                                @if($row->imageUrl())
                                    <img src="{{ $row->imageUrl() }}" alt="" class="pn-thumb">
                                @else
                                    <span class="pn-thumb d-inline-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></span>
                                @endif
                            </td>
                            <td style="max-width:280px;">
                                <div class="fw-semibold">{{ $row->title }}</div>
                                <div class="small text-muted">{{ Str::limit($row->message, 100) }}</div>
                                @if($row->click_action)
                                    <code class="small d-block mt-1">{{ Str::limit($row->click_action, 40) }}</code>
                                @endif
                                @if($row->error_summary && $row->status === 'failed')
                                    <div class="small text-danger mt-1">{{ Str::limit($row->error_summary, 80) }}</div>
                                @endif
                            </td>
                            <td><span class="badge bg-primary">{{ $row->group?->name ?? '—' }}</span></td>
                            <td class="small">{{ $row->recipients_count }} recipients</td>
                            <td class="small">{{ $row->deliveryLabel() }}</td>
                            <td>
                                @if($row->status === 'sent')
                                    <span class="badge bg-success">Sent</span>
                                @elseif($row->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td class="small text-nowrap">{{ $row->sent_at?->format('d M Y h:i A') ?? '—' }}</td>
                            <td class="small text-nowrap">{{ $row->scheduled_at?->format('d M Y h:i A') ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.notifications.resend', $row) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Resend"><i class="bi bi-arrow-repeat"></i> Resend</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-4 text-muted">Abhi koi notification record nahi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $records->links() }}</div>
    </div>
</div>

<div class="modal fade" id="sendPushModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.notifications.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header pn-modal-header">
                    <h5 class="modal-title"><i class="bi bi-send me-2"></i> Send Push Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Select Group <span class="text-danger">*</span></label>
                        <select name="notification_group_id" class="form-select" required>
                            <option value="">— Group Select Karo —</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="200" placeholder="Notification title...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" rows="4" class="form-control" required maxlength="5000" placeholder="Notification message..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png">
                        <div class="form-text">Max 5MB. JPG, PNG, JPEG</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Click Action (Optional)</label>
                        <input type="text" name="click_action" class="form-control" maxlength="500" placeholder="app://screen or https://...">
                        <div class="form-text">Deep link ya URL</div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="schedule_later" value="1" id="scheduleLaterCheck">
                        <label class="form-check-label" for="scheduleLaterCheck"><i class="bi bi-clock me-1"></i> Schedule for later</label>
                    </div>
                    <div class="mb-0" id="scheduleAtWrap" hidden>
                        <label class="form-label">Schedule date &amp; time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i> Send Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
(function () {
    var chk = document.getElementById('scheduleLaterCheck');
    var wrap = document.getElementById('scheduleAtWrap');
    if (!chk || !wrap) return;
    chk.addEventListener('change', function () {
        wrap.hidden = !chk.checked;
    });
})();
</script>
@endsection
