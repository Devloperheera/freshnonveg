<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAuthService;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(private UserAuthService $authService) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => UserDashboardApi::user($user),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $data['phone'] = $this->authService->normalizePhone($data['phone']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'],
            'address' => ($data['address'] ?? null) ?: null,
            'latitude' => array_key_exists('latitude', $data) ? $data['latitude'] : $user->latitude,
            'longitude' => array_key_exists('longitude', $data) ? $data['longitude'] : $user->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile update ho gaya! / प्रोफाइल सेव हो गई।',
            'data' => UserDashboardApi::user($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($user->password) {
            $rules['current_password'] = ['required', 'string'];
        }

        $data = $request->validate($rules);

        if ($user->password && ! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Purana password galat hai / वर्तमान पासवर्ड गलत है।',
                'errors' => ['current_password' => ['Purana password galat hai / वर्तमान पासवर्ड गलत है।']],
            ], 422);
        }

        $user->update(['password' => $data['password']]);

        return response()->json([
            'success' => true,
            'message' => 'Password change ho gaya! / पासवर्ड बदल दिया गया।',
            'data' => UserDashboardApi::user($user->fresh()),
        ]);
    }
}
