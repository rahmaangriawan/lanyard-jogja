@php
  $fallbackTestimonials = [
    ['name' => 'Anggi Putri', 'role' => 'Admin Sekolah', 'quote' => 'Kami pesan lanyard custom untuk kegiatan sekolah, hasilnya memuaskan. Cetakan jelas, tidak mudah luntur, dan pengerjaannya tepat waktu.', 'avatar' => '/uploads/testimoni-anggi-putri.webp', 'tone' => 'terracotta'],
    ['name' => 'Andi Pratama', 'role' => 'Event Organizer', 'quote' => 'Pelayanan cepat dan hasil lanyardnya rapi. Warna sesuai desain, bahan nyaman dipakai, dan timnya responsif sampai barang diterima.', 'avatar' => '/uploads/testimoni-andi-pratama.webp', 'tone' => 'cream'],
    ['name' => 'Dimas Nugroho', 'role' => 'Owner UMKM', 'quote' => 'Kualitas selalu konsisten, harga masuk akal, dan desain bisa disesuaikan dengan brand. Cocok untuk kebutuhan kantor maupun promosi.', 'avatar' => '/uploads/testimoni-dimas-nugroho.webp', 'tone' => 'yellow'],
  ];
  $testimonials = \Illuminate\Support\Facades\Schema::hasTable('testimonial')
    ? \App\Support\HomePageCache::remember('home_testimonials', fn () => \App\Models\Testimonial::query()->where('published', true)->orderBy('sortOrder')->orderByDesc('createdAt')->take(3)->get())
    : collect($fallbackTestimonials);
@endphp

<section class="home-testimonials" id="testimoni" aria-label="Testimoni pelanggan Lanyard Jogja">
  <div class="home-shell">
    <header class="home-testimonials__intro" data-sr="fade">
      <p>Testimoni Pelanggan</p>
      <h2>Apa Kata Mereka</h2>
    </header>
    <div class="home-testimonials__cards">
      @foreach ($testimonials as $testimonial)
        @php
          $isFallback = is_array($testimonial);
          $tone = $isFallback ? $testimonial['tone'] : ['terracotta', 'cream', 'yellow'][$loop->index % 3];
          $name = $isFallback ? $testimonial['name'] : $testimonial->name;
          $role = $isFallback ? $testimonial['role'] : $testimonial->role;
          $quote = $isFallback ? $testimonial['quote'] : $testimonial->quote;
          $avatar = $isFallback ? $testimonial['avatar'] : $testimonial->imageUrl;
        @endphp
        <article class="home-testimonials__note home-testimonials__note--{{ $tone }}" data-sr="fade" data-sr-delay="{{ $loop->index * 100 }}">
          <header>
            @if($avatar)<img src="{{ $avatar }}" alt="Foto {{ $name }}" width="256" height="256" loading="lazy" decoding="async">@endif
            <span><strong>{{ $name }}</strong><small>{{ $role }}</small></span>
          </header>
          <blockquote><p>{{ $quote }}</p></blockquote>
          <span class="home-testimonials__quote" aria-hidden="true">&rdquo;</span>
        </article>
      @endforeach
    </div>
  </div>
</section>
