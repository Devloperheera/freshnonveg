<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private UserAuthService $authService) {}

    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('Website.profile', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $data['phone'] = $this->authService->normalizePhone($data['phone']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'],
            'address' => $data['address'] ?: null,
        ]);

        return back()->with('success', 'Profile update ho gaya! / प्रोफाइल सेव हो गई।');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($user->password) {
            $rules['current_password'] = ['required', 'string'];
        }

        $data = $request->validate($rules);

        if ($user->password && ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Purana password galat hai / वर्तमान पासवर्ड गलत है।']);
        }

        $user->update(['password' => $data['password']]);

        return back()->with('success', 'Password change ho gaya! / पासवर्ड बदल दिया गया।');
    }
}
