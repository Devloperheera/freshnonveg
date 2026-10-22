@extends('Admin.Layout.master')
@section('title', 'My Profile — FreshNonVeg Admin')

@section('custom_css')
<style>:root{--primary-color:#2BAE49;--primary-mid:#1E8E3E;--primary-light:#F57C00;--secondary-color:#F57C00;--golden:#D32F2F;--light-bg:#FFFBF7}.profile-hero{background:linear-gradient(135deg,var(--primary-color),var(--primary-mid));border-radius:12px;padding:32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden}.profile-hero::after{content:'';position:absolute;right:-40px;top:-40px;width:180px;height:180px;background:rgba(255,255,255,.06);border-radius:50%}.profile-hero::before{content:'';position:absolute;left:-30px;bottom:-30px;width:120px;height:120px;background:rgba(249,115,22,.08);border-radius:50%}.avatar-circle{width:72px;height:72px;background:var(--secondary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#fff;flex-shrink:0;border:3px solid rgba(255,255,255,.3)}.info-card{background:#fff;border-radius:12px;border:1px solid #dee2e6;overflow:hidden;margin-bottom:20px}.info-card-header{background:var(--light-bg);padding:14px 20px;border-bottom:1px solid #dee2e6;display:flex;align-items:center;gap:10px}.info-card-header i{color:var(--primary-color);font-size:15px}.info-card-header span{font-size:13px;font-weight:700;color:var(--primary-color);text-transform:uppercase;letter-spacing:.5px}.info-row{display:flex;align-items:center;padding:14px 20px;border-bottom:1px solid #dee2e6}.info-row:last-child{border-bottom:none}.info-label{font-size:12px;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.4px;width:160px;flex-shrink:0}.info-value{font-size:14px;font-weight:600;color:#2c3e50}.status-badge{display:inline-flex;align-items:center;gap:5px;background:#eef5fc;color:#15803d;border:1px solid #bbf7d0;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700}.form-section{background:#fff;border-radius:12px;border:1px solid #dee2e6;overflow:hidden;margin-bottom:20px}.section-title{background:var(--light-bg);padding:14px 20px;margin:0;border-bottom:1px solid #dee2e6;font-size:14px;font-weight:700;color:var(--primary-color);text-transform:uppercase;letter-spacing:.5px}.section-title i{color:var(--secondary-color)}.form-section form{padding:20px}.form-label{font-weight:600;font-size:13px;color:#2c3e50;margin-bottom:6px}.required-field{color:#dc3545}.form-control{border:1.5px solid #dee2e6;border-radius:8px;padding:8px 12px;font-size:13px;transition:all .2s}.form-control:focus{border-color:var(--primary-color);box-shadow:0 0 0 3px rgba(0,62,126,0.1);outline:none}.btn-primary{background:linear-gradient(135deg,var(--primary-color),var(--primary-mid));border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;color:#fff}.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,62,126,0.3);background:linear-gradient(135deg,var(--primary-mid),var(--primary-color));color:#fff}.btn-warning{background:var(--secondary-color);border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:all .2s}.btn-warning:hover{background:#b84a15;transform:translateY(-1px);color:#fff}.btn-outline-secondary{border:1px solid #dee2e6;border-radius:8px;padding:6px 14px;font-size:12px;color:#6c757d;background:#fff;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}.btn-outline-secondary:hover{border-color:var(--primary-color);color:var(--primary-color)}.alert-success-custom{background:#e8f5e9;border:1px solid #a5d6a7;color:#1b5e20;border-radius:8px;padding:12px 16px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;margin-bottom:20px}.alert-danger-custom{background:#fdecea;border:1px solid #f5c6cb;color:#7f1d1d;border-radius:8px;padding:12px 16px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;margin-bottom:20px}.invalid-feedback{font-size:11px;margin-top:4px}.alert-info-custom{background:#e8f4fd;border:1px solid #b8e0f7;border-radius:8px;padding:12px 16px;margin-top:20px;display:flex;align-items:center;gap:10px}.alert-info-custom i{color:#0c5e8a;font-size:14px}.alert-info-custom span{font-size:12px;color:#0c5e8a}@media(max-width:768px){.info-row{flex-direction:column;align-items:flex-start;gap:8px}.info-label{width:auto}}</style>
@endsection

@section('content')
<div class="container-fluid mt-4">

    {{-- Page Title --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 style="color:var(--primary-color);font-weight:800;margin:0">
                <i class="fas fa-user-circle me-2" style="color:var(--secondary-color)"></i>My Profile
            </h4>
            <small style="color:#6c757d">Manage your admin account details</small>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    {{-- Success / Error Alerts --}}
    @if(session('success_profile'))
        <div class="alert-success-custom">
            <i class="fas fa-check-circle"></i> {{ session('success_profile') }}
        </div>
    @endif

    @if(session('success_password'))
        <div class="alert-success-custom">
            <i class="fas fa-check-circle"></i> {{ session('success_password') }}
        </div>
    @endif

    @if(session('error_profile'))
        <div class="alert-danger-custom">
            <i class="fas fa-exclamation-circle"></i> {{ session('error_profile') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- Left — Profile Hero + Info --}}
        <div class="col-lg-4">

            {{-- Hero Card --}}
            <div class="profile-hero">
                <div class="d-flex align-items-center gap-3 mb-3" style="position:relative;z-index:1">
                    <div class="avatar-circle">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:800">{{ Auth::guard('admin')->user()->name }}</div>
                        <div style="font-size:12px;opacity:.75">{{ Auth::guard('admin')->user()->email }}</div>
                    </div>
                </div>
                <div style="position:relative;z-index:1">
                    <span class="status-badge"><i class="fas fa-circle" style="font-size:8px"></i> Active</span>
                    <span style="font-size:11px;opacity:.6;margin-left:10px">Administrator</span>
                </div>
            </div>

            {{-- Account Info --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-info-circle"></i>
                    <span>Account Information</span>
                </div>
                <div class="info-row">
                    <div class="info-label">Role</div>
                    <div class="info-value">Administrator</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge"><i class="fas fa-circle" style="font-size:8px"></i> Active</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ Auth::guard('admin')->user()->email }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ Auth::guard('admin')->user()->phone ?? 'Not provided' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Member Since</div>
                    <div class="info-value">{{ Auth::guard('admin')->user()->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value">{{ Auth::guard('admin')->user()->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>

        </div>

        {{-- Right — Edit Forms --}}
        <div class="col-lg-8">

            {{-- Update Profile Form --}}
            <div class="form-section">
                <h5 class="section-title">
                    <i class="fas fa-edit me-2"></i>Update Profile
                </h5>
                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Full Name <span class="required-field">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', Auth::guard('admin')->user()->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Email Address <span class="required-field">*</span></label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', Auth::guard('admin')->user()->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', Auth::guard('admin')->user()->phone ?? '') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                        <button type="reset" class="btn-warning">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>

            {{-- Change Password Form --}}
            <div class="form-section">
                <h5 class="section-title">
                    <i class="fas fa-lock me-2"></i>Change Password
                </h5>
                {{-- ✅ FIXED: Route name changed from 'admin.profile.password' to 'admin.password.update' --}}
                <form method="POST" action="{{ route('admin.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Current Password <span class="required-field">*</span></label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Enter current password"
                                   required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password <span class="required-field">*</span></label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min 8 characters"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password <span class="required-field">*</span></label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Re-enter new password"
                                   required>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- Info Note --}}
            <div class="alert-info-custom">
                <i class="fas fa-info-circle"></i>
                <span>Your password should be at least 8 characters long and include letters, numbers, and special characters for better security.</span>
            </div>

        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
document.querySelector('input[name="password"]')?.addEventListener('input', function() {
    const password = this.value;
    let strengthDiv = document.getElementById('passwordStrength');
    if (!strengthDiv) {
        strengthDiv = document.createElement('div');
        strengthDiv.id = 'passwordStrength';
        strengthDiv.className = 'mt-1';
        strengthDiv.style.fontSize = '11px';
        this.parentNode.appendChild(strengthDiv);
    }
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
    } else if (password.length < 6) {
        strengthDiv.innerHTML = '<span style="color:#dc3545">❌ Weak - Password must be at least 8 characters</span>';
    } else if (password.length < 8) {
        strengthDiv.innerHTML = '<span style="color:#ffc107">⚠️ Medium - Add more characters for strong password</span>';
    } else {
        strengthDiv.innerHTML = '<span style="color:#28a745">✅ Strong password</span>';
    }
});
</script>
@endsection
