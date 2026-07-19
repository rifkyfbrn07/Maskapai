# Panduan Deploy Laravel Docker: FlyIndonesia / Maskapai

Panduan ini menjalankan arsitektur lima container:

```text
Client -> Nginx Load Balancer -> web1, web2, web3 -> MySQL
```

## 1. Prasyarat Ubuntu Server

Pastikan Ubuntu Server memiliki Git, Docker Engine, dan Docker Compose Plugin.

```bash
sudo apt update
sudo apt install -y git ca-certificates curl
docker --version
docker compose version
```

Docker harus aktif dan user deployment harus boleh menjalankan Docker. Jika belum, ikuti instalasi resmi Docker Engine untuk Ubuntu, lalu logout/login setelah menambahkan user ke grup `docker`.

## 2. Clone Project

```bash
cd /home/ujikom
git clone https://github.com/USERNAME/NAMA-REPOSITORY.git Maskapai
cd Maskapai
```

Ganti URL repository dengan repository GitHub Anda. Jangan pernah menyimpan file `.env` atau key pembayaran di repository.

## 3. Buat Environment Aman

Project menyediakan bootstrap untuk membuat `.env`, `APP_KEY`, serta password MySQL secara otomatis.

```bash
chmod +x scripts/bootstrap.sh
./scripts/bootstrap.sh
```

Script hanya membuat `.env` bila file tersebut belum ada; ia tidak menimpa konfigurasi existing.

Cek konfigurasi dasar:

```bash
nano .env
```

Untuk server ini, sesuaikan minimal:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP_SERVER:8080
APP_PORT=8080
```

Ganti `IP_SERVER` dengan IP atau domain yang digunakan pengguna. Simpan dengan `Ctrl+O`, `Enter`, lalu keluar `Ctrl+X`.

## 4. Midtrans (Opsional untuk Demo)

Untuk demo/UKK, key Midtrans boleh dibiarkan kosong dan gunakan tombol Local Simulator di halaman checkout.

Untuk Sandbox asli, isi credential yang diambil dari Midtrans Dashboard > Settings > Access Keys:

```env
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=SB-Mid-client-KEY_SANDBOX_ASLI
MIDTRANS_SERVER_KEY=SB-Mid-server-KEY_SANDBOX_ASLI
MIDTRANS_IS_SANDBOX=true
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/v1/transactions
MIDTRANS_STATUS_URL=https://api.sandbox.midtrans.com/v2
```

Jangan memakai key contoh, `mockkey`, atau key Production di Sandbox. Key Midtrans tidak boleh dikirim ke chat, disimpan di Git, atau dibagikan di screenshot.

## 5. Validasi Docker Compose

```bash
docker compose config --quiet
```

Tidak ada output berarti YAML dan variable environment valid. Jika muncul error, perbaiki terlebih dahulu; jangan lanjut build sebelum validasi ini lolos.

## 6. Build dan Menjalankan Lima Container

```bash
docker compose up -d --build
docker compose ps
```

Target status seluruh service adalah `Up` atau `healthy`:

```text
maskapai-load-balancer
maskapai-web1-1
maskapai-web2-1
maskapai-web3-1
maskapai-mysql
```

Jika build gagal, lihat log tanpa menghapus data database:

```bash
docker compose logs --tail=150
```

## 7. Jalankan Migration dan Seeder

Migration hanya dijalankan dari satu web container. Jangan menjalankan migration bersamaan dari tiga replica.

```bash
docker compose exec web1 php artisan migrate --force
docker compose exec web1 php artisan db:seed --force
docker compose exec web1 php artisan optimize:clear
```

Verifikasi:

```bash
docker compose exec web1 php artisan migrate:status
```

## 8. Verifikasi Aplikasi dan Database

Uji health endpoint:

```bash
curl -I http://IP_SERVER:8080/up
curl -I http://IP_SERVER:8080/
```

Kedua respons harus `HTTP/1.1 200 OK`. Uji koneksi MySQL dari Laravel:

```bash
docker compose exec web1 php artisan tinker --execute="DB::select('SELECT 1');"
```

Buka aplikasi melalui browser:

```text
http://IP_SERVER:8080
```

Jika port 80 masih dipakai Apache/Nginx Ubuntu, jangan membuka URL tanpa `:8080` sebelum reverse proxy host dikonfigurasi.

## 9. Verifikasi Asset CSS dan Vite

Jika halaman tampil seperti HTML tanpa Tailwind, periksa URL asset:

```bash
curl -s http://IP_SERVER:8080/ | grep -oE '(href|src)="[^"]*(build|hot)[^"]*"' | head
```

URL asset harus memuat `:8080`. Pada `docker/nginx/load-balancer.conf`, gunakan:

```nginx
proxy_set_header Host $http_host;
```

Bukan `$host`, karena `$host` dapat menghilangkan port dari URL asset.

## 10. Verifikasi Load Balancing

Nginx menggunakan upstream `web1`, `web2`, `web3` dan algoritme `least_conn`.

```bash
docker compose ps
docker compose logs --tail=50 load-balancer
```

Pastikan tiga service web sehat. Untuk state yang berpindah antar replica, project menggunakan database untuk session, cache, dan queue. File upload memakai named volume `laravel_storage` bersama.

## 11. Update Deployment

Saat ada perubahan dari GitHub:

```bash
git pull --ff-only
docker compose up -d --build
docker compose exec web1 php artisan migrate --force
docker compose exec web1 php artisan optimize:clear
docker compose exec web2 php artisan optimize:clear
docker compose exec web3 php artisan optimize:clear
```

Jangan menjalankan `docker compose down -v` pada server production kecuali memang ingin menghapus data MySQL dan storage secara permanen.

## 12. Checklist Troubleshooting

### APP_KEY error

Periksa apakah `.env` ada dan `APP_KEY` tidak kosong. Jangan generate key di setiap web container; ketiganya wajib menggunakan key yang sama.

### CSS tidak termuat

Pastikan membuka port `8080`, bukan Apache host pada port `80`. Jalankan build ulang:

```bash
docker compose up -d --build --force-recreate web1 web2 web3 load-balancer
```

### Midtrans warning

Pastikan tidak ada deklarasi `MIDTRANS_SERVER_KEY=` atau `MIDTRANS_CLIENT_KEY=` kedua yang kosong di bagian bawah `.env`.

```bash
awk -F= '/^MIDTRANS_(CLIENT_KEY|SERVER_KEY)=/ { print $1 ": " (length($2) ? "ADA" : "KOSONG") }' .env
```

### Lihat log container

```bash
docker compose logs --tail=200 web1
docker compose logs --tail=200 mysql
docker compose logs --tail=200 load-balancer
```

## 13. Catatan Keamanan

- Gunakan `APP_DEBUG=false` di server.
- Gunakan password MySQL yang dihasilkan bootstrap, jangan password contoh.
- Simpan `.env` dengan permission `600`.
- Jangan expose port MySQL ke host/public.
- Untuk webhook Midtrans asli, gunakan domain publik dengan HTTPS; IP private `192.168.x.x` tidak dapat menerima callback Midtrans.
- Backup volume MySQL sebelum update besar.
