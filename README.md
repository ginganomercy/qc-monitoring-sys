# 🔍 QC Monitoring System

> **Quality Control Monitoring & Inspection Management System**  
> Built with Laravel 10 + Filament v3 + MySQL

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-yellow?style=flat-square)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue?style=flat-square)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?style=flat-square)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📋 Overview

QC Monitoring System adalah aplikasi berbasis web untuk **tracking dan monitoring** inspeksi kualitas produk di production line dengan **interface 100% Bahasa Indonesia**. Sistem ini membantu tim QC untuk:

- ✅ **Record inspeksi** produk (Pass/Reject)
- 📊 **Monitor dashboard** real-time kualitas produksi
- 📈 **Analisa defect** berdasarkan tipe dan severity
- 🎯 **Track daily targets** per line
- 👥 **User management** dengan role-based access control
- 🌐 **UI Bahasa Indonesia** dengan hybrid approach (strategic English retention)

---

## ✨ Key Features

### 1. Dashboard Analytics 📊
- **Stats Overview**: Total inspeksi, pass rate, reject rate harian
- **Inspection Chart**: Trend inspeksi 7 hari terakhir
- **Top Defects Chart**: 5 defect paling sering terjadi
- **Recent Inspections**: 10 inspeksi terakhir dengan detail
- **Lazy Loading**: Progressive rendering untuk performa optimal

### 2. Master Data Management 🗂️
- **Products**: Manage style numbers dan deskripsi produk
- **Lines**: Manage production lines
- **Defect Types**: Klasifikasi defect dengan severity (low/medium/high/critical)
- **Components**: Component produk yang dapat terdefect
- **Daily Targets**: Set target inspeksi harian per line

### 3. QC Inspections 🔍
- **Quick Inspection**: Form cepat dengan conditional fields
- **Pass/Reject**: Status otomatis menentukan field yang ditampilkan
- **Quantity Tracking**: Input total quantity, issue quantity; OK quantity dihitung otomatis
- **Defect Recording**: Capture tipe defect, komponen, issue type, dan notes
- **Hourly Tracking**: Pencatatan jam inspeksi untuk analitik per jam
- **Date Validation**: Prevent future dating
- **Inspector Tracking**: Automatic user assignment

### 4. Reporting & Analytics 📈
- Filter by date range, product, line, status
- **Export Excel**: Download laporan inspeksi dalam format `.xlsx` ✅
- **Export PDF**: Download laporan dalam format PDF ✅
- Search functionality across all resources
- Column sorting dan pagination
- **Hourly Defect Table**: Widget analitik defect per jam

### 5. User & Permissions 👥
- **Spatie Permission**: Role-based access control
- **Roles**: Super Admin, Admin, Inspector, Viewer
- **Authentication**: Filament built-in authentication
- **User Management**: Filament Shield integration

---

## 🚀 Tech Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Backend Framework** | Laravel | 10.x | Core application |
| **Admin Panel** | Filament | 3.2+ | UI & CRUD operations |
| **Database** | MySQL | 8.0+ | Data persistence |
| **Permissions** | Spatie Laravel Permission | 6.x | RBAC |
| **Frontend** | Livewire | 3.x | Reactive components |
| **UI Components** | Tailwind CSS | 3.x | Styling |
| **Excel Export** | Maatwebsite Excel | 3.x | .xlsx exports |
| **PDF Export** | barryvdh/laravel-dompdf | 2.x | PDF generation |

---

## 📊 Database Schema

Total **12 tables**: 
- Core tables: `inspections`, `products`, `lines`, `defect_types`, `components`, `daily_targets`
- Auth: `users`
- Permissions: `roles`, `permissions` + pivot tables

**Key Relationships**:
```
users ──┬──> inspections (inspector)
        └──> roles ──> permissions

products ──> inspections
lines ──┬──> inspections
        └──> daily_targets

defect_types ──> inspections
components ──> inspections
```

**inspections table — key columns**:
```
inspection_date, inspection_hour, product_id, line_id, status, inspector_id
total_quantity, issue_quantity, ok_quantity (STORED GENERATED), issue_type
defect_type_id (nullable), component_id (nullable), notes
```

📄 **Full Schema**: See [SCHEMA.md](SCHEMA.md)

---

## ⚡ Performance Optimizations

**Current Performance**: ~150-180ms page load time (**95% faster** from 3s baseline)

### Optimization Techniques Applied:

1. **N+1 Query Elimination** ✅
   - `withCount()` for relationships
   - Eager loading with `with()` for InspectionResource
   - Reduced queries from 13+ to 1-2 per page

2. **Cache & Session Optimization** ✅ *(Sprint 10)*
   - File-based cache driver (30-40% faster than database)
   - File-based session driver (15-20% faster)
   - `CacheHelper` class for centralized caching (5-min TTL)
   - Cached navigation badges and widget statistics

3. **Optimized Widget Queries** ✅
   - SQL `COUNT()` instead of collection filtering
   - SQL `GROUP BY` for aggregations
   - 80% faster widget rendering

4. **Database Indexing** ✅
   - 14+ indexes on `inspections` table
   - Composite indexes for common query patterns
   - 40-50% faster searches and filters

5. **Selective Column Loading** ✅
   - Load only required columns
   - 40% memory reduction
   - 15% faster queries

6. **Lazy Widget Loading** ✅
   - Progressive dashboard rendering
   - Non-blocking initial page load
   - Better perceived performance

7. **Composer Autoload Optimization** ✅
   - Optimized class map for faster autoloading
   - 5-10ms improvement per request

