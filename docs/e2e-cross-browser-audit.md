# E2E dan Cross-Browser Audit

Dokumen ini adalah checklist manual untuk alur publik Lanyard Jogja. Audit tidak
mengubah route, response, payload WhatsApp, atau behavior aplikasi.

## Target dan bukti

- Target lokal: `http://127.0.0.1:8000`
- Tanggal audit: `2026-08-08`
- Penguji: isi saat audit manual
- Bukti: screenshot, URL akhir, viewport, browser, dan waktu pengujian
- Status awal: pending eksekusi manual oleh penguji
- Smoke request lokal 2026-08-08: blocked karena MySQL lokal menolak koneksi;
  jalankan MySQL/Laragon sebelum mengisi hasil route.

## E2E artikel sampai WhatsApp

| Skenario | Langkah | Hasil yang diharapkan |
| --- | --- | --- |
| Pencarian normal | Buka `/blog`, isi `lanyard`, kirim pencarian | Hasil tampil, URL mempertahankan `q`, tanpa error |
| Karakter khusus | Uji apostrof, `%`, `_`, dan teks mirip SQL | Tidak 500, tidak bocor lintas data, hasil tetap aman |
| Hasil kosong | Cari istilah yang tidak ada | Empty state ringkas dan tombol hapus pencarian berfungsi |
| Buka artikel | Klik kartu hasil ke `/blog/{slug}` | Canonical, judul, metadata, gambar, dan isi sesuai artikel |
| Order WhatsApp | Dari artikel/produk klik CTA order | Href WhatsApp tetap memakai nomor Jogja dan konteks produk/order |
| URL invalid | Buka slug artikel/produk yang tidak ada | 404 terkontrol, bukan exception atau halaman kosong |

Jangan mengirim pesan WhatsApp selama audit; cukup verifikasi URL, query, dan
isi pesan yang telah terisi.

## Matriks browser dan viewport

| Browser/device | Desktop | Mobile | Status |
| --- | --- | --- | --- |
| Chrome/Edge | 1280px, 1440px | Android Chrome 390px | isi saat diuji |
| Firefox | 1280px | Android Firefox bila tersedia | isi saat diuji |
| Safari | macOS Safari bila tersedia | iOS Safari 375px/390px | perlu perangkat atau layanan eksternal bila tidak tersedia |

Pada setiap kombinasi, periksa header/menu, pencarian blog, kartu artikel, detail
artikel, CTA WhatsApp, focus keyboard, gambar, reduced-motion, console error,
dan horizontal overflow.

## Kriteria selesai

- Tidak ada horizontal scroll pada 375px, 390px, 412px, tablet, atau desktop.
- Tidak ada aset 404, teks terpotong, CTA tertutup, atau layout shift mencolok.
- Search, pagination, link artikel, link produk, dan WhatsApp tetap menuju URL
  yang sama seperti sebelum audit.
- Browser/device yang tidak tersedia dicatat sebagai pending, bukan dianggap lulus.
