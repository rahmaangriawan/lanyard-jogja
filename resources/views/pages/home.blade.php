@extends('layouts.public')

@php
    $homeSeoSettings = \App\Support\HomePageCache::remember('home_seo_settings', function () {
        return \App\Models\Setting::whereIn('key', ['seo_meta_title', 'seo_meta_description'])->pluck('value', 'key')->toArray();
    });
    $homeTitle = $homeSeoSettings['seo_meta_title'] ?? 'Lanyard Custom Jogja Premium';
    $homeDesc = $homeSeoSettings['seo_meta_description'] ?? 'Lanyard Jogja melayani cetak tali lanyard custom berkualitas tinggi dengan harga terjangkau di Jogja.';
@endphp
@section('title', $homeTitle)
@section('meta_description', $homeDesc)
@section('seo_canonical', rtrim(config('app.url', url('/')), '/') . '/')
@section('seo_og_image', '/uploads/lanyard-jogja-logo.webp')

@push('head')
@php
    $homeCssPath = public_path('build/public/home.min.css');
    $homeCssVersion = file_exists($homeCssPath) ? filemtime($homeCssPath) : 1;
    $homeJsPath = public_path('build/public/home.min.js');
    $homeJsVersion = file_exists($homeJsPath) ? filemtime($homeJsPath) : 1;
@endphp
<link rel="stylesheet" href="/build/public/home.min.css?v={{ $homeCssVersion }}">
<link rel="stylesheet" href="/css/home-quotation-calculator.css?v={{ filemtime(public_path('css/home-quotation-calculator.css')) }}">
@endpush

@push('schema')
@include('components.schema.home')
@endpush

@section('content')
  <div class="homepage-content">
    @include('components.hero')
    @include('components.home-category-strip')
    @include('components.home-text-marquee')
    @include('components.home-advantages')
    @include('components.home-product-fan')
    @include('components.home-price-packages')
    @include('components.home-quotation-calculator')
    @include('components.home-order-process')
    @include('components.testimonials')
    @include('components.blog-preview')
    @include('components.cta')
  </div>
  @include('components.footer')
@endsection

@section('scripts')
  <script src="/build/public/home.min.js?v={{ $homeJsVersion }}" defer></script>
  <script src="/js/home-quotation-calculator.js?v={{ filemtime(public_path('js/home-quotation-calculator.js')) }}" defer></script>
@endsection
