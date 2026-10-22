@extends('Website.Layout.master')

@section('title', 'Bill '.$order->order_number.' — FreshNonVeg')

@section('content')
<style>
.bill-page{padding:28px 16px 48px}
.bill-actions{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:0 auto 16px;max-width:420px}
@media print{
    #siteHeader,footer,.mobile-bottom-nav,.wa,.bill-actions,.commerce-toast,#toast-container{display:none!important}
    body{padding-top:0!important;background:#fff!important}
    .bill-page{padding:0}
}
</style>
<section class="bill-page">
    <div class="bill-actions">
        <button type="button" class="btn cart-btn-outline" onclick="window.print()">Print bill</button>
        <a href="{{ route('dashboard.orders') }}" class="btn cart-btn-outline">My orders</a>
    </div>
    @include('Website.partials.food-bill', ['order' => $order])
</section>
@endsection
