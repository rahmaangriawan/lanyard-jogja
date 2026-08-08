# Deployment Log dan Runbook Lanyard Jogja

Template ini mendokumentasikan deploy tanpa menyimpan secret. Workflow aktif
tetap berada di `.github/workflows/deploy.yml`; dokumen ini tidak mengubahnya.

## Log eksekusi

```text
Tanggal/waktu:
Commit/branch:
Operator:
Environment: production / staging / local
Artifact:
Database migration:
Cache clear/optimize:
Health-check URL dan status:
Smoke test route:
Rollback diperlukan: ya / tidak
Catatan/error:
```

## Prasyarat CloudPanel

- PHP 8.3 dan document root menuju folder `public`.
- `.env` production sudah dibuat di server dan tidak disimpan di repository.
- Secret GitHub environment: `SSH_HOST`, `SSH_PORT`, `SSH_USER`,
  `SSH_PASSWORD`.
- Variable opsional: `DEPLOY_PATH`, `PHP_BIN`, `HEALTHCHECK_URL`.
- Backup database dan `storage` tersedia sebelum deploy.

## Isi package dan data runtime

Package mengecualikan `.git/`, `.github/`, `.env*`, `node_modules/`, `tests/`,
`public/hot`, `public/storage`, cache/log/session runtime, `database/*.sql`, dan
`.phpunit.result.cache`. Server mempertahankan `.env`, `storage/`,
`public/storage/`, dan upload CMS.

## Urutan deploy yang harus dicatat

1. Build dan package dibuat di runner.
2. Archive diunggah ke server staging.
3. Laravel masuk maintenance mode.
4. Release disinkronkan tanpa menimpa `.env`, `storage/`, atau upload CMS.
5. Jalankan `artisan migrate --force` dan `artisan storage:link --force`.
6. Jalankan `artisan optimize:clear`, lalu `artisan optimize`.
7. Pastikan permission runtime benar dan aplikasi keluar dari maintenance mode.
8. Jalankan health check URL serta smoke test `/`, `/blog`, `/produk`, dan `/kawruh`.

## Rollback

- Hentikan publikasi release jika health check gagal.
- Pulihkan release/source sebelumnya dan database backup sesuai prosedur
  CloudPanel.
- Pulihkan `.env`, `storage`, dan upload CMS dari backup; jangan menghapus data
  CMS secara rekursif.
- Catat penyebab, waktu pemulihan, dan verifikasi ulang health check.
