@extends('Website.Layout.master')

@section('title', 'FreshNonVeg – Fresh Non Veg Delivered to Your Door')

@section('content')
    @include('Website.Components.herosection')
    @include('Website.Components.hotline')
    @include('Website.Components.category')
    @include('Website.Components.advantages')
    @include('Website.Components.products')
    @include('Website.Components.orderway')
    @include('Website.Components.statics')
    @include('Website.Components.testimonials')
    @include('Website.Components.faq')
    @include('Website.Components.cta')
@endsection
