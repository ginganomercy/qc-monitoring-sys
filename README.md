# QC Monitoring System

Sistem manajemen *Quality Control* berbasis Laravel 10 dan Filament 3. 
Dirancang khusus untuk monitoring produksi pabrik garmen, termasuk pelacakan *defect*, *daily target*, dan kinerja lini produksi.

## 🚀 Fitur Utama
- **Manajemen QC**: Input form inspeksi dinamis (Pass/Reject) dengan detail komponen & jenis *defect*.
- **Data Master**: Kelola Style Produk, Line Produksi, Jenis Defect, dan Komponen.
- **Perencanaan**: Set dan pantau Target Harian per lini.
- **Dashboard Real-Time**: 
  - Sparklines performa 7 hari terakhir.
  - Grafik tren inspeksi bulanan/mingguan.
  - Top 5 *Defects* (dengan prioritas warna).
  - *Lazy loading* widgets untuk performa.

---

## 🛠️ Requirements
- Docker & Docker Compose
- (Opsional) PHP 8.2 & Composer jika ingin run lokal tanpa Docker.

---

## 📦 Deployment via Docker (Production-Ready)

Proyek ini menggunakan konfigurasi 4-container: **Nginx**, **PHP-FPM**, **MySQL 8.0**, dan **Redis 7**.

### 1. Setup Environment
```bash
# Copy template .env
cp .env.example .env

# Generate Application Key
# Jika tidak punya instalasi PHP lokal, Anda bisa menjalankan command ini nanti
# di dalam container (lihat step 3)
php artisan key:generate
```

Ubah password default di `.env`:
```env
DB_PASSWORD=SecurePassword123!
SEED_ADMIN_PASSWORD=GantiPasswordIni!
```

### 2. Start Services
```bash
docker compose up -d
```
Container akan otomatis:
- Menjalankan migrasi database
- Membersihkan dan me-*rebuild* cache config/route
- Membuat *symbolic link* untuk storage.

### 3. Setup (Jika belum generate app key)
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan config:cache
```

### 4. Import Data Awal (Opsional)
Untuk populate data dummy/master:
```bash
docker compose exec app php artisan db:seed
```

### Akses Aplikasi
Buka browser ke `http://localhost`. Semua traffic ke port 80 di-*handle* oleh container Nginx.
Default login (jika menjalankan seeder):
- Email: `admin@qc.com`
- Password: `(Lihat variabel SEED_ADMIN_PASSWORD di .env)`

---

## 🗄️ Caching & Optimization

Sistem ini menerapkan optimisasi *production-level*:
- **Session & Cache**: Disimpan di **Redis** untuk eliminasi *file I/O bottleneck*.
- **Query Optimization**: *Eager loading* (`with`) & agregasi agregat (1 SQL query alih-alih 21 di widget chart).
- **Cache Invalidations**: Widget cache otomatis di-*reset* saat ada submit form inspeksi baru.

---

## 🔒 Security

- `expose_php = Off` dan security headers diaktifkan via Nginx.
- Akses langsung ke `/.env` dan file *hidden* diblokir.
- Form inspeksi terlindungi dari manipulasi *request*: Inspector otomatis di-set ke akun yang login (`auth()->id()`).
- Data session tersandikan (`encrypt = true`) dan diamankan dengan `same_site = strict`.
