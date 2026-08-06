@php
  $pricePackages = [
    [
      'name' => 'Lanyard 1.5 CM',
      'specification' => 'Ukuran: 45cm, Bahan: Polyester Tissue',
      'price' => 'Rp 9.000',
      'features' => ['Full Printing 2 Sisi', 'Kait & Stopper Termasuk', 'Bahan Polyester', 'Desain Gratis', 'Gratis Ongkir'],
    ],
    [
      'name' => 'Lanyard 2 CM',
      'specification' => 'Ukuran: 45cm, Bahan: Polyester / Nylon',
      'price' => 'Rp 10.000',
      'featured' => true,
      'features' => ['Full Printing 2 Sisi', 'Kait & Stopper Termasuk', 'Polyester Tissue / Nylon', 'Desain Gratis', 'Gratis Ongkir', 'Cocok untuk Seminar'],
    ],
    [
      'name' => 'Lanyard 2.5 CM',
      'specification' => 'Ukuran: 45cm, Bahan: Polyester / Nylon',
      'price' => 'Rp 12.000',
      'features' => ['Full Printing 2 Sisi', 'Kait & Stopper Termasuk', 'Polyester Tissue / Nylon', 'Desain Gratis', 'Gratis Ongkir', 'Ideal untuk Branding Besar'],
    ],
  ];
@endphp

<section id="paket-harga" class="home-price-packages" aria-labelledby="home-price-packages-title">
  <div class="home-shell">
    <header class="home-price-packages__intro" data-sr="fade">
      <p>Harga Transparan</p>
      <h2 id="home-price-packages-title">Paket Harga</h2>
    </header>

    <div class="home-price-packages__grid" aria-label="Pilihan paket harga lanyard">
      @foreach ($pricePackages as $package)
        @php
          $quotationUrl = 'https://wa.me/6282210200700?text=' . rawurlencode('Halo Lanyard Jogja, saya ingin pesan paket ' . $package['name'] . '.');
        @endphp
        <article class="home-price-packages__card{{ !empty($package['featured']) ? ' is-featured' : '' }}" data-sr="fade" data-sr-delay="{{ $loop->index * 90 }}">
          @if (!empty($package['featured']))
            <span class="home-price-packages__badge"><svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="m12 2 3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>Paling Populer</span>
          @endif
          <header>
            <h3>{{ $package['name'] }}</h3>
            <p>{{ $package['specification'] }}</p>
          </header>
          <div class="home-price-packages__price"><strong>{{ $package['price'] }}</strong><span>/pcs</span></div>
          <p class="home-price-packages__minimum">Minimum order 50 pcs</p>
          <ul>
            @foreach ($package['features'] as $feature)
              <li><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ $feature }}</li>
            @endforeach
          </ul>
          <a href="{{ $quotationUrl }}" target="_blank" rel="noopener noreferrer" data-button-motion aria-label="Pesan {{ $package['name'] }}"><span data-button-label>Pesan Sekarang</span></a>
        </article>
      @endforeach
    </div>
  </div>
</section>
