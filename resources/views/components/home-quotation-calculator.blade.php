@php
  $calculatorPackages = [
    ['value' => '1.5', 'label' => 'Lanyard 1.5 cm', 'price' => 9000, 'formatted' => 'Rp 9.000'],
    ['value' => '2', 'label' => 'Lanyard 2 cm', 'price' => 10000, 'formatted' => 'Rp 10.000'],
    ['value' => '2.5', 'label' => 'Lanyard 2.5 cm', 'price' => 12000, 'formatted' => 'Rp 12.000'],
  ];
@endphp

<section id="kalkulator-harga" class="home-quotation-calculator" aria-labelledby="home-quotation-calculator-title" data-quotation-calculator>
  <div class="home-shell home-quotation-calculator__layout">
    <header class="home-quotation-calculator__intro" data-sr="fade">
      <p>Estimasi Cepat</p>
      <h2 id="home-quotation-calculator-title">Hitung Estimasi Pesanan</h2>
      <span>Pilih ukuran dan jumlah pesanan untuk melihat perkiraan biaya lanyard custom Anda.</span>
    </header>

    <form class="home-quotation-calculator__form" data-quotation-form>
      <fieldset>
        <legend>Ukuran lanyard</legend>
        <div class="home-quotation-calculator__packages">
          @foreach ($calculatorPackages as $package)
            <label class="home-quotation-calculator__package{{ $loop->first ? ' is-selected' : '' }}">
              <input type="radio" name="lanyard_size" value="{{ $package['value'] }}" data-package-price="{{ $package['price'] }}" data-package-label="{{ $package['label'] }}" {{ $loop->first ? 'checked' : '' }}>
              <span>{{ $package['label'] }}</span>
              <strong>{{ $package['formatted'] }}<small>/pcs</small></strong>
            </label>
          @endforeach
        </div>
      </fieldset>

      <div class="home-quotation-calculator__fields">
        <label class="home-quotation-calculator__field">
          <span>Jumlah pesanan</span>
          <span class="home-quotation-calculator__quantity">
            <button type="button" aria-label="Kurangi jumlah" data-quotation-decrease>−</button>
            <input type="number" name="quantity" min="50" max="100000" step="1" value="50" inputmode="numeric" aria-describedby="quotation-minimum">
            <button type="button" aria-label="Tambah jumlah" data-quotation-increase>+</button>
          </span>
          <small id="quotation-minimum">Minimum pemesanan 50 pcs.</small>
        </label>

        <label class="home-quotation-calculator__field">
          <span>Jenis bahan</span>
          <select name="material">
            <option value="Polyester Tissue">Polyester Tissue</option>
            <option value="Nylon">Nylon</option>
            <option value="Belum menentukan">Belum menentukan</option>
          </select>
          <small>Detail bahan dikonfirmasi bersama tim kami.</small>
        </label>
      </div>

      <div class="home-quotation-calculator__result" aria-live="polite" aria-atomic="true">
        <div>
          <span>Estimasi total</span>
          <strong data-quotation-total>Rp 450.000</strong>
          <small data-quotation-breakdown>50 pcs × Rp 9.000</small>
        </div>
        <a href="https://wa.me/6282210200700" target="_blank" rel="noopener noreferrer" data-quotation-link data-button-motion>
          <span data-button-label>Minta Quotation</span>
          <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M5 12h13m-6-6 6 6-6 6" /></svg>
        </a>
      </div>
      <p class="home-quotation-calculator__disclaimer">Estimasi ini menggunakan harga paket dasar full printing. Harga akhir menyesuaikan detail desain dan spesifikasi pesanan.</p>
    </form>
  </div>
</section>
