@extends('Website.Dashboard.layout')

@section('title', 'My addresses — FreshNonVeg')
@section('dashboard_title', 'My addresses')

@section('dashboard')
@php
    $form = $editing;
    $labelValue = old('label', $form?->label ?? 'Home');
@endphp
<div class="ud-grid ud-grid-wide">
    <div class="ud-card">
        <div class="ud-card-head">
            <h2>{{ $form ? 'Address edit karein' : 'Naya address' }}</h2>
            @if($form)
                <a href="{{ route('dashboard.addresses') }}" class="small">Cancel</a>
            @endif
        </div>

        @if(! $form && $addresses->count() >= $maxAddresses)
            <p class="ud-row-sub">Maximum {{ $maxAddresses }} addresses save ho sakte hain. Naya add karne ke liye koi purana delete karein.</p>
        @else
            <form method="POST" action="{{ $form ? route('dashboard.addresses.update', $form) : route('dashboard.addresses.store') }}" class="ud-form">
                @csrf
                @if($form) @method('PUT') @endif

                <label class="form-label">Label</label>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @foreach(\App\Models\UserAddress::LABELS as $lbl)
                        <label class="checkout-addr-tag">
                            <input type="radio" name="label" value="{{ $lbl }}" @checked($labelValue === $lbl)>
                            <span>{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>

                <label class="form-label" for="addrPhone">Mobile / Phone *</label>
                <input type="text" name="phone" id="addrPhone" maxlength="20" required
                       class="form-control mb-1 @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $form?->phone ?? auth()->user()->phone) }}">
                @error('phone')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

                <div class="row g-2 mt-2">
                    <div class="col-sm-4">
                        <label class="form-label" for="addrPincode">Pincode *</label>
                        <input type="text" name="pincode" id="addrPincode" inputmode="numeric" maxlength="6" required
                               class="form-control @error('pincode') is-invalid @enderror"
                               value="{{ old('pincode', $form?->pincode) }}" placeholder="6-digit"
                               data-lookup-url="{{ route('checkout.pincode', ['pincode' => '__PINCODE__']) }}">
                        <div class="small mt-1" id="addrPinStatus"></div>
                        @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" for="addrState">State *</label>
                        <input type="text" name="state" id="addrState" maxlength="100" required
                               class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $form?->state) }}">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" for="addrCity">City *</label>
                        <input type="text" name="city" id="addrCity" maxlength="100" required
                               class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $form?->city) }}">
                    </div>
                </div>

                <label class="form-label mt-3" for="addrAddress">House / Street / Full address *</label>
                <textarea name="address" id="addrAddress" rows="2" maxlength="2000" required
                          class="form-control mb-1 @error('address') is-invalid @enderror"
                          placeholder="Flat no, building, street, landmark">{{ old('address', $form?->address) }}</textarea>
                @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <label class="d-flex align-items-center gap-2 my-3">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $form?->is_default))>
                    <span class="small fw-semibold">Default address banayein (checkout par pehle se chuna rahega)</span>
                </label>

                <button type="submit" class="ud-btn w-100">
                    <i class="bi bi-save"></i> {{ $form ? 'Update address' : 'Save address' }}
                </button>
            </form>
        @endif
    </div>

    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Saved addresses</h2>
            <span class="ud-row-sub">{{ $addresses->count() }} / {{ $maxAddresses }}</span>
        </div>

        @if($addresses->isEmpty())
            <div class="ud-empty">
                <i class="bi bi-geo-alt"></i>
                <p>Abhi koi address save nahi hai. Checkout par "Is address ko save karein" tick karein ya yahan add karein.</p>
            </div>
        @else
            <div class="ud-addr-grid">
                @foreach($addresses as $addr)
                    <div class="ud-addr {{ $addr->is_default ? 'is-default' : '' }}">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <strong>
                                <i class="bi {{ $addr->label === 'Work' ? 'bi-briefcase' : ($addr->label === 'Home' ? 'bi-house-door' : 'bi-geo-alt') }} me-1"></i>
                                {{ $addr->label }}
                            </strong>
                            @if($addr->is_default)
                                <span class="ud-badge ud-badge-ok">Default</span>
                            @endif
                        </div>
                        <div class="small">{{ $addr->fullLine() }}</div>
                        <div class="ud-row-sub"><i class="bi bi-telephone me-1"></i>{{ $addr->phone }}</div>
                        <div class="ud-addr-actions">
                            <a href="{{ route('dashboard.addresses', ['edit' => $addr->id]) }}" class="ud-btn ud-btn-ghost ud-btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @unless($addr->is_default)
                                <form method="POST" action="{{ route('dashboard.addresses.default', $addr) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="ud-btn ud-btn-ghost ud-btn-sm">Default</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('dashboard.addresses.destroy', $addr) }}" class="d-inline"
                                  onsubmit="return confirm('Ye address delete karein?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="ud-btn ud-btn-danger ud-btn-sm" aria-label="{{ $addr->label }} address delete karein">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var pin = document.getElementById('addrPincode');
    if (!pin) return;
    var status = document.getElementById('addrPinStatus');
    var state = document.getElementById('addrState');
    var city = document.getElementById('addrCity');
    pin.addEventListener('input', function () {
        var v = pin.value.replace(/\D/g, '').slice(0, 6);
        pin.value = v;
        status.textContent = '';
        status.className = 'small mt-1';
        if (v.length !== 6) return;
        status.textContent = 'Pincode check ho raha hai…';
        fetch(pin.dataset.lookupUrl.replace('__PINCODE__', v), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.state) state.value = d.state;
                if (d.city) city.value = d.city;
                if (d.delivery && d.delivery.deliverable === false) {
                    status.textContent = 'Is pincode par abhi delivery nahi hoti — address save ho jayega, lekin order place nahi hoga.';
                    status.className = 'small mt-1 text-danger';
                } else {
                    status.textContent = '✅ Delivery available';
                    status.className = 'small mt-1 text-success';
                }
            })
            .catch(function () {
                status.textContent = 'Pincode lookup fail — State/City khud bhar dein.';
                status.className = 'small mt-1 text-danger';
            });
    });
})();
</script>
@endpush
