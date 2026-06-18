# QC Monitoring System

Sistem manajemen *Quality Control* berbasis Laravel 10 dan Filament 3.
Dirancang khusus untuk monitoring produksi pabrik, termasuk pelacakan *defect*, *daily target*, dan kinerja lini produksi.

> **Mode**: Admin-Only — Tidak ada role inspector. Semua input dilakukan oleh admin.

---

## 🚀 Fitur Utama

### 1. Dashboard Real-Time
- Statistik inspeksi hari ini (total, lolos, ditolak, pass rate)
- Grafik sparkline performa 7 hari terakhir
- Top 5 defect dengan prioritas warna
- Inspeksi terbaru (10 terakhir)

### 2. Manajemen QC
- Input form inspeksi dinamis (Pass/Reject)
- Detail komponen & jenis *defect*
- Validasi otomatis (reject wajib isi defect type)
- `user_id` otomatis diset dari admin yang login

### 3. Data Master
- Kelola Style Produk
- Line Produksi
- Jenis Defect (severity: low, medium, high, critical)
- Komponen
- Target Harian

### 4. Export Laporan
- **Excel**: Export data inspeksi dengan filter (tanggal, produk, line, status)
- **PDF**: Laporan profesional dengan summary statistik

### 5. Optimasi Performa
- Query optimization (21+ query → 2-3 query di dashboard)
- Caching dengan Redis (5 menit TTL)
- Lazy loading widgets

---

## 🛠️ Requirements

| Software | Versi |
|----------|-------|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Redis | 7.0+ |
| Composer | 2.0+ |
| Node.js | 16+ |
| Docker & Docker Compose | (untuk deployment) |

---

## 📦 Installation (Local Development)

### 1. Clone Repository
```bash
git clone https://github.com/ginganomercy/qc-monitoring-sys.git
cd qc-monitoring-sys
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database
Buat database `qc_monitoring` di MySQL, lalu edit `.env`:
```env
DB_DATABASE=qc_monitoring
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Jalankan Migrasi & Seeder
```bash
php artisan migrate --seed
```

### 6. Start Server
```bash
php artisan serve
# Akses: http://localhost:8000/admin
```

---

## 🐳 Docker Deployment (Production)

### 1. Setup Environment
```bash
cp .env.example .env
```

File `.env` production — **tanpa komentar inline pada baris DB_***:
```env
APP_NAME="QC Monitor"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=qc_monitoring
DB_USERNAME=qc_user
DB_PASSWORD=your_strong_password

SEED_ADMIN_PASSWORD=your_admin_password
```

> ⚠️ Jangan tambahkan komentar `# ...` pada baris yang sama dengan nilai variabel DB. Ini akan menyebabkan credentials gagal dibaca.

### 2. Start Containers
```bash
docker compose up -d
# Entrypoint otomatis: cek koneksi DB (PDO), migrate, config cache, storage:link
```

### 3. Seed Data Master (sekali saja, saat pertama deploy)
```bash
docker compose exec app php artisan db:seed --class=UserSeeder --force
docker compose exec app php artisan db:seed --class=ProductSeeder --force
docker compose exec app php artisan db:seed --class=LineSeeder --force
docker compose exec app php artisan db:seed --class=DefectTypeSeeder --force
docker compose exec app php artisan db:seed --class=ComponentSeeder --force
docker compose exec app php artisan db:seed --class=DailyTargetSeeder --force
docker compose exec app php artisan optimize
```

> ℹ️ `InspectionSeeder` sengaja dinonaktifkan — hanya data contoh, tidak diperlukan di production.

### 4. Update Deployment (setelah push ke GitHub)
```bash
docker compose pull
docker compose down
docker compose up -d
```

### 5. Akses Aplikasi
Buka browser ke domain yang dikonfigurasi di `APP_URL`.

---

