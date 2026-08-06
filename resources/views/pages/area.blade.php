@extends('layouts.public')

@section('title', 'Area Layanan Lanyard Jogja')
@section('meta_description', 'Lanyard Jogja melayani kebutuhan lanyard custom di Kota Jogja, Sleman, Bantul, Kulon Progo, Gunungkidul, dan pengiriman ke seluruh Indonesia.')
@section('seo_og_image', '/uploads/area-info-bg.webp')

@push('head')
  <link rel="stylesheet" href="/css/support-pages.css?v={{ file_exists(public_path('css/support-pages.css')) ? filemtime(public_path('css/support-pages.css')) : 1 }}">
@endpush

@section('content')
@php
  $primaryAreas = ['Kota Yogyakarta', 'Sleman', 'Bantul', 'Kulon Progo', 'Gunungkidul'];
  $whatsappUrl = 'https://wa.me/6282210200700?text='.rawurlencode('Halo Lanyard Jogja, saya mau konsultasi lanyard custom untuk area saya.');
@endphp
<div class="support-page area-page">
  <section class="support-hero support-hero--area" aria-labelledby="area-title">
    <div class="support-shell support-hero__grid">
      <div>
        <p class="support-eyebrow">Area Layanan</p>
        <h1 id="area-title">Dari Jogja untuk Kebutuhan Lanyard di Seluruh Indonesia</h1>
        <p class="support-lead">Konsultasi dari mana saja, produksi terarah, lalu pesanan dikirim ke alamat yang telah Anda konfirmasi.</p>
        <a class="support-button support-button--primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" data-button-motion><span data-button-label>Konsultasi Area Anda</span></a>
      </div>
      <div class="area-page__coverage-note" aria-label="Cakupan layanan Lanyard Jogja">
        <svg class="area-page__coverage-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.6 2.4a1 1 0 0 0-1.05-.23L3.2 8.65a1 1 0 0 0 .06 1.88l7.42 2.64l2.64 7.42a1 1 0 0 0 .94.66h.04a1 1 0 0 0 .92-.73l6.61-17.06a1 1 0 0 0-.23-1.06M14.1 17.3l-1.84-5.18a1 1 0 0 0-.6-.6L6.48 9.68l12.52-4.74z"/></svg>
        <strong>Berbasis di Jogja</strong><p>Melayani kebutuhan lokal dan pengiriman nasional.</p>
      </div>
    </div>
  </section>

  <section class="area-page__local" aria-labelledby="area-local-title">
    <div class="support-shell">
      <header class="support-section-heading"><p class="support-eyebrow">Prioritas Lokal</p><h2 id="area-local-title">Area Utama di Daerah Istimewa Yogyakarta</h2></header>
      <ol class="area-page__list">
        @foreach($primaryAreas as $area)
          <li><span>0{{ $loop->iteration }}</span><strong>{{ $area }}</strong><p>Konsultasi dan pengiriman kebutuhan lanyard custom.</p></li>
        @endforeach
      </ol>
    </div>
  </section>

  @if($cityPages->isNotEmpty())
    <section class="area-page__cities" aria-labelledby="area-cities-title">
      <div class="support-shell">
        <header class="support-section-heading"><p class="support-eyebrow">Informasi Per Kota</p><h2 id="area-cities-title">Temukan Layanan yang Lebih Spesifik</h2></header>
        <div class="area-page__cities-grid">
          @foreach($cityPages as $cityPage)
            <a href="{{ route('area.show', $cityPage->slug) }}"><span>{{ $cityPage->title }}</span><span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
          @endforeach
        </div>
      </div>
    </section>
  @endif
</div>
@include('components.footer')
@push('schema')@include('components.schema.area')@endpush
@endsection
