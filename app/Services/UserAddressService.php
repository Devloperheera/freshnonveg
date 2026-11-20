<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAddressService
{
    public const MAX_ADDRESSES = 10;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:20'],
            'pincode' => ['required', 'regex:/^[0-9]{6}$/'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'post_office' => ['nullable', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:2000'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return Collection<int, UserAddress>
     */
    public function list(User $user): Collection
    {
        return $user->addresses()->get();
    }

    public function find(User $user, int $id): ?UserAddress
    {
        return UserAddress::query()->where('user_id', $user->id)->whereKey($id)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): UserAddress
    {
        if (UserAddress::query()->where('user_id', $user->id)->count() >= self::MAX_ADDRESSES) {
            throw ValidationException::withMessages([
                'address' => 'Maximum '.self::MAX_ADDRESSES.' addresses save ho sakte hain. Koi purana address delete karein.',
            ]);
        }

        return DB::transaction(function () use ($user, $data) {
            $isFirst = ! UserAddress::query()->where('user_id', $user->id)->exists();
            $makeDefault = $isFirst || (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                UserAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
            }

            return UserAddress::query()->create([
                ...$this->clean($data),
                'user_id' => $user->id,
                'is_default' => $makeDefault,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, UserAddress $address, array $data): UserAddress
    {
        $this->assertOwner($user, $address);

        return DB::transaction(function () use ($user, $address, $data) {
            if ((bool) ($data['is_default'] ?? false)) {
                UserAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
                $address->is_default = true;
            }
            $address->fill($this->clean($data))->save();

            return $address->refresh();
        });
    }

    public function delete(User $user, UserAddress $address): void
    {
        $this->assertOwner($user, $address);

        DB::transaction(function () use ($user, $address) {
            $wasDefault = (bool) $address->is_default;
            $address->delete();

            if ($wasDefault) {
                UserAddress::query()->where('user_id', $user->id)->latest('updated_at')->first()?->update(['is_default' => true]);
            }
        });
    }

    public function makeDefault(User $user, UserAddress $address): UserAddress
    {
        $this->assertOwner($user, $address);

        DB::transaction(function () use ($user, $address) {
            UserAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return $address->refresh();
    }

    /**
     * Checkout ke baad: naya address ho to save karo (same pincode + address dobara save nahi hota).
     *
     * @param  array<string, mixed>  $checkout  validated checkout data
     */
    public function rememberFromCheckout(User $user, array $checkout): ?UserAddress
    {
        $data = [
            'label' => $checkout['address_label'] ?? 'Home',
            'phone' => $checkout['phone'] ?? '',
            'pincode' => $checkout['pincode'] ?? '',
            'state' => $checkout['state'] ?? '',
            'city' => $checkout['city'] ?? '',
            'post_office' => $checkout['post_office'] ?? null,
            'address' => $checkout['delivery_address'] ?? '',
        ];

        $existing = UserAddress::query()
            ->where('user_id', $user->id)
            ->where('pincode', $data['pincode'])
            ->get()
            ->first(fn (UserAddress $a) => mb_strtolower(trim($a->address)) === mb_strtolower(trim((string) $data['address'])));

        if ($existing) {
            $existing->update(['phone' => $data['phone']]);

            return $existing;
        }

        try {
            return $this->create($user, $data);
        } catch (ValidationException) {
            return null; // limit poori — order fir bhi place ho
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function clean(array $data): array
    {
        $label = trim((string) ($data['label'] ?? ''));

        return array_filter([
            'label' => $label !== '' ? mb_substr($label, 0, 30) : 'Home',
            'phone' => isset($data['phone']) ? trim((string) $data['phone']) : null,
            'pincode' => isset($data['pincode']) ? trim((string) $data['pincode']) : null,
            'state' => isset($data['state']) ? trim((string) $data['state']) : null,
            'city' => isset($data['city']) ? trim((string) $data['city']) : null,
            'post_office' => isset($data['post_office']) ? (trim((string) $data['post_office']) ?: null) : null,
            'address' => isset($data['address']) ? trim((string) $data['address']) : null,
        ], fn ($v, $k) => $v !== null || $k === 'post_office', ARRAY_FILTER_USE_BOTH);
    }

    private function assertOwner(User $user, UserAddress $address): void
    {
        abort_unless((int) $address->user_id === (int) $user->id, 404);
    }
}
