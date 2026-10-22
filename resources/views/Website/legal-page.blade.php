@extends('Website.Layout.master')

@section('title', ($pageContent->meta_title ?: $pageContent->title).' — FreshNonVeg')
@section('meta_title', $pageContent->meta_title ?: $pageContent->title)
@section('meta_description', $pageContent->meta_description ?: Str::limit(strip_tags($pageContent->content), 160))

@section('custom_css')
<style>
.legal-page-wrap { max-width: 860px; margin: 0 auto; }
.legal-page-card {
    background: #fff; border: 3px solid var(--dark); border-radius: 20px;
    box-shadow: 6px 6px 0 var(--dark); padding: 28px 24px;
}
.legal-page-body { line-height: 1.75; color: #374151; }
.legal-page-body h1, .legal-page-body h2, .legal-page-body h3 { font-weight: 800; margin-top: 1.25rem; }
.legal-page-body p { margin-bottom: 1rem; }
.legal-page-body ul, .legal-page-body ol { margin-bottom: 1rem; padding-left: 1.25rem; }
</style>
@endsection

@section('content')
<section class="py-5">
    <div class="container legal-page-wrap">
        <p class="mb-2"><a href="{{ route('home') }}" class="text-decoration-none fw-semibold">← Home</a></p>
        <div class="legal-page-card rv">
            <h1 class="t mb-4">{{ $pageContent->title }}</h1>
            <div class="legal-page-body">
                {!! \App\Support\SafeHtml::display($pageContent->content) !!}
            </div>
        </div>
    </div>
</section>
@endsection
