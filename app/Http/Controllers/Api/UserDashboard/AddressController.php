<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\UserAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private UserAddressService $addresses) {}

    public function index(Request $request): JsonResponse
    {
        return $this->listResponse($this->user($request));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $address = $this->addresses->create($user, $request->validate($this->addresses->rules()));

        return $this->listResponse($user, 'Address save ho gaya.', ['address' => $address->toApi()], 201);
    }

    public function update(Request $request, UserAddress $address): JsonResponse
    {
        $user = $this->user($request);
        $address = $this->addresses->update($user, $address, $request->validate($this->addresses->rules()));

        return $this->listResponse($user, 'Address update ho gaya.', ['address' => $address->toApi()]);
    }

    public function destroy(Request $request, UserAddress $address): JsonResponse
    {
        $user = $this->user($request);
        $this->addresses->delete($user, $address);

        return $this->listResponse($user, 'Address delete ho gaya.');
    }

    public function makeDefault(Request $request, UserAddress $address): JsonResponse
    {
        $user = $this->user($request);
        $address = $this->addresses->makeDefault($user, $address);

        return $this->listResponse($user, 'Default address set ho gaya.', ['address' => $address->toApi()]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function listResponse(User $user, ?string $message = null, array $extra = [], int $status = 200): JsonResponse
    {
        return response()->json(array_filter([
            'success' => true,
            'message' => $message,
            'data' => [
                ...$extra,
                'addresses' => $this->addresses->list($user)->map(fn (UserAddress $a) => $a->toApi())->values()->all(),
                'max_addresses' => UserAddressService::MAX_ADDRESSES,
            ],
        ], fn ($v) => $v !== null), $status);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
