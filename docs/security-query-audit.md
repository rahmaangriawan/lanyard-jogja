# Security dan Query Audit

Semua nilai dari request harus tetap menjadi query binding. Jangan memasukkan
nilai request ke raw SQL, nama kolom, sorting, atau aggregate.

## Hasil audit 2026-08-08

- Pencarian blog di `routes/web.php` membatasi panjang input, meng-escape `!`,
  `%`, dan `_`, lalu memakai `LIKE ? ESCAPE '!'` dengan binding.
- CMS search di `CmsResourceController` dan laporan URL tidak ditemukan di
  `UtilityPageController` memakai pola binding dan wildcard escaping yang sama.
- Pencarian produk API menggunakan Eloquent operator `like`; nilai tetap
  diparameterisasi oleh query builder dan tidak diinterpolasi sebagai SQL.
- `WriterController` memakai `orderByRaw` hanya untuk ekspresi role internal
  dengan nilai role sebagai binding.
- Aggregate dashboard dan subquery memakai ekspresi SQL statis; tidak ada
  kolom atau fungsi yang berasal dari request.

Tidak ditemukan interpolasi SQL request atau kebutuhan perubahan behavior pada
audit ini. Semantics pencarian, urutan, pagination, dan response API dipertahankan.

## Inventaris indeks

Migration yang ada sudah menyediakan index untuk slug, SKU, published,
category, urutan/tanggal publikasi, komentar per post, kota, order, dan
testimonial. Audit menemukan listing portfolio belum memiliki kombinasi
`published`, `sortOrder`, dan `createdAt`; migration
`2026_08_08_120000_add_portfolio_listing_index.php` menambahkan index tersebut
secara idempotent dan reversible. Migration belum dijalankan karena MySQL lokal
tidak aktif saat audit.

Sebelum menambah migration lain, cek index aktual di database:

```sql
SHOW INDEX FROM product;
SHOW INDEX FROM post;
SHOW INDEX FROM category;
SHOW INDEX FROM testimonial;
```

Gunakan `EXPLAIN` pada query publik blog, produk, homepage, kategori, dan
pencarian. Migration index baru hanya boleh dibuat bila index yang sama belum
ada dan rencana query menunjukkan kebutuhan nyata; migration harus idempotent,
memiliki nama index eksplisit, dan reversible.

## Aturan untuk query baru

1. Escape `!`, `%`, dan `_` untuk pencarian literal.
2. Gunakan binding dengan `LIKE ? ESCAPE '!'`.
3. Batasi dynamic sorting pada allowlist kolom internal.
4. Jangan mengubah hasil, urutan, pagination, atau response shape saat
   melakukan cleanup query.
