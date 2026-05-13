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
- Query optimization (7 query → 2 query di dashboard)
- Caching dengan Redis
- Lazy loading widgets

---

## 🛠️ Requirements

| Software | Versi |
|----------|-------|
| PHP | 8.1+ |
| MySQL | 8.0+ |
| Redis | 7.0+ (opsional, file cache fallback) |
| Composer | 2.0+ |
| Node.js | 16+ |

---

## 📦 Installation

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
Buat database `qc_monitorr` di MySQL, lalu edit `.env`:
```env
DB_DATABASE=qc_monitorr
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Jalankan Migrasi & Seeder
```bash
php artisan migrate
php artisan db:seed
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
# Edit .env dengan password yang kuat
```

### 2. Start Containers
```bash
docker compose up -d
```

### 3. Setup (sekali saja)
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan storage:link
```

### 4. Akses Aplikasi
Buka browser ke `http://localhost`

---

## 🔐 Default Login

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@qc.com` | `tegal*2020` |

> ⚠️ **Penting**: Ganti password setelah deploy ke production.

---

## 📊 Database Schema

```
users (admin accounts)
  └── inspections (N:1) → user_id

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
│   ├── Resources/        # CRUD Resources (Product, Line, Defect, etc.)
│   └── Widgets/           # Dashboard widgets
├── Models/               # Eloquent models
├── Services/             # Business logic (ExportService, QueryOptimizerService)
├── Exports/              # Excel export classes
└── Helpers/              # CacheHelper

database/
├── migrations/           # Database schema
└── seeders/              # Sample data

resources/views/reports/  # Export templates (Excel, PDF)
```

---

## 📈 Performance

| Metrik | Nilai |
|--------|-------|
| Page Load | ~150-180ms |
| Query per Dashboard | 2-3 (dari 21+ sebelumnya) |
| Caching | 5 menit TTL |

---

## 🔒 Security Features

- Form inspeksi auto-set `user_id` ke admin yang login
- Session encryption & CSRF protection
- Rate limiting pada login
- Security headers (X-Frame-Options, HSTS, etc.)
- Document root pointing ke `/public`

---

## 📝 Export Fitur

### Excel Export
Filter tersedia:
- Tanggal (start_date, end_date)
- Produk
- Line
- Status (pass/reject)

### PDF Export
- Header dengan logo
- Summary statistik
- Table data dengan styling profesional

---

## 🛠️ Troubleshooting

| Error | Solusi |
|-------|--------|
| `Access denied` | Cek `DB_USERNAME` dan `DB_PASSWORD` di `.env` |
| `Unknown database` | Buat database `qc_monitorr` di MySQL |
| `Class not found` | Jalankan `composer dump-autoload` |
| Cache stale | `php artisan cache:clear` |

---

## 📞 Support

- **GitHub**: [ginganomercy/qc-monitoring-sys](https://github.com/ginganomercy/qc-monitoring-sys)
- **Dokumentasi**: ENGINEER.md, SCHEMA.md, CLIENT.md

---

<p align="center">
  <strong>Quality is not an act, it is a habit.</strong><br>
  — Aristotle
</p>