# 🔍 QC Monitoring System

> **Quality Control Monitoring & Inspection Management System**
> Built with Laravel 10 + Filament v3 + MySQL

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-yellow?style=flat-square)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue?style=flat-square)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📋 Overview

**QC Monitor** adalah aplikasi web untuk **tracking dan monitoring inspeksi kualitas produk** di production line dengan **interface 100% Bahasa Indonesia**. Sistem membantu tim QC mencatat hasil inspeksi, menganalisa defect, dan membuat keputusan berbasis data real-time.

- ✅ Record inspeksi produk (Pass/Reject)
- 📊 Monitor dashboard real-time
- 📈 Analisa defect berdasarkan tipe & severity
- 🎯 Track daily targets per line
- 🌐 UI Bahasa Indonesia (hybrid: technical terms tetap English)

---

## ✨ Key Features

### 1. Dashboard Analytics 📊
- 4 widget statistik: Total, Lolos, Ditolak, Pass Rate harian
- Grafik trend 7 hari terakhir
- Top 5 defect paling sering
- 10 inspeksi terakhir (lazy loading)

### 2. Master Data Management 🗂️
- **Produk**: Manage style numbers & deskripsi
- **Lines**: Manage production lines
- **Tipe Defect**: Klasifikasi dengan severity (low/medium/high/critical)
- **Komponen**: Komponen produk yang bisa terdefect
- **Target Harian**: Set target inspeksi per line per hari

### 3. QC Inspections 🔍
- Form cepat (<30 detik per inspeksi)
- Conditional fields berdasarkan status Pass/Reject
- Validasi: tidak bisa future date, reject wajib pilih defect type
- Inspector otomatis tercatat dari user yang login

### 4. Reporting & Analytics 📈
- Filter: date range, produk, line, status, inspector
- Search & sort di semua resource
- Pagination otomatis

---

## 🚀 Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | Laravel | 10.x |
| Admin Panel | Filament | 3.2+ |
| Database | MySQL | 8.0+ |
| Frontend | Livewire | 3.x |
| Styling | Tailwind CSS | 3.x |

---

## 📦 Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/ginganomercy/qc-monitoring-sys.git qc-monitoring
cd qc-monitoring
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qc_monitorr
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Migrations & Seed
```bash
php artisan migrate --seed
```

### 6. Start Development Server
```bash
php artisan serve
```

Akses: `http://127.0.0.1:8000/admin`

---

## 👥 Default Users (After Seeding)

| Email | Password | Nama |
|-------|----------|------|
| `admin@qc.com` | `tegal*2020` | Admin QC |
| `alisa2891@qc.com` | `tegal*2020` | Alisa (Inspector) |

> Semua user memiliki akses penuh. Role system belum diimplementasikan.

---

## 📁 Project Structure

```
qc-monitoring-system/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # CRUD resources (6 resources)
│   │   └── Widgets/            # Dashboard widgets (4 widgets)
│   ├── Helpers/
│   │   └── CacheHelper.php     # Centralized caching utility
│   └── Models/                 # Eloquent models
├── database/
│   ├── migrations/             # 8 migration files
│   └── seeders/               # UserSeeder, DefectTypeSeeder, dll
├── public/
│   └── images/                 # Logo & aset statis
│       ├── logo-poltek.png
│       └── qc-logo-dashboard.png
├── lang/id/                    # Terjemahan Bahasa Indonesia
├── .env.example
├── CLIENT.md                   # Dokumentasi untuk klien/stakeholder
├── ENGINEER.md                 # Dokumentasi teknis developer
├── SCHEMA.md                   # Detail skema database
└── NOTE.md                     # Catatan developer internal
```

---

## ⚡ Performance

| Metrik | Nilai |
|--------|-------|
| Page load | ~150–180ms |
| Dashboard refresh | <300ms |
| Query per halaman | 1–2 (dari 13+ sebelumnya) |
| Memory reduction | 40% |

**Optimizations**: N+1 elimination, DB indexing (14+ indexes), file-based cache & session, selective column loading, lazy widget loading.

---

## 🔐 Security

- ✅ CSRF Protection (Laravel default)
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ XSS Protection (Blade escaping)
- ✅ Password Hashing (bcrypt)
- ✅ Input Validation (form request)
- ✅ Rate limiting login

---

## 📈 Roadmap

### Selesai ✅
- [x] Core QC monitoring & dashboard
- [x] 100% UI Bahasa Indonesia
- [x] Performance optimization (95% improvement)
- [x] Database indexing & caching

### Planned 🔄
- [ ] Export ke Excel/PDF
- [ ] Email notifikasi defect kritis
- [ ] Role-based access control (admin vs inspector)
- [ ] Mobile-responsive improvements
- [ ] Redis cache untuk production

---

## 👨‍💻 Documentation

| File | Untuk | Isi |
|------|-------|-----|
| [README.md](README.md) | Semua | Overview & quick start |
| [CLIENT.md](CLIENT.md) | Klien/Stakeholder | Manfaat bisnis, fitur, harga |
| [ENGINEER.md](ENGINEER.md) | Developer | Setup, arsitektur, optimasi |
| [SCHEMA.md](SCHEMA.md) | Developer/DBA | Detail skema database |
| [NOTE.md](NOTE.md) | Developer | Catatan internal |

---

<p align="center">Made with ❤️ for Quality Control Teams</p>
