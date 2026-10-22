<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAuthService;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(private UserAuthService $authService) {}

    public function showLogin(Request $request): View
    {
        AuthRedirect::captureIntended($request);

        return view('Website.Auth.login');
    }

    public function showRegister(Request $request): View
    {
        AuthRedirect::captureIntended($request);

        return view('Website.Auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->authService->attemptPasswordLogin(
            $data['login'],
            $data['password'],
            $request,
            'web'
        );

        $this->authService->webLogin($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Welcome back, '.$user->name.'!');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['phone'] = $this->authService->normalizePhone($data['phone']);

        $user = $this->authService->register($data, 'web', $request);
        $this->authService->webLogin($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Registration successful!');
    }

    public function redirectGoogle(Request $request): RedirectResponse
    {
        AuthRedirect::captureIntended($request);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $result = $this->authService->resolveGoogleUser([
            'sub' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
            'picture' => $googleUser->getAvatar(),
        ], $request, 'web');

        /** @var User $user */
        $user = $result['user'];

        if ($result['needs_phone']) {
            $request->session()->put('google_complete_user_id', $user->id);
            $request->session()->put('google_complete_name', $user->name);
            $request->session()->put('google_complete_email', $user->email);

            return redirect()->route('auth.google.phone');
        }

        $this->authService->webLogin($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Logged in with Google!');
    }

    public function showGooglePhoneForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('google_complete_user_id')) {
            return redirect()->route('login');
        }

        return view('Website.Auth.google-phone', [
            'name' => $request->session()->get('google_complete_name'),
            'email' => $request->session()->get('google_complete_email'),
        ]);
    }

    public function completeGooglePhone(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('google_complete_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
        ]);

        $user = User::query()->findOrFail($userId);
        $phone = $this->authService->normalizePhone($data['phone']);

        $user = $this->authService->completeGooglePhone($user, $phone, $request, 'web');

        $request->session()->forget(['google_complete_user_id', 'google_complete_name', 'google_complete_email']);

        $this->authService->webLogin($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Registration completed!');
    }

    public function sendLoginOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        try {
            $result = $this->authService->sendLoginOtp($data['login']);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('login_mode', 'otp');
        }

        $message = $result['channel'] === 'phone'
            ? 'OTP aapke phone number par bhej diya gaya hai.'
            : 'OTP aapke registered email par bhej diya gaya hai. Inbox aur Spam folder check karein.';

        return back()
            ->withInput(['login' => $result['login']])
            ->with('otp_sent', true)
            ->with('login_mode', 'otp')
            ->with('success', $message);
    }

    public function verifyLoginOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        try {
            $user = $this->authService->verifyLoginOtp($data['login'], $data['otp'], $request, 'web');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('otp_sent', true)
                ->with('login_mode', 'otp');
        }

        $this->authService->webLogin($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'OTP se login successful!');
    }

    public function showForgotPassword(): View
    {
        return view('Website.Auth.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->authService->sendResetOtp($data['login']);

        $message = $result['channel'] === 'phone'
            ? 'Reset OTP phone par bhej diya gaya hai.'
            : 'Reset OTP aapke registered email par bhej diya gaya hai. Inbox aur Spam folder check karein.';

        return redirect()
            ->route('password.reset.form', ['login' => $result['login']])
            ->with('success', $message);
    }

    public function showResetPasswordForm(Request $request): View|RedirectResponse
    {
        $login = $request->query('login', old('login'));

        if (! $login) {
            return redirect()->route('password.forgot');
        }

        return view('Website.Auth.reset-password', ['login' => $login]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->authService->resetPasswordWithOtp($data['login'], $data['otp'], $data['password']);

        return redirect()->route('login')->with('success', 'Password reset ho gaya. Ab login karein.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if ($user instanceof User) {
            $this->authService->recordLogout($user);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
