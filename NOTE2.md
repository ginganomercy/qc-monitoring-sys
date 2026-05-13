# 📝 Catatan Lanjutan Deployment (cPanel)

> **Status Saat Ini**: Domain `qc-monitoring-tf.dev-myproject.my.id` sudah dibuat dan repositori GitHub telah berhasil ditarik (pull) ke File Manager cPanel (`public_html/repositories/qc-monitoring-sys`). **Tahap selanjutnya (Tertunda - Silakan istirahat)**: Membuat file `.env` di File Manager dan mengeksekusi perintah Terminal.

---

## ✅ 1. Ringkasan Perubahan Terakhir (Lokal -> GitHub)

- **Panel UI**: Konfigurasi `AdminPanelProvider.php` telah diubah agar aplikasi **selalu berada dalam Light Mode (putih)**, mengabaikan tema gelap pada sistem/browser user.
- **Performa**: Package `predis/predis` telah diinstal melalui Composer. Package ini krusial agar aplikasi Laravel dapat terkoneksi dengan fitur Redis di cPanel untuk caching dan manajemen sesi yang lebih cepat.
- **Git Repository**: Semua perubahan di atas sudah di-commit dan di-push ke branch `main` pada repository `https://github.com/ginganomercy/qc-monitoring-sys`.

---

## 🛠️ 2. Langkah Lanjutan di cPanel (Kapanpun Dilanjutkan)

### A. Konfigurasi Document Root (Kunci Keamanan)
Saat membuat subdomain (misal: `qc-monitoring-tf.dev-myproject.my.id`), bagian terpenting untuk keamanan Laravel adalah pengaturan **Document Root**.

1. Di form pembuatan domain, **jangan centang** "Bagikan root dokumen".
2. Pada kolom Document Root yang muncul, isi dengan path yang berujung pada folder `public/` dari repository Anda.
   - **Contoh (Luar public_html)**: `/repositories/qc-monitoring-sys/public`
   - **Contoh (Dalam public_html)**: `public_html/repositories/qc-monitoring-sys/public`
3. *Penjelasan Keamanan*: Dengan mengarahkan Document Root ke `/public`, web server Apache/LiteSpeed secara otomatis mengunci akses publik. User dari internet tidak akan pernah bisa men-download file `.env` atau folder `app/` milik Anda, sehingga sangat aman.

### B. Membuat Konfigurasi `.env` (Penting!)
Buka **File Manager** cPanel, masuk ke root folder repository (bukan folder public). Buat file `.env` baru dan isikan:

```env
APP_NAME="QC Monitor"
APP_ENV=production
# APP_KEY biarkan kosong dulu, nanti di-generate via terminal
APP_KEY=
APP_DEBUG=false
APP_URL=https://qc-monitoring-tf.dev-myproject.my.id

# Konfigurasi Database (Buat database & user dulu di menu MySQL Databases)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db_yang_dibuat
DB_USERNAME=user_db_yang_dibuat
DB_PASSWORD=password_db_yang_dibuat

# Konfigurasi Redis (Untuk performa maksimal)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
```

### C. Eksekusi Perintah Terminal Akhir
Buka menu **Terminal** di cPanel, lalu ketik perintah berikut secara berurutan:

```bash
# 1. Masuk ke direktori repository Anda (sesuaikan path-nya)
cd ~/repositories/qc-monitoring-sys
# atau cd ~/public_html/repositories/qc-monitoring-sys

# 2. Install dependensi PHP untuk versi production
composer install --optimize-autoloader --no-dev

# 3. Generate Application Key (wajib untuk security)
php artisan key:generate

# 4. Jalankan Migrasi dan Seeder
php artisan migrate --force
php artisan db:seed --force

# 5. Buat Storage Link (agar gambar/file bisa diakses)
php artisan storage:link

# 6. Bersihkan & Build Ulang Cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### D. Update Otomatis di Masa Depan
Jika suatu saat ada perbaikan kode di lokal dan sudah di-push ke GitHub, cara updatenya sangat mudah:
1. Buka cPanel > **Git™ Version Control**.
2. Klik **Manage** pada repo Anda.
3. Tab **Pull or Deploy** > klik **Update from Remote**.
4. Buka Terminal, masuk ke folder project, lalu jalankan `php artisan optimize:clear` dan `php artisan config:cache`.

---
*Catatan ini dibuat agar proses deployment bisa dilanjutkan kapan saja tanpa kehilangan progres.*
