# Menjalankan Maskapai dengan Docker

## Prasyarat

- Docker Engine 26+ dan Docker Compose v2.
- Jangan memakai password contoh pada file environment.

## Konfigurasi pertama kali

1. Salin konfigurasi Docker dan buat application key:

   ```powershell
   Copy-Item .env.docker.example .env
   docker run --rm php:8.3-cli php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
   ```

2. Salin nilai `base64:...` yang dihasilkan ke `APP_KEY` dalam `.env`, lalu ganti `MYSQL_PASSWORD` dan `MYSQL_ROOT_PASSWORD` dengan password berbeda yang kuat.

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
