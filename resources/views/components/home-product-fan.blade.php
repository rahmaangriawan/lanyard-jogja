@php
  use App\Models\Product;

  $products = \App\Support\HomePageCache::remember(
    'home_product_fan_products',
    fn () => Product::with('category')
      ->where('published', true)
      ->orderBy('sortOrder')
      ->take(5)
      ->get()
  );

  if ($products->isEmpty()) {
    $products = collect([
      (object) ['name' => 'Lanyard Tissue Premium', 'slug' => 'lanyard-tissue', 'shortDescription' => 'Permukaan halus dengan hasil cetak warna yang tajam.', 'featuredImage' => '/uploads/hero-lanyard-slider-01.webp', 'basePrice' => 'Mulai Rp 5.000', 'category' => (object) ['name' => 'Premium']],
      (object) ['name' => 'Lanyard Polyester Standar', 'slug' => 'lanyard-polyester', 'shortDescription' => 'Pilihan ekonomis dan tahan lama untuk kebutuhan event.', 'featuredImage' => '/uploads/hero-lanyard-slider-02.webp', 'basePrice' => 'Mulai Rp 3.500', 'category' => (object) ['name' => 'Standar']],
      (object) ['name' => 'Lanyard Nylon Premium', 'slug' => 'lanyard-nylon', 'shortDescription' => 'Material kuat dengan tampilan rapi untuk pemakaian rutin.', 'featuredImage' => '/uploads/aset-produk.webp', 'basePrice' => 'Mulai Rp 7.500', 'category' => (object) ['name' => 'Premium']],
      (object) ['name' => 'ID Card Holder', 'slug' => 'id-card-holder', 'shortDescription' => 'Pelindung kartu identitas untuk kantor dan kegiatan.', 'featuredImage' => '/uploads/hero-lanyard-slider-01.webp', 'basePrice' => 'Mulai Rp 15.000', 'category' => (object) ['name' => 'Aksesori']],
      (object) ['name' => 'Wristband Event', 'slug' => 'wristband-event', 'shortDescription' => 'Wristband custom untuk identitas acara yang lebih praktis.', 'featuredImage' => '/uploads/hero-lanyard-slider-02.webp', 'basePrice' => 'Mulai Rp 4.000', 'category' => (object) ['name' => 'Event']],
    ]);
  }

  $products = $products->take(5);

  $asset = fn ($source) => \App\Support\AssetUrl::relative($source) ?: '/uploads/aset-produk.webp';
  $plain = fn ($value) => trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
@endphp

<section id="produk-pilihan" class="home-product-fan" aria-labelledby="home-product-fan-title" data-home-product-fan>
  <div class="home-shell">
    <header class="home-product-fan__header">
      <div>
        <p>Produk Pilihan</p>
        <h2 id="home-product-fan-title">Pilih Lanyard Sesuai Gayamu</h2>
      </div>
      <div class="home-product-fan__controls" aria-label="Kontrol produk pilihan">
        <button type="button" data-product-fan-prev aria-label="Produk sebelumnya"><span class="material-symbols-outlined" aria-hidden="true">arrow_back</span></button>
        <button type="button" data-product-fan-next aria-label="Produk berikutnya"><span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></button>
      </div>
    </header>

    <div class="home-product-fan__stage" data-product-fan-stage tabindex="0" aria-label="Produk pilihan, gunakan tombol panah untuk berpindah">
      <ul class="home-product-fan__list">
        @foreach ($products as $index => $product)
          @php
            $image = $asset($product->featuredImage ?? null);
            $description = $plain($product->shortDescription ?? '') ?: 'Produk custom berkualitas untuk kebutuhan identitas dan promosi Anda.';
            $detailUrl = route('products.show', ['slug' => $product->slug]);
          @endphp
          <li class="home-product-fan__item" data-product-fan-item>
            <article class="home-product-fan__card">
              <button
                type="button"
                class="home-product-fan__select"
                data-product-fan-select
                data-product-fan-index="{{ $index }}"
                data-product-fan-url="{{ $detailUrl }}"
                aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                aria-label="{{ $index === 0 ? 'Produk aktif: ' : 'Pilih produk: ' }}{{ $product->name }}"
              >
                <img
                  src="{{ $image }}"
                  @if ($srcset = \App\Support\ResponsiveImage::srcset($image, [320, 640]))
                    srcset="{{ $srcset }}"
                    sizes="(min-width: 768px) 360px, 82vw"
                  @endif
                  alt="{{ $product->name }}"
                  width="640"
                  height="640"
                  loading="lazy"
                  decoding="async"
                >
                <span class="home-product-fan__category">{{ $product->category->name ?? 'Lanyard Custom' }}</span>
              </button>
              <div class="home-product-fan__body">
                <h3>{{ $product->name }}</h3>
                <p>{{ $description }}</p>
                <div>
                  <strong>{{ !empty($product->basePrice) ? $product->basePrice : 'Minta quotation' }}</strong>
                  <a href="{{ $detailUrl }}">Lihat Detail <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
                </div>
              </div>
            </article>
          </li>
        @endforeach
      </ul>
    </div>

    <p class="home-product-fan__status sr-only" data-product-fan-status aria-live="polite"></p>
    <a class="home-product-fan__all" href="{{ route('products.index') }}">Lihat Semua Produk <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
  </div>
</section>
