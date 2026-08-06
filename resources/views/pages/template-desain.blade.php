@extends('layouts.public')

@section('title', 'Template Desain Lanyard Custom')
@section('meta_description', 'Pilih arah visual lanyard custom untuk kantor, event, sekolah, komunitas, dan kebutuhan promosi bersama Lanyard Jogja.')

@push('head')
  <link rel="stylesheet" href="/css/support-pages.css?v={{ file_exists(public_path('css/support-pages.css')) ? filemtime(public_path('css/support-pages.css')) : 1 }}">
@endpush

@section('content')
@php
  $templateChoices = [
    ['title' => 'Corporate', 'tag' => 'Rapi & profesional', 'copy' => 'Palet tenang, logo jelas, dan detail yang mudah dibaca untuk tim kerja.', 'class' => 'is-corporate'],
    ['title' => 'Event', 'tag' => 'Berani & mudah dikenali', 'copy' => 'Identitas acara yang terasa hidup dari registrasi sampai panggung utama.', 'class' => 'is-event'],
    ['title' => 'Sekolah', 'tag' => 'Ceria & fungsional', 'copy' => 'Warna institusi dan informasi penting tersusun nyaman untuk dipakai harian.', 'class' => 'is-school'],
    ['title' => 'Komunitas', 'tag' => 'Akrab & ekspresif', 'copy' => 'Gaya visual yang menyatukan anggota tanpa kehilangan karakter komunitas.', 'class' => 'is-community'],
    ['title' => 'Full Color', 'tag' => 'Ilustratif & maksimal', 'copy' => 'Ruang bebas untuk pattern, ilustrasi, foto, atau gradasi khas brand Anda.', 'class' => 'is-full-color'],
    ['title' => 'Minimalis', 'tag' => 'Sederhana & berkelas', 'copy' => 'Komposisi bersih untuk brand yang ingin tampil tenang, tajam, dan premium.', 'class' => 'is-minimal'],
  ];
@endphp
<div class="support-page design-template-page">
  <section class="support-hero support-hero--template" aria-labelledby="template-design-title">
    <div class="support-shell support-hero__grid">
      <div>
        <p class="support-eyebrow">Arah Visual</p>
        <h1 id="template-design-title">Mulai dari Gaya yang Paling Cocok untuk Brand Anda</h1>
        <p class="support-lead">Pilih salah satu arah desain sebagai titik awal. Tim kami akan membantu menerjemahkannya menjadi lanyard yang siap diproduksi.</p>
        <a class="support-button support-button--primary" href="https://wa.me/6282210200700?text={{ rawurlencode('Halo Lanyard Jogja, saya mau konsultasi desain lanyard custom.') }}" target="_blank" rel="noopener noreferrer" data-button-motion><span data-button-label>Konsultasi Desain</span></a>
      </div>
      <aside class="support-hero__aside" aria-label="Cara memilih template desain">
        <span>01</span><p>Pilih gaya yang paling dekat dengan kebutuhan Anda.</p>
        <span>02</span><p>Kirim logo, warna brand, atau referensi visual.</p>
        <span>03</span><p>Terima arahan mockup dari tim Lanyard Jogja.</p>
      </aside>
    </div>
  </section>

  <section class="design-atlas" aria-labelledby="design-atlas-title">
    <div class="support-shell">
      <header class="support-section-heading">
        <p class="support-eyebrow">Pilih Titik Awal</p>
        <h2 id="design-atlas-title">Enam Arah Desain, Satu Identitas yang Kuat</h2>
      </header>
      <div class="design-atlas__list">
        @foreach($templateChoices as $choice)
          @php($message = 'Halo Lanyard Jogja, saya tertarik gaya desain '.$choice['title'].' untuk lanyard custom.')
          <a class="design-atlas__item {{ $choice['class'] }}" href="https://wa.me/6282210200700?text={{ rawurlencode($message) }}" target="_blank" rel="noopener noreferrer">
            <span class="design-atlas__number">0{{ $loop->iteration }}</span>
            <div><small>{{ $choice['tag'] }}</small><h3>{{ $choice['title'] }}</h3><p>{{ $choice['copy'] }}</p></div>
            <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
          </a>
        @endforeach
      </div>
    </div>
  </section>
</div>
@include('components.footer')
@endsection
