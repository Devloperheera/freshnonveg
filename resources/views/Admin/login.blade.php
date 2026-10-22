<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshNonVeg — Admin Login</title>
    <meta name="description" content="FreshNonVeg Admin Login Panel - Secure access for administrators to manage products, orders, and customers.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('website_assets/image/favicon.png') }}" type="image/png">
    @include('Admin.Layout.partials.auth-theme')
    <style>

        /* ===== RESET & BASE ===== */
        *{margin:0;padding:0;box-sizing:border-box}
        body{min-height:100vh;font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:var(--cream);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
        body::before{content:'';position:fixed;top:-150px;left:-150px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(211,47,47,.07),transparent 70%);pointer-events:none}
        body::after{content:'';position:fixed;bottom:-150px;right:-150px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(43,174,73,.08),transparent 70%);pointer-events:none}

        /* ===== TOP BAR ===== */
        .top-bar{position:fixed;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--brand-red-dark),var(--brand-red) 18%,var(--brand-orange) 50%,var(--brand-green) 82%,var(--brand-green-dark));z-index:10}

        /* ===== LOGIN WRAPPER ===== */
        .login-wrapper{width:100%;max-width:460px;padding:20px;z-index:1}
        .login-card{background:#fff;border-radius:var(--radius);padding:44px 40px 36px;border:1.5px solid var(--border);box-shadow:var(--shadow);position:relative}

        /* ===== CORNER DECORATIONS ===== */
        .corner{position:absolute;font-size:1.6rem;opacity:.12;pointer-events:none}
        .corner-tl{top:16px;left:18px}
        .corner-br{bottom:16px;right:18px;transform:rotate(180deg)}

        /* ===== LOGO ===== */
        .logo-area{text-align:center;margin-bottom:26px}
        .logo-wrap{display:inline-block;padding:6px;border:2px solid var(--border);background:var(--cream)}
        .logo-wrap img{width:110px;height:110px;border-radius:50%;object-fit:contain;display:block}
        .logo-sub{font-size:.8rem;color:var(--brand-orange);font-weight:700;letter-spacing:2.5px;text-transform:uppercase;margin-top:2px}

        /* ===== DIVIDER ===== */
        .divider{display:flex;align-items:center;gap:12px;margin-bottom:24px}
        .divider span{font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
        .divider::before,.divider::after{content:'';flex:1;height:1.5px;background:var(--border)}

        /* ===== FORM ===== */
        .form-label{font-size:.8rem;font-weight:700;color:var(--text-dark);margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px}
        .input-group-text{background:var(--green-bg);border:1.5px solid var(--border);border-right:none;color:var(--green);padding:0 14px;font-size:1rem}
        .form-control{border:1.5px solid var(--border);border-left:none;padding:11px 14px;font-size:.92rem;color:var(--text-dark);background:#fff;transition:border-color .25s ease}
        .form-control:focus{outline:none;box-shadow:none;border-color:var(--green);background:#fff}
        .input-group:focus-within .input-group-text{border-color:var(--green)}
        .form-control.is-invalid{border-color:#dc3545}

        /* ===== PASSWORD TOGGLE ===== */
        .pass-toggle{border:1.5px solid var(--border);border-left:none;background:var(--green-bg);color:var(--text-muted);cursor:pointer;padding:0 14px;transition:color .25s}
        .pass-toggle:hover{color:var(--green)}
        .input-group:focus-within .pass-toggle{border-color:var(--green)}

        /* ===== LINKS ===== */
        .forgot-link{font-size:.82rem;color:var(--gold-dark);text-decoration:none;font-weight:600;transition:color .2s}
        .forgot-link:hover{color:var(--gold);text-decoration:underline}
        .form-check-input:checked{background-color:var(--green);border-color:var(--green)}
        .form-check-label{font-size:.83rem;color:var(--text-muted)}

        /* ===== BUTTON ===== */
        .btn-login{width:100%;padding:14px;font-size:.97rem;font-weight:700;letter-spacing:.4px;border:none;border-radius:12px;color:#fff;background:linear-gradient(135deg,var(--brand-red),var(--brand-orange),var(--brand-green));transition:all .3s ease;margin-top:4px;cursor:pointer}
        .btn-login:hover{opacity:.92;transform:translateY(-2px);box-shadow:0 8px 30px rgba(245,124,0,.35);color:#fff}
        .btn-login:active{transform:translateY(0)}
        .btn-login i{color:#fff}

        /* ===== ALERTS ===== */
        .alert-error{background:#fdf0f0;border:1.5px solid #f5c6cb;border-radius:10px;padding:12px 16px;font-size:.86rem;color:#842029;display:flex;align-items:center;gap:10px;margin-bottom:20px}
        .alert-error i{color:#842029}
        .alert-success{background:#f0fdf4;border:1.5px solid #b7dfb8;border-radius:10px;padding:12px 16px;font-size:.86rem;color:#155724;display:flex;align-items:center;gap:10px;margin-bottom:20px}
        .alert-success i{color:#155724}

        /* ===== FOOTER ===== */
        .login-footer{text-align:center;margin-top:22px;font-size:.78rem;color:var(--text-muted)}
        .login-footer a{color:var(--green);text-decoration:none;font-weight:600;transition:color .2s}
        .login-footer a:hover{color:var(--green-dark);text-decoration:underline}
        .login-footer .sep{color:var(--border);margin:0 6px}

        /* ===== RESPONSIVE ===== */
        @media(max-width:480px){
            .login-card{padding:32px 22px 28px}
            .logo-wrap img{width:90px;height:90px}
            .logo-title{font-size:1.2rem}
            .btn-login{padding:12px;font-size:.9rem}
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar"></div>

<!-- Login Wrapper -->
<div class="login-wrapper">
    <div class="login-card">

        <!-- Corner Decorations -->
        <span class="corner corner-tl">🌿</span>
        <span class="corner corner-br">🌿</span>

        <!-- Logo -->
        <div class="logo-area">
            <div class="logo-wrap">
                <img src="{{ asset('website_assets/image/logo.png') }}" alt="FreshNonVeg Logo">
            </div>
            <div class="logo-title"><span class="brand-fresh">Fresh</span><span class="brand-non">Non</span><span class="brand-veg">Veg</span></div>
            <div class="logo-sub">Admin Panel</div>
        </div>

        <!-- Error Alert -->
        @if($errors->any())
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Success Alert -->
        @if(session('success'))
        <div class="alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Divider -->
        <div class="divider">
            <span>Sign in to continue</span>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope-fill"></i>
                    </span>
                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="admin@freshnonveg.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input
                        type="password"
                        name="password"
                        id="passwordInput"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button class="pass-toggle" type="button" id="togglePass">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Remember + Forgot -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <a href="{{ route('admin.forgot.password') }}" class="forgot-link">
                    <i class="bi bi-question-circle me-1"></i>Forgot Password?
                </a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Admin Panel
            </button>
        </form>

        <!-- Footer -->
        <div class="login-footer">
            <a href="{{ route('home') }}">
                <i class="bi bi-arrow-left me-1"></i>Back to Website
            </a>
            <span class="sep">·</span>
            © {{ date('Y') }} FreshNonVeg
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password show/hide toggle
    document.getElementById('togglePass').addEventListener('click', function() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>
