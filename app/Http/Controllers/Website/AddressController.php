<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\UserAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function __construct(private UserAddressService $addresses) {}

    public function index(Request $request): View
    {
        $user = $this->user();
        $editing = $request->filled('edit') ? $this->addresses->find($user, (int) $request->query('edit')) : null;

        return view('Website.Dashboard.addresses', [
            'addresses' => $this->addresses->list($user),
            'editing' => $editing,
            'maxAddresses' => UserAddressService::MAX_ADDRESSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->addresses->rules());
        $this->addresses->create($this->user(), $data);

        return redirect()->route('dashboard.addresses')->with('success', 'Address save ho gaya.');
    }

    public function update(Request $request, UserAddress $address): RedirectResponse
    {
        $data = $request->validate($this->addresses->rules());
        $this->addresses->update($this->user(), $address, $data);

        return redirect()->route('dashboard.addresses')->with('success', 'Address update ho gaya.');
    }

    public function destroy(UserAddress $address): RedirectResponse
    {
        $this->addresses->delete($this->user(), $address);

        return redirect()->route('dashboard.addresses')->with('success', 'Address delete ho gaya.');
    }

    public function makeDefault(UserAddress $address): RedirectResponse
    {
        $this->addresses->makeDefault($this->user(), $address);

        return redirect()->route('dashboard.addresses')->with('success', 'Default address set ho gaya.');
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
