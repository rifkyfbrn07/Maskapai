# Menjalankan Maskapai dengan Docker

## Prasyarat

- Docker Engine 26+ dan Docker Compose v2.
- Jangan memakai password contoh pada file environment.

## Konfigurasi pertama kali

1. Buat konfigurasi Docker, application key, dan password MySQL secara otomatis:

   ```bash
   chmod +x scripts/bootstrap.sh
   ./scripts/bootstrap.sh
   ```

2. Bila memakai Midtrans asli, isi `MIDTRANS_CLIENT_KEY` dan `MIDTRANS_SERVER_KEY` dengan Sandbox Access Keys dari dashboard Midtrans. Untuk demo lokal/UKK, biarkan kosong dan gunakan Local Simulator.

3. Build dan jalankan seluruh lima container:

   ```powershell
   docker compose up -d --build
   ```

4. Verifikasi service:

   ```powershell
   docker compose ps
   docker compose logs --tail=100
   Invoke-WebRequest http://localhost:8080/up
   ```

## Migration dan seeder

Jalankan hanya dari satu replica. Menjalankan migration bersamaan dari tiga replica dapat menyebabkan race condition.

```powershell
docker compose exec web1 php artisan migrate --force
docker compose exec web1 php artisan db:seed --force
docker compose exec web1 php artisan optimize
```

## Verifikasi konektivitas

```powershell
docker compose exec web1 php artisan migrate:status
docker compose exec web1 php artisan tinker --execute="DB::select('SELECT 1');"
docker compose exec mysql mysql -umaskapai -p maskapai
```

Untuk melihat distribusi request, ulangi request berikut. Header `X-Backend` harus memperlihatkan lebih dari satu backend ketika ketiga container sehat:

```powershell
1..12 | ForEach-Object { (Invoke-WebRequest http://localhost:8080/up).Headers['X-Backend'] }
```

MySQL tidak diekspos ke host dan hanya dapat diakses container pada jaringan `backend`. Storage Laravel memakai named volume bersama agar upload tetap tersedia pada semua replica. Untuk deployment nyata, ganti storage volume bersama dengan object storage (misalnya S3) dan kelola secret melalui secret manager.
