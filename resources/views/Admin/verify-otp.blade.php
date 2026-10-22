<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP — FreshNonVeg Admin</title>
    <meta name="description" content="Verify your OTP to reset your FreshNonVeg admin account password.">
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
        .sub{color:var(--text-muted);font-size:.88rem;margin-bottom:6px}
        .email-badge{background:var(--green-bg);border:1.5px solid var(--border);border-radius:8px;padding:6px 16px;font-size:.82rem;color:var(--green);font-weight:600;display:inline-block;margin-bottom:20px}
        .email-badge i{color:var(--gold)}

        /* ===== OTP INPUTS ===== */
        .otp-inputs{display:flex;gap:10px;justify-content:center;margin:16px 0 24px}
        .otp-input{width:52px;height:58px;text-align:center;font-size:1.6rem;font-weight:800;color:var(--green);border:1.5px solid var(--border);border-radius:12px;background:var(--green-bg);transition:all .25s ease}
        .otp-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(7,91,43,.12);outline:none;background:#fff}
        .otp-input:focus-visible{outline:none}

        /* ===== BUTTON ===== */
        .btn-main{width:100%;padding:13px;font-weight:700;border:none;border-radius:12px;color:#fff;background:linear-gradient(135deg,var(--brand-red),var(--brand-orange),var(--brand-green));font-size:.95rem;transition:all .3s ease;cursor:pointer}
        .btn-main:hover{opacity:.92;transform:translateY(-2px);box-shadow:0 8px 30px rgba(245,124,0,.35);color:#fff}
        .btn-main i{color:#fff}

        /* ===== BACK LINK ===== */
        .back-link{display:block;text-align:center;margin-top:16px;font-size:.83rem;color:var(--text-muted);text-decoration:none;font-weight:500;transition:color .2s}
        .back-link:hover{color:var(--green);text-decoration:underline}
        .back-link i{color:var(--green)}

        /* ===== ALERTS ===== */
        .alert-err{background:#fdf0f0;border:1.5px solid #f5c6cb;border-radius:10px;padding:11px 14px;font-size:.86rem;color:#842029;display:flex;align-items:center;gap:10px;margin-bottom:20px;text-align:left}
        .alert-err i{color:#842029}
        .alert-suc{background:#f0fdf4;border:1.5px solid #b7dfb8;border-radius:10px;padding:11px 14px;font-size:.86rem;color:#155724;display:flex;align-items:center;gap:10px;margin-bottom:20px;text-align:left}
        .alert-suc i{color:#155724}

        /* ===== TIMER ===== */
        .timer-wrap{color:var(--text-muted);font-size:.83rem;margin-top:14px}
        .timer-wrap #countdown{font-weight:700;color:var(--gold-dark)}
        .timer-wrap #resendBtn{display:none;color:var(--green);font-weight:700;font-size:.83rem;cursor:pointer;border:none;background:none;text-decoration:underline;padding:0;transition:color .2s}
        .timer-wrap #resendBtn:hover{color:var(--gold)}

        /* ===== RESPONSIVE ===== */
        @media(max-width:480px){
            .auth-card{padding:32px 22px 28px}
            .icon-wrap{width:60px;height:60px;font-size:1.6rem}
            h4{font-size:1.1rem}
            .sub{font-size:.82rem}
            .btn-main{padding:12px;font-size:.88rem}
            .otp-input{width:44px;height:50px;font-size:1.3rem}
            .otp-inputs{gap:6px}
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
        <div class="step-badge">Step 2 of 3 · Verify OTP</div>

        <!-- Icon -->
        <div class="icon-wrap">📧</div>

        <!-- Heading -->
        <h4>Check Your Email</h4>
        <p class="sub">OTP sent to</p>
        <div class="email-badge">
            <i class="bi bi-envelope-check me-1"></i>{{ session('admin_reset_email') }}
        </div>

        <!-- Error Alert -->
        @if($errors->any())
        <div class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Success Alert -->
        @if(session('success'))
        <div class="alert-suc">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- OTP Form -->
        <form method="POST" action="{{ route('admin.verify.otp.post') }}" id="otpForm">
            @csrf
            <input type="hidden" name="otp" id="otpHidden">

            <!-- OTP Inputs -->
            <div class="otp-inputs">
                <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric" autofocus>
                <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric">
            </div>

            <!-- Verify Button -->
            <button type="submit" class="btn-main" id="verifyBtn">
                <i class="bi bi-shield-check me-2"></i>Verify OTP
            </button>
        </form>

        <!-- Timer -->
        <div class="timer-wrap">
            OTP expires in <span id="countdown">10:00</span>
            <br>
            <button id="resendBtn" onclick="window.location='{{ route('admin.forgot.password') }}'">Resend OTP</button>
        </div>

        <!-- Back Link -->
        <a href="{{ route('admin.forgot.password') }}" class="back-link">
            <i class="bi bi-arrow-left me-1"></i>Change Email
        </a>

    </div>
</div>

<script>
    // ===== OTP INPUT AUTO-JUMP =====
    const inputs = document.querySelectorAll('.otp-input');

    inputs.forEach((input, i) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
            if (input.value && i < 5) inputs[i + 1].focus();
            updateHidden();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && i > 0) {
                inputs[i - 1].focus();
            }
            if (e.key === 'ArrowLeft' && i > 0) {
                inputs[i - 1].focus();
            }
            if (e.key === 'ArrowRight' && i < 5) {
                inputs[i + 1].focus();
            }
        });

        // Paste support
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            if (digits) {
                inputs.forEach((inp, idx) => {
                    if (idx < digits.length) {
                        inp.value = digits[idx];
                    }
                });
                updateHidden();
                const nextIndex = Math.min(digits.length, 5);
                inputs[nextIndex].focus();
            }
        });
    });

    function updateHidden() {
        let otp = '';
        inputs.forEach(inp => otp += inp.value);
        document.getElementById('otpHidden').value = otp;
    }

    // Form validation
    document.getElementById('otpForm').addEventListener('submit', function(e) {
        updateHidden();
        const otp = document.getElementById('otpHidden').value;
        if (otp.length < 6) {
            e.preventDefault();
            alert('Please enter all 6 digits.');
        }
    });

    // ===== COUNTDOWN TIMER (10 minutes) =====
    let timeLeft = 600;
    const cd = document.getElementById('countdown');
    const resend = document.getElementById('resendBtn');

    const timer = setInterval(() => {
        timeLeft--;
        const m = String(Math.floor(timeLeft / 60)).padStart(2, '0');
        const s = String(timeLeft % 60).padStart(2, '0');
        cd.textContent = m + ':' + s;

        if (timeLeft <= 0) {
            clearInterval(timer);
            cd.textContent = 'Expired';
            cd.style.color = '#dc3545';
            resend.style.display = 'inline';
        }
    }, 1000);
</script>

</body>
</html>