## 🔐 Default Login

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@qc.com` | Nilai `SEED_ADMIN_PASSWORD` di `.env` |

> ⚠️ **Penting**: Ganti password setelah deploy ke production.

---

## 📊 Database Schema

```
users (admin accounts)
  └── inspections (N:1) → user_id   ← direname dari inspector_id (v1.2)

products (master data)
  └── inspections (N:1)

lines (production lines)
  ├── inspections (N:1)
  └── daily_targets (N:1)

defect_types (severity levels)
  └── inspections (N:1) [nullable]

components (product parts)
  └── inspections (N:1) [nullable]
```

**Total Tables**: 7
**Total Indexes**: 25+

---

## 📁 Project Structure

```
app/
├── Filament/
│   ├── Resources/        # CRUD Resources (Product, Line, Defect, Inspection, dll)
│   └── Widgets/          # Dashboard widgets
├── Models/               # Eloquent models
├── Services/             # Business logic (ExportService, QueryOptimizerService)
├── Exports/              # Excel export classes
└── Helpers/              # CacheHelper

database/
├── migrations/           # Database schema
└── seeders/              # Data master (InspectionSeeder dinonaktifkan)

docker/
├── php/
│   ├── Dockerfile        # PHP 8.2-fpm-alpine + composer + extensions
│   ├── entrypoint.sh     # Boot script: PDO health check → migrate → cache → fpm
│   └── php.ini
└── nginx/
    ├── Dockerfile
    └── default.conf

.github/workflows/
└── qcdeploy.yml          # CI/CD: pint → build → push GHCR → deploy

resources/views/reports/  # Export templates (Excel, PDF)
```

---

## ⚙️ CI/CD Pipeline

GitHub Actions (`.github/workflows/qcdeploy.yml`):

1. **Code Quality** — `./vendor/bin/pint --test` (linter wajib pass)
2. **Build & Push** — Docker image → GHCR (`latest` + commit SHA)
3. **Deploy** — SSH ke server → `docker compose pull && docker compose up -d`

Image: `ghcr.io/ginganomercy/qc-monitoring-sys:latest`

---

## 📈 Performance

| Metrik | Nilai |
|--------|-------|
| Page Load | ~150-180ms |
| Query per Dashboard | 2-3 (dari 21+ sebelumnya) |
| Caching | Redis, 5 menit TTL |

---

## 🔒 Security Features

- Form inspeksi auto-set `user_id` ke admin yang login
- Session encryption & CSRF protection
- Rate limiting pada login
- Security headers (X-Frame-Options, HSTS, etc.)
- Document root pointing ke `/public`

---

## 🛠️ Troubleshooting

| Error | Solusi |
|-------|--------|
| `Access denied` | Cek `DB_USERNAME`/`DB_PASSWORD` di `.env` — pastikan tidak ada komentar inline |
| `MySQL is unavailable - sleeping` (loop panjang) | Sudah di-fix via PHP PDO check di `entrypoint.sh` (commit `e751702`) |
| `symlink(): Permission denied` | Sudah di-fix via `COPY --chown=www-data` di Dockerfile (commit `fb3f784`) |
| `Unknown column 'inspector_id'` | Sudah direname ke `user_id`. Pastikan image dari GHCR adalah yang terbaru |
| `Class not found` | Jalankan `composer dump-autoload` |
| Cache stale | `php artisan cache:clear` atau `php artisan optimize` |

---

## 📞 Support

- **GitHub**: [ginganomercy/qc-monitoring-sys](https://github.com/ginganomercy/qc-monitoring-sys)
- **Dokumentasi Teknis**: [ENGINEER.md](ENGINEER.md), [SCHEMA.md](SCHEMA.md)
- **Panduan Klien**: [CLIENT.md](CLIENT.md)
- **Catatan Dev**: [NOTE.md](NOTE.md)

---

<p align="center">
  <strong>Quality is not an act, it is a habit.</strong><br>
  — Aristotle
</p>