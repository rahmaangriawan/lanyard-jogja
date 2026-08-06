@extends('layouts.public')

@section('title', 'Tentang Lanyard Jogja')
@section('meta_description', 'Kenali Lanyard Jogja, partner cetak lanyard custom untuk kantor, event, sekolah, dan komunitas.')
@section('seo_og_image', '/uploads/tentang-lanyard.webp')

@push('head')
  <link rel="stylesheet" href="/css/support-pages.css?v={{ file_exists(public_path('css/support-pages.css')) ? filemtime(public_path('css/support-pages.css')) : 1 }}">
@endpush

@section('content')
<div class="support-page about-page">
  <section class="about-page__hero" aria-labelledby="about-title">
    <div class="support-shell about-page__hero-grid">
      <div class="about-page__copy">
        <p class="support-eyebrow">Tentang Kami</p>
        <h1 id="about-title">Lanyard yang Membawa Identitas Anda Lebih Dekat</h1>
        <p class="support-lead">Lanyard Jogja membantu kantor, sekolah, event, dan komunitas menyiapkan identitas yang nyaman dipakai serta mudah diingat.</p>
        <a class="support-button support-button--primary" href="https://wa.me/6282210200700?text={{ rawurlencode('Halo Lanyard Jogja, saya mau konsultasi lanyard custom.') }}" target="_blank" rel="noopener noreferrer" data-button-motion><span data-button-label>Mulai Konsultasi</span></a>
      </div>
      <figure class="about-page__image"><img src="/uploads/tentang-lanyard-jogja.webp" alt="Ragam lanyard custom produksi Lanyard Jogja" width="1672" height="941" loading="eager" decoding="async"></figure>
    </div>
  </section>

  <section class="about-page__story" aria-labelledby="about-story-title">
    <div class="support-shell about-page__story-grid">
      <header class="support-section-heading"><p class="support-eyebrow">Cara Kami Bekerja</p><h2 id="about-story-title">Tidak Sekadar Cetak, Kami Menjaga Prosesnya</h2></header>
      <div class="about-page__story-copy"><p>Setiap pesanan dimulai dari memahami kebutuhan: siapa yang memakai, di acara apa, dan kesan seperti apa yang ingin dibawa brand Anda.</p><p>Dari pemilihan bahan sampai pengecekan sebelum kirim, kami menjaga detail agar hasil akhirnya terasa tepat ketika dipakai.</p></div>
    </div>
  </section>

  <section class="about-page__principles" aria-label="Nilai Lanyard Jogja">
    <div class="support-shell">
      <ol>
        <li><span>01</span><h2>Jelas sejak awal</h2><p>Spesifikasi, arahan desain, dan estimasi dibicarakan sebelum produksi.</p></li>
        <li><span>02</span><h2>Rapi di detail</h2><p>Warna, material, dan pengait diperiksa agar nyaman dipakai.</p></li>
        <li><span>03</span><h2>Siap mendampingi</h2><p>Tim membantu dari konsultasi sampai pesanan dikirim ke alamat Anda.</p></li>
      </ol>
    </div>
  </section>
</div>
@include('components.footer')
@endsection
