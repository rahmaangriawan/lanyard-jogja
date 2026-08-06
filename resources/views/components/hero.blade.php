@php
  $heroQuotationUrl = 'https://wa.me/6282210200700?text='.rawurlencode('Halo Lanyard Jogja, saya mau pesan lanyard custom.');
@endphp

<section id="hero" class="ptl-hero" aria-labelledby="hero-title">
  <div class="ptl-hero__container">
    <div class="ptl-hero__content">
      <p class="ptl-hero__eyebrow">
        <span class="material-symbols-outlined" aria-hidden="true">location_city</span>
        <span>Vendor Terpercaya di Jogja</span>
      </p>
      <h1 id="hero-title" class="ptl-hero__title">
        <span>Cetak</span>
        <span>Lanyard</span>
        <span>Custom</span>
        <strong>Jogja</strong>
      </h1>

      <p class="ptl-hero__description">
        Spesialis cetak tali ID card, wristband lanyard, dan keychain berkualitas tinggi. Produksi kilat, harga terjangkau, gratis ongkir ke seluruh Indonesia.
      </p>

      <div class="ptl-hero__actions">
        <a
          href="{{ $heroQuotationUrl }}"
          target="_blank"
          rel="noopener noreferrer"
          class="ptl-hero__cta ptl-hero__cta--primary"
        >
          <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
          <span>Pesan Sekarang</span>
        </a>
        <a href="{{ route('products.index') }}" class="ptl-hero__cta ptl-hero__cta--secondary">
          <span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
          <span>Lihat Produk</span>
        </a>
      </div>
    </div>

    <div class="ptl-hero__visual" aria-label="Contoh hasil cetak lanyard custom">
      <img
        class="ptl-hero__image"
        src="/uploads/hero-lanyard-jogja.webp"
        alt="Koleksi lanyard custom bertema Jogja dengan ID card dan aksesori"
        width="1448"
        height="1086"
        loading="eager"
        fetchpriority="high"
        decoding="async"
      >
      <div class="ptl-hero__shipping" aria-label="Gratis ongkir se-Indonesia">
        <span class="material-symbols-outlined" aria-hidden="true">local_shipping</span>
        <span>Gratis Ongkir<br>Se-Indonesia</span>
      </div>
    </div>

    <ul class="ptl-hero__metrics" aria-label="Ringkasan performa Lanyard Jogja">
      <li>
        <span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
        <span><strong>10.000+</strong><small>Pcs / Hari</small></span>
      </li>
      <li>
        <span class="material-symbols-outlined" aria-hidden="true">schedule</span>
        <span><strong>1–3 hr</strong><small>Waktu Produksi</small></span>
      </li>
      <li>
        <span class="ptl-hero__metric-star" aria-hidden="true">★</span>
        <span><strong>5.0</strong><small>Rating Pelanggan</small></span>
      </li>
    </ul>
  </div>
</section>
