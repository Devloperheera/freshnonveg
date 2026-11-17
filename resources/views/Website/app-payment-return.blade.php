@extends('Website.Layout.master')

@section('title', 'Payment — FreshNonVeg App')

@section('content')
<section class="py-5">
    <div class="container text-center" style="max-width:480px">
        <div class="fcard p-4">
            @if($isPaid)
                <div class="text-success mb-2" style="font-size:2.5rem">✓</div>
                <h1 class="h4 mb-2">Payment successful</h1>
                <p class="small text-muted mb-3">Order <strong>{{ $orderNumber }}</strong> place ho gaya. FreshNonVeg app me wapas jayein — order status refresh ho jayega.</p>
            @else
                <div class="text-warning mb-2" style="font-size:2.5rem">!</div>
                <h1 class="h4 mb-2">Payment incomplete</h1>
                <p class="small text-muted mb-3">Order <strong>{{ $orderNumber }}</strong>. App me dubara payment try karein.</p>
            @endif
            <a href="{{ $deepLink }}" class="btn btn-fun btn-red">Open FreshNonVeg App</a>
            <p class="small text-muted mt-3 mb-0">Button kaam na kare to app manually khol ke Orders dekhein.</p>
        </div>
    </div>
</section>
@endsection