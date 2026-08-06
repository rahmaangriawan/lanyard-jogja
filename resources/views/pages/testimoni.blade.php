@extends('layouts.public')

@section('title', 'Testimoni Pelanggan Lanyard Jogja')
@section('meta_description', 'Cerita pelanggan tentang pengalaman memesan lanyard custom bersama Lanyard Jogja.')

@push('head')
  <link rel="stylesheet" href="/css/support-pages.css?v={{ file_exists(public_path('css/support-pages.css')) ? filemtime(public_path('css/support-pages.css')) : 1 }}">
@endpush

@section('content')
<div class="support-page testimonial-page">
  <section class="support-hero support-hero--testimonials" aria-labelledby="testimonial-title">
    <div class="support-shell">
      <p class="support-eyebrow">Testimoni Pelanggan</p>
      <h1 id="testimonial-title">Cerita Mereka Setelah Memesan di Lanyard Jogja</h1>
      <p class="support-lead">Setiap cerita memberi kami alasan untuk terus menjaga hasil produksi dan pelayanan.</p>
    </div>
  </section>
  <section class="testimonial-wall" aria-label="Kumpulan testimoni pelanggan">
    <div class="support-shell">
      @if($testimonials->isNotEmpty())
        <div class="testimonial-wall__grid">
          @foreach($testimonials as $testimonial)
            <article class="testimonial-wall__item">
              <header>
                @if($testimonial->imageUrl)<img src="{{ $testimonial->imageUrl }}" alt="Foto {{ $testimonial->name }}" width="120" height="120" loading="lazy" decoding="async">@endif
                <div><h2>{{ $testimonial->name }}</h2>@if($testimonial->role)<p>{{ $testimonial->role }}</p>@endif</div>
              </header>
              <blockquote>{{ $testimonial->quote }}</blockquote>
              <span aria-hidden="true">”</span>
            </article>
          @endforeach
        </div>
      @else
        <div class="support-empty"><span class="material-symbols-outlined" aria-hidden="true">format_quote</span><h2>Cerita pelanggan segera hadir</h2><p>Kami sedang mengumpulkan pengalaman terbaik dari pelanggan Lanyard Jogja.</p></div>
      @endif
    </div>
  </section>
</div>
@include('components.footer')
@endsection
