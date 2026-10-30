<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountDeletionService;
use App\Services\UserAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DeleteAccountController extends Controller
{
    public function __construct(
        private AccountDeletionService $accountDeletion,
        private UserAuthService $authService,
    ) {}

    public function show(Request $request): View
    {
        $loginUrl = route('login', ['redirect' => $request->url()]);

        return view('Website.delete-account', [
            'user' => $request->user(),
            'loginUrl' => $loginUrl,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $rules = [
            'confirm' => ['accepted'],
            'confirm_phrase' => ['required', 'string', 'in:DELETE MY ACCOUNT'],
        ];

        if ($user->password) {
            $rules['current_password'] = ['required', 'string'];
        }

        $data = $request->validate($rules, [
            'confirm.accepted' => 'Account delete karne ke liye checkbox tick karein.',
            'confirm_phrase.in' => 'Neeche exactly likhein: DELETE MY ACCOUNT',
            'current_password.required' => 'Apna password enter karein.',
        ]);

        if ($user->password && ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password galat hai.']);
        }

        $this->accountDeletion->deleteAccount($user);
        $this->authService->recordLogout($user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Aapka account delete ho gaya hai. / Your account has been deleted.');
    }
}
