# 🛠️ ENGINEER.md — Technical Guide

> **Dokumentasi teknis lengkap untuk developer QC Monitoring System**

**Target Audience**: Backend developer, junior programmer, DevOps, tech lead

---

## 📑 Table of Contents

1. [System Architecture](#-system-architecture)
2. [Development Setup](#-development-setup)
3. [Database Design & Optimization](#-database-design--optimization)
4. [Code Structure & Patterns](#-code-structure--patterns)
5. [Performance Optimization](#-performance-optimization)
6. [Deployment Guide](#-deployment-guide)
7. [Troubleshooting](#-troubleshooting)

---

## 🏗️ System Architecture

### Technology Stack

```
┌────────────────────────────────────────┐
│         Frontend (Browser)             │
│   Livewire Components + Tailwind CSS  │
└──────────────┬─────────────────────────┘
               │ HTTP / Livewire AJAX
┌──────────────┴─────────────────────────┐
│       Application Layer (Laravel)      │
│  ┌──────────────────────────────────┐  │
│  │   Filament Admin Panel (UI)     │  │
│  │   ├── Resources (CRUD)          │  │
│  │   ├── Widgets (Dashboard)       │  │
│  │   └── Forms & Tables            │  │
│  └──────────────────────────────────┘  │
│  ┌──────────────────────────────────┐  │
│  │   Business Logic                │  │
│  │   ├── Models (Eloquent ORM)     │  │
│  │   └── Helpers (CacheHelper)     │  │
│  └──────────────────────────────────┘  │
└──────────────┬─────────────────────────┘
               │ PDO / MySQL Driver
┌──────────────┴─────────────────────────┐
│         Data Layer (MySQL 8.0)         │
│   ├── inspections (core)               │
│   ├── products, lines, defect_types   │
│   ├── components, daily_targets        │
│   └── users                           │
└────────────────────────────────────────┘
```

### Request Flow

```
User Action → Livewire → Filament Resource/Widget
   → Eloquent Model → Query Builder (Indexed)
   → MySQL → Eager Loading → Filament Render
   → HTML → User
```

---

## 💻 Development Setup

### Prerequisites

```bash
php -v          # >= 8.1
composer -V
mysql --version # >= 8.0
node -v         # >= 16.x
```

### Step-by-Step

#### 1. Clone & Dependensi

```bash
git clone https://github.com/ginganomercy/qc-monitoring-sys.git qc-monitoring
cd qc-monitoring
composer install
npm install
```

#### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

#### 3. Database

```sql
-- Buat database
CREATE DATABASE qc_monitorr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edit `.env`:

```env
APP_NAME="QC Monitor"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qc_monitorr
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file

FILAMENT_FILESYSTEM_DISK=public
```

#### 4. Migrate & Seed

```bash
php artisan migrate --seed
# Atau fresh start:
php artisan migrate:fresh --seed
```

#### 5. Jalankan Server

```bash
php artisan serve
# Akses: http://127.0.0.1:8000/admin
```

### Default Credentials (Seeder)

| Email | Password | Nama |
|-------|----------|------|
| `admin@qc.com` | `tegal*2020` | Admin QC |
| `alisa2891@qc.com` | `tegal*2020` | Alisa |

> Semua user memiliki akses yang sama. Tidak ada role system saat ini.

### IDE (VS Code)

**Extensions yang direkomendasikan**:
- PHP Intelephense
- Laravel Extension Pack
- Tailwind CSS IntelliSense
- Livewire Language Support

---

## 🗄️ Database Design & Optimization

### Schema Overview

```yaml
Core Tables:
  - inspections: Transaksi utama (~600K record/tahun)
  - products:    Master produk (~1K record)
  - lines:       Master production line (~10–50 record)
  - defect_types: Klasifikasi defect (~50–100 record)
  - components:  Master komponen (~100–200 record)
  - daily_targets: Target harian (~300 record/bulan)

Auth:
  - users: Akun inspector (tanpa role system)
```

> ⚠️ **Tidak ada** tabel Spatie Permission (roles, permissions, pivot). RBAC belum diimplementasikan.

### Critical Indexes

**Tabel `inspections`** — 14 indexes:

```sql
-- Single column
INDEX idx_inspection_date (inspection_date)
INDEX idx_status          (status)
INDEX idx_created_at      (created_at)

-- Foreign keys
INDEX idx_product_id      (product_id)
INDEX idx_line_id         (line_id)
INDEX idx_defect_type_id  (defect_type_id)
INDEX idx_component_id    (component_id)
INDEX idx_inspector_id    (inspector_id)

-- Composite (CRITICAL untuk dashboard)
INDEX idx_status_date     (status, inspection_date)
INDEX idx_line_date       (line_id, inspection_date)
```

### N+1 Anti-Pattern

```php
// ❌ SALAH — trigger N+1
TextColumn::make('inspections_count')->counts('inspections');

// ✅ BENAR — override di ListRecords
public function getTableQuery(): Builder
{
    return Product::query()->withCount('inspections'); // 1 query
}
```

### Widget Query Pattern

```php
// ❌ BURUK — load semua ke memori PHP
$today = Inspection::whereDate('inspection_date', today())->get();
$pass  = $today->where('status', 'pass')->count(); // PHP filter

// ✅ BAGUS — aggregasi di SQL
$pass = Inspection::whereDate('inspection_date', today())
    ->where('status', 'pass')
    ->count(); // 1 query, ~5ms
```

---

## 📦 Code Structure & Patterns

### Filament Resource Anatomy

```
app/Filament/Resources/
└── ProductResource/
    ├── ProductResource.php        # Definisi resource utama
    └── Pages/
        ├── ListProducts.php       # Tabel + override getTableQuery()
        ├── CreateProduct.php      # Form create
        └── EditProduct.php        # Form edit
```

### Resource Template

```php
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $label           = 'Produk';
    protected static ?string $pluralLabel     = 'Produk';
    protected static ?int    $navigationSort  = 1;
    
    public static function form(Form $form): Form { ... }
    public static function table(Table $table): Table { ... }
}
```

### Widget Pattern (Lazy + SQL Aggregation)

```php
class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = true; // Non-blocking render

    protected function getStats(): array
    {
        $total = Inspection::whereDate('inspection_date', today())->count();
        $pass  = Inspection::whereDate('inspection_date', today())
                    ->where('status', 'pass')->count();
        $rate  = $total > 0 ? round($pass / $total * 100, 1) : 0;

        return [
            Stat::make('Total Hari Ini', $total),
            Stat::make('Lolos', $pass),
            Stat::make('Tingkat Kelulusan', $rate . '%'),
        ];
    }
}
```

### CacheHelper

```php
// app/Helpers/CacheHelper.php
class CacheHelper
{
    const TTL = 300; // 5 menit

    public static function getResourceCount(string $model, string $key): int
    {
        return Cache::remember($key, self::TTL, fn() => $model::count());
    }
}

// Penggunaan di Resource:
public static function getNavigationBadge(): ?string
{
    return CacheHelper::getResourceCount(Inspection::class, 'inspections.count');
}
```

### Model Scopes

```php
class Inspection extends Model
{
    public function scopeToday(Builder $q): void
    {
        $q->whereDate('inspection_date', today());
    }
    
    public function scopePassed(Builder $q): void  { $q->where('status', 'pass'); }
    public function scopeRejected(Builder $q): void { $q->where('status', 'reject'); }
}

// Usage:
$passed = Inspection::today()->passed()->count();
```

---

## ⚡ Performance Optimization

### Current Metrics

| Metrik | Nilai |
|--------|-------|
| Page load | ~150–180ms (dari 3s baseline) |
| Query per halaman | 1–2 (dari 13+) |
| Memory reduction | 40% |

### Optimization Levels

| Level | Teknik | Impact | Status |
|-------|--------|--------|--------|
| 1 | N+1 elimination (withCount, eager load) | 70–80% | ✅ Done |
| 2 | SQL aggregation (COUNT, GROUP BY) | 10–15% | ✅ Done |
| 3 | Database indexing (14+ indexes) | 40–50% | ✅ Done |
| 4 | Selective column loading | 15–20% | ✅ Done |
| 4.5 | File-based cache & session | 30–40% | ✅ Done |
| 5 | Query result caching (CacheHelper) | 80–95%* | ✅ Done |
| 6 | Redis, queue, advanced | 5–10% | 🔄 Future |

*pada cache hit

### Production: Gunakan Redis

```env
# .env production
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

---

## 🚀 Deployment Guide

### Shared Hosting / VPS

```bash
# 1. Upload files (exclude: /vendor, /node_modules, .env)
# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Setup .env production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# 4. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# 5. Migrate
php artisan migrate --force

# 6. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### .htaccess (sudah ada di `public/`)

```apache
Options -MultiViews -Indexes
RewriteEngine On
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

---

## 🔧 Troubleshooting

### Masalah Umum

| Error | Penyebab | Solusi |
|-------|----------|--------|
| `php_network_getaddresses` | DB host salah | Cek `DB_HOST` di `.env` |
| `Access denied for user` | Credentials salah | Cek `DB_USERNAME` / `DB_PASSWORD` |
| `Unknown database` | DB belum dibuat | Jalankan `CREATE DATABASE qc_monitorr` |
| `Class not found` | Autoload stale | Jalankan `composer dump-autoload` |
| Logo tidak muncul | Path salah | Pastikan file ada di `public/images/` |
| Cache stale | Data tidak update | `php artisan cache:clear` |

### Reset Ulang Dev Environment

```bash
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
php artisan view:clear
composer dump-autoload
```

### Reset Password via Tinker

```bash
php artisan tinker
>>> \App\Models\User::where('email','admin@qc.com')
       ->update(['password' => bcrypt('password_baru')]);
```

---

## 📈 Future Roadmap

| Fitur | Prioritas | Estimasi |
|-------|-----------|----------|
| Role-based access (admin vs inspector) | High | 1–2 hari |
| Export Excel / PDF | High | 2–3 hari |
| Email notifikasi defect kritis | Medium | 1–2 hari |
| Redis cache (production) | Medium | 0.5 hari |
| Real-time dashboard (polling) | Low | 1 hari |
| Native mobile app | Low | 2–4 minggu |
| ERP integration | Future | Custom |

---

## 🔗 References

| Dokumen | Link |
|---------|------|
| README | [README.md](README.md) |
| Schema DB | [SCHEMA.md](SCHEMA.md) |
| Panduan Klien | [CLIENT.md](CLIENT.md) |
| Catatan Dev | [NOTE.md](NOTE.md) |
| Repo GitHub | [ginganomercy/qc-monitoring-sys](https://github.com/ginganomercy/qc-monitoring-sys) |
| Laravel Docs | [laravel.com/docs/10.x](https://laravel.com/docs/10.x) |
| Filament Docs | [filamentphp.com/docs](https://filamentphp.com/docs) |
