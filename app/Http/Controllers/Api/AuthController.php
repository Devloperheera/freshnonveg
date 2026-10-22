<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(private UserAuthService $authService) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'fcm_token' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'max:30'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_brand' => ['nullable', 'string', 'max:80'],
            'device_model' => ['nullable', 'string', 'max:80'],
            'os_version' => ['nullable', 'string', 'max:40'],
            'app_version' => ['nullable', 'string', 'max:30'],
            'app_build_number' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:20'],
        ]);

        $data['phone'] = $this->authService->normalizePhone($data['phone']);

        $user = $this->authService->register($data, 'app', $request);
        $token = $this->authService->issueApiToken($user, $request->input('device_name', 'mobile-app'));

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'fcm_token' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'max:30'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_brand' => ['nullable', 'string', 'max:80'],
            'device_model' => ['nullable', 'string', 'max:80'],
            'os_version' => ['nullable', 'string', 'max:40'],
            'app_version' => ['nullable', 'string', 'max:30'],
            'app_build_number' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $this->authService->attemptPasswordLogin(
            $data['login'],
            $data['password'],
            $request,
            'app'
        );

        $token = $this->authService->issueApiToken($user, $request->input('device_name', 'mobile-app'));

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'in:login,reset'],
        ]);

        $purpose = $data['purpose'] ?? 'login';
        $result = $purpose === 'reset'
            ? $this->authService->sendResetOtp($data['login'])
            : $this->authService->sendLoginOtp($data['login']);

        $message = $result['channel'] === 'phone'
            ? 'OTP sent to your phone number.'
            : 'OTP sent to your registered email. Please check inbox and spam.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'channel' => $result['channel'],
            'login' => $result['login'],
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
            'fcm_token' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'max:30'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $this->authService->verifyLoginOtp($data['login'], $data['otp'], $request, 'app');
        $token = $this->authService->issueApiToken($user, $request->input('device_name', 'mobile-app'));

        return response()->json([
            'success' => true,
            'message' => 'OTP login successful.',
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function sendForgotOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->authService->sendResetOtp($data['login']);

        return response()->json([
            'success' => true,
            'message' => 'Password reset OTP sent.',
            'channel' => $result['channel'],
            'login' => $result['login'],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->authService->resetPasswordWithOtp($data['login'], $data['otp'], $data['password']);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function google(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')],
            'fcm_token' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'max:30'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_brand' => ['nullable', 'string', 'max:80'],
            'device_model' => ['nullable', 'string', 'max:80'],
            'os_version' => ['nullable', 'string', 'max:40'],
            'app_version' => ['nullable', 'string', 'max:30'],
            'app_build_number' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:20'],
        ]);

        $payload = $this->authService->verifyGoogleIdToken($data['id_token']);

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google token.',
            ], 422);
        }

        $result = $this->authService->resolveGoogleUser($payload, $request, 'app');
        /** @var User $user */
        $user = $result['user'];

        if ($result['needs_phone']) {
            if (empty($data['phone'])) {
                return response()->json([
                    'success' => false,
                    'needs_phone' => true,
                    'message' => 'Phone number required to complete registration.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ], 422);
            }

            $phone = $this->authService->normalizePhone($data['phone']);
            $user = $this->authService->completeGooglePhone($user, $phone, $request, 'app');
        }

        $token = $this->authService->issueApiToken($user, $request->input('device_name', 'mobile-app'));

        return response()->json([
            'success' => true,
            'message' => 'Google login successful.',
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function completeProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $phone = $this->authService->normalizePhone($data['phone']);
        $user = $this->authService->completeGooglePhone($user, $phone, $request, 'app');

        if (isset($data['name'])) {
            $user->update(['name' => $data['name']]);
        }

        $user->update(collect($data)->only(['address', 'latitude', 'longitude'])->filter()->all());

        return response()->json([
            'success' => true,
            'message' => 'Profile completed.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),
        ]);
    }

    public function showProfile(Request $request): JsonResponse
    {
        return $this->me($request);
    }

    public function updateProfile(Request $request): JsonResponse
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
            'message' => 'Profile updated successfully.',
            'user' => $this->userPayload($user->fresh()),
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
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $user->update(['password' => $data['password']]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function updateDevice(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authService->applyDeviceMeta($user, $request, true);
        $user->forceFill(['last_active_at' => now()])->save();

        return response()->json([
            'success' => true,
            'message' => 'Device info updated.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->user()->currentAccessToken()?->delete();
        $this->authService->recordLogout($user);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image' => $user->profile_image,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'address' => $user->address,
            'status' => $user->status,
            'registration_source' => $user->registration_source,
            'login_count' => $user->login_count,
            'last_login_at' => $user->last_login_at,
            'web_last_login_at' => $user->web_last_login_at,
            'app_installed_at' => $user->app_installed_at,
            'is_app_installed' => $user->is_app_installed,
            'created_at' => $user->created_at,
        ];
    }
}
