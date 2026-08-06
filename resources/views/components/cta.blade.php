@php
  $ctaWhatsappUrl = 'https://wa.me/6282210200700?text='.rawurlencode('Halo Lanyard Jogja, saya mau konsultasi harga lanyard custom.');
@endphp

<section class="home-cta" id="cta-section" aria-labelledby="home-cta-title">
  <div class="home-shell">
    <div class="home-cta__container">
      <img class="home-cta__landmark" src="/uploads/home-cta/tugu-jogja.webp" alt="" width="1490" height="854" loading="lazy" decoding="async" aria-hidden="true">
      <div class="home-cta__copy">
        <h2 id="home-cta-title"><span>Wujudkan</span> Lanyard Impian <span>Anda Sekarang</span></h2>
        <p class="home-cta__description">Konsultasi desain gratis, produksi 1-3 hari, gratis ongkir ke seluruh Indonesia.</p>
        <div class="home-cta__actions">
          <a class="home-cta__button home-cta__button--whatsapp ptl-button-motion" href="{{ $ctaWhatsappUrl }}" target="_blank" rel="noopener noreferrer" data-button-motion>
            <img src="/images/whatsapp.svg" alt="" width="20" height="20" loading="lazy" decoding="async" aria-hidden="true">
            <span class="ptl-button-motion__label" data-button-label>Chat WhatsApp</span>
          </a>
          <a class="home-cta__button home-cta__button--pricing ptl-button-motion" href="#paket-harga" data-button-motion>
            <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m0 16H5V8h14zm-9-8h5v2h-5zm0 4h5v2h-5z"/></svg>
            <span class="ptl-button-motion__label" data-button-label>Cek Harga Dulu</span>
          </a>
        </div>
      </div>
      <div class="home-cta__image" aria-hidden="true">
        <img src="/uploads/home-cta/lanyard-cta.webp" alt="" width="941" height="1672" loading="lazy" decoding="async">
      </div>
    </div>
  </div>
</section>
