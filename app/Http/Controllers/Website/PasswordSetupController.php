<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * OTP se login ke baad: jinka password nahi hai unse ek baar password banwao,
 * taaki agli baar OTP ki jagah password se login kar sakein.
 */
class PasswordSetupController extends Controller
{
    public const MIN_LENGTH = 8;

    public function show(): View|RedirectResponse
    {
        if (filled($this->user()->password)) {
            return redirect()->intended(route('home'));
        }

        return view('Website.Auth.set-password', [
            'user' => $this->user(),
            'minLength' => self::MIN_LENGTH,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->user();
        if (filled($user->password)) {
            return redirect()->intended(route('home'));
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:'.self::MIN_LENGTH, 'max:100', 'confirmed'],
        ], [
            'password.min' => 'Password kam se kam '.self::MIN_LENGTH.' akshar ka hona chahiye.',
            'password.confirmed' => 'Dono password same nahi hain.',
        ]);

        $user->update(['password' => $data['password']]);

        return redirect()->intended(route('home'))
            ->with('success', 'Password ban gaya! Agli baar mobile/email + password se login karein — OTP ki zarurat nahi.');
    }

    public function skip(Request $request): RedirectResponse
    {
        $request->session()->put('password_setup_skipped', true);

        return redirect()->intended(route('home'))
            ->with('success', 'Theek hai! Password baad me Profile settings se bana sakte hain.');
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
