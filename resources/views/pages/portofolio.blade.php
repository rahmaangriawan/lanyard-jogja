@extends('layouts.public')

@section('title', 'Portofolio Lanyard Custom')
@section('meta_description', 'Lihat pilihan karya lanyard custom dari Lanyard Jogja untuk kebutuhan kantor, event, sekolah, dan komunitas.')

@push('head')
  <link rel="stylesheet" href="/css/support-pages.css?v={{ file_exists(public_path('css/support-pages.css')) ? filemtime(public_path('css/support-pages.css')) : 1 }}">
@endpush

@section('content')
<div class="support-page portfolio-page">
  <section class="support-hero support-hero--portfolio" aria-labelledby="portfolio-title">
    <div class="support-shell support-hero__grid">
      <div>
        <p class="support-eyebrow">Karya Terpilih</p>
        <h1 id="portfolio-title">Berbagai Lanyard, Berbagai Karakter Brand</h1>
        <p class="support-lead">Kumpulan karya yang membantu Anda membayangkan material, warna, serta detail yang bisa dikembangkan untuk kebutuhan sendiri.</p>
      </div>
      <p class="portfolio-page__count"><strong>{{ $portfolios->count() }}</strong><span>Karya sedang ditampilkan</span></p>
    </div>
  </section>

  <section class="portfolio-board" aria-label="Daftar karya portofolio">
    <div class="support-shell">
      @if($portfolios->isNotEmpty())
        <div class="portfolio-board__grid">
          @foreach($portfolios as $portfolio)
            <article class="portfolio-board__item">
              <div class="portfolio-board__image">
                @if($portfolio->imageUrl)
                  <img src="{{ $portfolio->imageUrl }}" alt="{{ $portfolio->title }}" width="1200" height="900" loading="lazy" decoding="async">
                @else
                  <span aria-hidden="true">LJ</span>
                @endif
              </div>
              <div class="portfolio-board__body">
                <div>
                  @if($portfolio->logoUrl)
                    <img class="portfolio-board__logo" src="{{ $portfolio->logoUrl }}" alt="{{ $portfolio->logoText ?: 'Logo klien' }}" loading="lazy" decoding="async">
                  @elseif($portfolio->logoText)
                    <p class="portfolio-board__client">{{ $portfolio->logoText }}</p>
                  @endif
                  <h2>{{ $portfolio->title }}</h2>
                  @if($portfolio->description)<p>{{ $portfolio->description }}</p>@endif
                </div>
                @if($portfolio->link)
                  <a href="{{ $portfolio->link }}" target="_blank" rel="noopener noreferrer">Lihat referensi <span aria-hidden="true">→</span></a>
                @endif
              </div>
            </article>
          @endforeach
        </div>
      @else
        <div class="support-empty">
          <span class="material-symbols-outlined" aria-hidden="true">photo_library</span>
          <h2>Karya baru segera hadir</h2>
          <p>Ceritakan kebutuhan Anda—kami bantu menyusun arah visual yang paling pas.</p>
          <a class="support-button support-button--primary" href="https://wa.me/6282210200700?text={{ rawurlencode('Halo Lanyard Jogja, saya mau konsultasi lanyard custom.') }}" target="_blank" rel="noopener noreferrer" data-button-motion><span data-button-label>Mulai Konsultasi</span></a>
        </div>
      @endif
    </div>
  </section>
</div>
@include('components.footer')
@endsection
