<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — FreshNonVeg Admin</title>
    <meta name="description" content="Set a new password for your FreshNonVeg admin account. Create a strong password to secure your account.">
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

        /* ===== CARD WRAPPER ===== */
        .card-wrap{width:100%;max-width:440px;padding:20px;z-index:1}
        .auth-card{background:#fff;border-radius:var(--radius);padding:44px 38px 36px;border:1.5px solid var(--border);box-shadow:var(--shadow);position:relative;text-align:center}
        .auth-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--brand-red),var(--brand-orange),var(--brand-green));border-radius:var(--radius) var(--radius) 0 0}

        /* ===== STEP BADGE ===== */
        .step-badge{background:var(--green-bg);border:1.5px solid var(--border);border-radius:50px;padding:6px 20px;font-size:.75rem;font-weight:700;color:var(--green);display:inline-block;margin-bottom:16px;text-transform:uppercase;letter-spacing:.5px}

        /* ===== ICON ===== */
        .icon-wrap{width:72px;height:72px;background:linear-gradient(135deg,var(--green-bg),#d4ead7);border-radius:20px;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 16px}

        /* ===== TYPOGRAPHY ===== */
        h4{font-weight:800;color:var(--green-dark);font-size:1.3rem;margin-bottom:4px}
        .sub{color:var(--text-muted);font-size:.88rem;margin-bottom:22px;line-height:1.6}

        /* ===== FORM ===== */
        .form-label{font-size:.8rem;font-weight:700;color:var(--text-dark);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px}
        .input-group-text{background:var(--green-bg);border:1.5px solid var(--border);border-right:none;color:var(--green);padding:0 14px}
        .form-control{border:1.5px solid var(--border);border-left:none;padding:11px 14px;font-size:.92rem;color:var(--text-dark);background:#fff;transition:border-color .25s ease}
        .form-control:focus{outline:none;box-shadow:none;border-color:var(--green);background:#fff}
        .input-group:focus-within .input-group-text{border-color:var(--green)}
        .pass-toggle{border:1.5px solid var(--border);border-left:none;background:var(--green-bg);color:var(--text-muted);cursor:pointer;padding:0 14px;transition:color .25s}
        .pass-toggle:hover{color:var(--green)}
        .input-group:focus-within .pass-toggle{border-color:var(--green)}

        /* ===== PASSWORD STRENGTH ===== */
        .strength-bar{height:4px;border-radius:4px;background:#e0e0e0;margin-top:8px;overflow:hidden}
        .strength-fill{height:100%;border-radius:4px;width:0%;transition:width .4s ease,background .4s ease}
        .strength-text{font-size:.75rem;color:var(--text-muted);margin-top:6px;transition:color .3s ease}

        /* ===== BUTTON ===== */
        .btn-main{width:100%;padding:13px;font-weight:700;border:none;border-radius:12px;color:#fff;background:linear-gradient(135deg,var(--brand-red),var(--brand-orange),var(--brand-green));font-size:.95rem;transition:all .3s ease;cursor:pointer;margin-top:4px}
        .btn-main:hover{opacity:.92;transform:translateY(-2px);box-shadow:0 8px 30px rgba(245,124,0,.35);color:#fff}
        .btn-main i{color:#fff}

        /* ===== ALERT ===== */
        .alert-err{background:#fdf0f0;border:1.5px solid #f5c6cb;border-radius:10px;padding:11px 14px;font-size:.86rem;color:#842029;display:flex;align-items:center;gap:10px;margin-bottom:20px;text-align:left}
        .alert-err i{color:#842029}

        /* ===== RESPONSIVE ===== */
        @media(max-width:480px){
            .auth-card{padding:32px 22px 28px}
            .icon-wrap{width:60px;height:60px;font-size:1.6rem}
            h4{font-size:1.1rem}
            .sub{font-size:.82rem}
            .btn-main{padding:12px;font-size:.88rem}
            .otp-input{width:44px;height:50px;font-size:1.3rem}
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar"></div>

<!-- Card Wrapper -->
<div class="card-wrap">
    <div class="auth-card">

        <!-- Step Badge -->
        <div class="step-badge">Step 3 of 3 · New Password</div>

        <!-- Icon -->
        <div class="icon-wrap">🛡️</div>

        <!-- Heading -->
        <h4>Set New Password</h4>
        <p class="sub">Choose a strong password for your admin account.</p>

        <!-- Error Alert -->
        @if($errors->any())
        <div class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('admin.reset.password.post') }}" class="text-start">
            @csrf

            <!-- New Password -->
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" id="newPass" class="form-control" placeholder="Min. 8 characters" required>
                    <button type="button" class="pass-toggle" onclick="togglePass('newPass','eye1')">
                        <i class="bi bi-eye" id="eye1"></i>
                    </button>
                </div>
                <!-- Strength Bar -->
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-text" id="strengthText">Enter a password</div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirmation" id="confirmPass" class="form-control" placeholder="Repeat password" required>
                    <button type="button" class="pass-toggle" onclick="togglePass('confirmPass','eye2')">
                        <i class="bi bi-eye" id="eye2"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-main">
                <i class="bi bi-check-circle me-2"></i>Reset Password
            </button>
        </form>

    </div>
</div>

<script>
    // ===== TOGGLE PASSWORD VISIBILITY =====
    function togglePass(id, iconId) {
        const el = document.getElementById(id);
        const ic = document.getElementById(iconId);
        el.type = el.type === 'password' ? 'text' : 'password';
        ic.className = el.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }

    // ===== PASSWORD STRENGTH METER =====
    document.getElementById('newPass').addEventListener('input', function() {
        const val = this.value;
        const fill = document.getElementById('strengthFill');
        const text = document.getElementById('strengthText');

        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { w: '0%',   c: '#e0e0e0', t: 'Enter a password' },
            { w: '20%',  c: '#dc3545', t: '⚠️ Very Weak' },
            { w: '40%',  c: '#fd7e14', t: '⚠️ Weak' },
            { w: '60%',  c: '#ffc107', t: '📌 Fair' },
            { w: '80%',  c: '#28a745', t: '✅ Good' },
            { w: '100%', c: '#28a745', t: '✅✅ Strong' },
        ];

        const index = Math.min(score, 5);
        fill.style.width = levels[index].w;
        fill.style.background = levels[index].c;
        text.textContent = levels[index].t;
        text.style.color = levels[index].c;
    });

    // ===== CONFIRM PASSWORD MATCH CHECK =====
    document.getElementById('confirmPass').addEventListener('input', function() {
        const newPass = document.getElementById('newPass').value;
        const confirmPass = this.value;
        const text = document.getElementById('strengthText');

        if (confirmPass.length > 0 && newPass !== confirmPass) {
            text.textContent = '❌ Passwords do not match';
            text.style.color = '#dc3545';
        } else if (confirmPass.length > 0 && newPass === confirmPass) {
            const fill = document.getElementById('strengthFill');
            text.textContent = '✅ Passwords match';
            text.style.color = '#28a745';
        }
    });
</script>

</body>
</html>