📄 **Performance Details**: See [Sprint 10-11 Walkthrough](.gemini/antigravity/brain/.../walkthrough.md)

---

## 🌐 Localization

**100% Indonesian UI** ✅ *(Sprint 11 Complete)*

- ✅ All navigation labels in Bahasa Indonesia
- ✅ 6 resources fully translated (Product, Line, Defect Type, Component, Daily Target, Inspection)
- ✅ 4 dashboard widgets translated
- ✅ All validation messages in Indonesian
- ✅ Hybrid approach: Strategic English retention for industry terms (Status, Inspector, QC, Line)
- ✅ Timezone: Asia/Jakarta
- ✅ Date/time formats: Indonesian locale

**Translation Files**:
- `lang/id/filament.php` - 70+ UI translations
- `lang/id/validation.php` - Complete Laravel validation rules

---

## 🔧 Requirements

- **PHP**: >= 8.1
- **Composer**: Latest
- **MySQL**: >= 8.0 (or MariaDB 10.3+)
- **Node.js**: >= 16.x (for asset compilation)
- **npm**: >= 8.x

---

## 📦 Quick Start

### 1. Clone Repository
```bash
git clone <repository-url> qc-monitoring
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
DB_DATABASE=qc_monitoring
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations
```bash
php artisan migrate --seed
```

### 6. Create Admin User
```bash
php artisan make:filament-user
```

### 7. Start Development Server
```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000/admin/login`

---

## 👥 Default Users (After Seeding)

| Email | Password | Role |
|-------|----------|------|
| `admin@example.com` | `password` | Super Admin |

---

## 📁 Project Structure

```
qc-monitoring-rebuild/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # CRUD resources
│   │   │   ├── ProductResource/
│   │   │   ├── LineResource/
│   │   │   ├── InspectionResource/
│   │   │   └── ...
│   │   └── Widgets/            # Dashboard widgets
│   │       ├── StatsOverview.php
│   │       ├── InspectionChart.php
│   │       ├── TopDefectsChart.php
│   │       └── RecentInspections.php
│   │
│   ├── Models/                 # Eloquent models
│   │   ├── User.php
│   │   ├── Inspection.php
│   │   ├── Product.php
│   │   └── ...
│   │
│   └── Policies/               # Authorization
│
├── database/
│   ├── migrations/             # Database schema
│   └── seeders/               # Sample data
│
├── resources/
│   └── views/                 # Blade templates (minimal)
│
└── routes/
    └── web.php                # Web routes
```

---

## 🎯 Usage Guide

### Creating an Inspection

1. Navigate to **Inspections** → **Create**
2. Select **Inspection Date**, **Product**, **Line**
3. Choose **Status**:
   - **Pass**: No further fields required
   - **Reject**: Select defect type & component
4. Add **Notes** (optional)
5. Click **Create**

### Viewing Dashboard

Dashboard shows real-time metrics:
- Total inspections today
- Pass rate percentage
- Top defects this week
- Recent 10 inspections
- 7-day inspection trend

### Setting Daily Targets

1. Navigate to **Daily Targets** → **Create**
2. Select **Line** and **Date**
3. Enter **Target Quantity**
4. Save

*Note: One target per line per date (unique constraint)*

---

## 🔐 Security Features

- ✅ **CSRF Protection**: Laravel default
- ✅ **SQL Injection Prevention**: Eloquent ORM
- ✅ **XSS Protection**: Blade template escaping
- ✅ **Password Hashing**: Bcrypt
- ✅ **Role-Based Access**: Spatie Permission
- ✅ **Input Validation**: Form Request validation

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
```

---

## 📈 Roadmap

### Completed ✅
- [x] **Multi-language support** - 100% Indonesian UI (Sprint 11)
- [x] **Query result caching** - CacheHelper implementation (Sprint 10)
- [x] **Performance optimization** - 95% improvement from baseline (Sprint 9-10)
- [x] **Export to Excel** - `InspectionExport` via Maatwebsite Excel (Sprint 12)
- [x] **Export to PDF** - barryvdh/laravel-dompdf integration (Sprint 12)
- [x] **Quantity tracking** - total/issue/ok quantity per inspection (Sprint 12)
- [x] **Hourly analytics** - `HourlyDefectTable` widget (Sprint 12)

### Planned
- [ ] Advanced reporting (pre-aggregated monthly/yearly summaries)
- [ ] Email notifications for critical defects
- [ ] Mobile responsive improvements
- [ ] Real-time dashboard updates (Livewire polling)
- [ ] Redis cache driver for production
- [ ] Production deployment optimization

---

## 🤝 Contributing

Contributions welcome! Please:
1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📝 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Developer Documentation

For detailed technical documentation, setup guides, and optimization strategies:

📘 **See [ENGINEER.md](ENGINEER.md) for:**
- Complete setup instructions
- Architecture deep-dive
- Database optimization guide
- Performance tuning checklist
- Junior developer onboarding guide
- Future enhancement roadmap

---

## 📞 Support

For questions or issues:
- 📧 Email: support@example.com
- 🐛 Issues: [GitHub Issues](https://github.com/yourorg/qc-monitoring/issues)
- 📖 Docs: [Wiki](https://github.com/yourorg/qc-monitoring/wiki)

---

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP Framework
- [Filament](https://filamentphp.com) - Beautiful Admin Panel
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - RBAC
- [Livewire](https://laravel-livewire.com) - Dynamic Components

---

<p align="center">Made with ❤️ for Quality Control Teams</p>
