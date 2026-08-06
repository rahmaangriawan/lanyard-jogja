@php
  $orderSteps = [
    [
      'title' => 'Hubungi Kami',
      'description' => 'Chat via WhatsApp atau hubungi kami untuk konsultasi desain dan kebutuhan Anda secara gratis.',
      'icon' => 'chat',
    ],
    [
      'title' => 'Kirim Desain',
      'description' => 'Unggah file desain AI, CDR, atau PDF; tim kami juga siap membantu membuat desain sesuai tema acara Anda.',
      'icon' => 'upload',
    ],
    [
      'title' => 'Konfirmasi',
      'description' => 'Tinjau kembali digital desain sebelum produksi dimulai. Revisi gratis hingga Anda puas.',
      'icon' => 'check',
    ],
    [
      'title' => 'Terima Pesanan',
      'description' => 'Lanyard dikerjakan 1–3 hari lalu dikirim gratis ke seluruh Indonesia dengan ekspedisi tepercaya.',
      'icon' => 'shipping',
    ],
  ];
@endphp

<section id="pemesanan" class="home-order-process" aria-labelledby="home-order-process-title">
  <div class="home-shell">
    <header class="home-order-process__intro" data-sr="fade">
      <p>Cara Pemesanan</p>
      <h2 id="home-order-process-title">Proses Mudah</h2>
    </header>

    <ol class="home-order-process__list">
      @foreach ($orderSteps as $step)
        <li data-sr="fade" data-sr-delay="{{ $loop->index * 90 }}">
          <span class="home-order-process__number">{{ $loop->iteration }}</span>
          <span class="home-order-process__icon" aria-hidden="true">
            @if ($step['icon'] === 'chat')
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M19.05 4.94A9.9 9.9 0 0 0 12.03 2C6.55 2 2.1 6.45 2.1 11.93c0 1.75.46 3.46 1.33 4.96L2 22l5.26-1.38a9.91 9.91 0 0 0 4.77 1.22h.01c5.47 0 9.92-4.45 9.92-9.93a9.84 9.84 0 0 0-2.91-6.97M12.03 20.16a8.2 8.2 0 0 1-4.18-1.14l-.3-.18-3.12.82.84-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.52 3.68-8.2 8.22-8.2a8.15 8.15 0 0 1 5.81 2.41 8.14 8.14 0 0 1 2.4 5.79c0 4.53-3.68 8.23-8.21 8.23m4.5-6.15c-.25-.12-1.47-.73-1.7-.81-.23-.09-.4-.12-.56.12-.17.25-.65.81-.8.98-.15.16-.3.18-.55.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.25-1.5-1.4-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.3.37-.45.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.85-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1s.9 2.44 1.02 2.61c.13.16 1.77 2.7 4.29 3.79.6.26 1.07.42 1.43.54.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.22-.16-.47-.29" /></svg>
            @elseif ($step['icon'] === 'upload')
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M19.35 10.04A7.49 7.49 0 0 0 4.64 9.1 5.5 5.5 0 0 0 5.5 20H10v-2H5.5a3.5 3.5 0 0 1-.35-6.98l1.33-.13.2-1.32a5.5 5.5 0 0 1 10.79.55l.2 1.38 1.39.05A3.5 3.5 0 0 1 19 18h-5v2h5a5.5 5.5 0 0 0 .35-10.96M13 13h3l-4-4-4 4h3v6h2z" /></svg>
            @elseif ($step['icon'] === 'check')
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M19 3h-1.18A3 3 0 0 0 15 1h-6a3 3 0 0 0-2.82 2H5a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-7-1a1 1 0 0 1 1 1h-2a1 1 0 0 1 1-1m7 19H5V5h2v1h10V5h2zm-8.2-4.3L7.6 13.5 9 12.1l1.8 1.8 4.2-4.2 1.4 1.4z" /></svg>
            @else
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M20 8h-3V4H3a2 2 0 0 0-2 2v11h2a3 3 0 0 0 6 0h6a3 3 0 0 0 6 0h2v-5zM6 18.5A1.5 1.5 0 1 1 7.5 17 1.5 1.5 0 0 1 6 18.5M15 15H9a3 3 0 0 0-6 0V6h12zm3 3.5a1.5 1.5 0 1 1 1.5-1.5 1.5 1.5 0 0 1-1.5 1.5M17 10h2.5l2 3H17z" /></svg>
            @endif
          </span>
          <h3>{{ $step['title'] }}</h3>
          <p>{{ $step['description'] }}</p>
        </li>
      @endforeach
    </ol>
  </div>
</section>
