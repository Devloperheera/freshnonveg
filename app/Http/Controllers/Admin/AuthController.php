<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('Admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::query()->where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        if ((int) $admin->status !== 1) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your admin account is inactive.']);
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showForgotPassword(): View
    {
        return view('Admin.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $admin = Admin::query()->where('email', $data['email'])->first();
        $otp = (string) random_int(100000, 999999);

        $request->session()->put('admin_reset_email', $admin?->email ?: $data['email']);
        $request->session()->put('admin_reset_otp', Hash::make($otp));
        $request->session()->put('admin_reset_otp_expires', now()->addMinutes(10)->timestamp);
        $request->session()->put('admin_otp_verified', false);

        if ($admin) {
            Mail::to($admin->email)->send(new AdminOtpMail($otp, $admin->name));
        }

        return redirect()->route('admin.verify.otp')
            ->with('success', 'Agar yeh email registered hai to OTP bhej diya gaya hai.');
    }

    public function showVerifyOtp(): View|RedirectResponse
    {
        if (! session('admin_reset_email')) {
            return redirect()->route('admin.forgot.password');
        }

        return view('Admin.verify-otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $expires = (int) session('admin_reset_otp_expires', 0);
        $hashedOtp = (string) session('admin_reset_otp', '');

        if ($expires < now()->timestamp || $hashedOtp === '' || ! Hash::check((string) $request->input('otp'), $hashedOtp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
        }

        $request->session()->put('admin_otp_verified', true);

        return redirect()->route('admin.reset.password');
    }

    public function showResetPassword(): View|RedirectResponse
    {
        if (! session('admin_reset_email') || ! session('admin_otp_verified')) {
            return redirect()->route('admin.forgot.password');
        }

        return view('Admin.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if (! session('admin_reset_email') || ! session('admin_otp_verified')) {
            return redirect()->route('admin.forgot.password');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = Admin::query()->where('email', session('admin_reset_email'))->first();

        if (! $admin) {
            return redirect()->route('admin.forgot.password');
        }

        $admin->update(['password' => Hash::make($data['password'])]);

        $request->session()->forget(['admin_reset_email', 'admin_reset_otp', 'admin_reset_otp_expires', 'admin_otp_verified']);

        return redirect()
            ->route('admin.login')
            ->with('success', 'Password updated successfully. You can log in now.');
    }
}
