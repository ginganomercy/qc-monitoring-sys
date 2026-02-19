# 🛠️ ENGINEER.MD - Developer & Technical Guide

> **Comprehensive technical documentation for developers working on QC Monitoring System**

**Target Audience**: Backend developers, Junior programmers, DevOps engineers, Technical leads

---

## 📑 Table of Contents

1. [System Architecture](#-system-architecture)
2. [Development Environment Setup](#-development-environment-setup)
3. [Database Design & Optimization](#-database-design--optimization)
4. [Code Structure & Patterns](#-code-structure--patterns)
5. [Performance Optimization Guide](#-performance-optimization-guide)
6. [Testing Strategy](#-testing-strategy)
7. [Deployment Guide](#-deployment-guide)
8. [Troubleshooting](#-troubleshooting)
9. [Future Optimization Roadmap](#-future-optimization-roadmap)
10. [Junior Developer Onboarding](#-junior-developer-onboarding)

---

## 🏗️ System Architecture

### Technology Stack

```
┌─────────────────────────────────────────────┐
│           Frontend (Browser)                 │
│   Livewire Components + Tailwind CSS        │
└──────────────────┬──────────────────────────┘
                   │ HTTP/AJAX
┌──────────────────┴──────────────────────────┐
│         Application Layer (Laravel)          │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │   Filament Admin Panel (UI Layer)     │ │
│  │   ├── Resources (CRUD)                │ │
│  │   ├── Widgets (Dashboard)             │ │
│  │   └── Forms & Tables                  │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │   Business Logic Layer                │ │
│  │   ├── Models (Eloquent ORM)           │ │
│  │   ├── Policies (Authorization)        │ │
│  │   └── Services (if needed)            │ │
│  └────────────────────────────────────────┘ │
└──────────────────┬──────────────────────────┘
                   │ PDO/MySQL Driver
┌──────────────────┴──────────────────────────┐
│         Data Layer (MySQL 8.0)               │
│   ├── inspections (core table)               │
│   ├── products, lines, defect_types         │
│   ├── daily_targets                          │
│   └── users + permissions (RBAC)            │
└──────────────────────────────────────────────┘
```

### Request Flow

```
User Action (Browser)
  ↓
Livewire Component
  ↓
Filament Resource/Widget
  ↓
Eloquent Model
  ↓
Query Builder (with Indexes)
  ↓
MySQL Database
  ↓
← Results with Eager Loading
  ↓
← Transformed by Filament
  ↓
← Rendered to HTML
  ↓
User View (Browser)
```

---

## 💻 Development Environment Setup

### Prerequisites Checklist

```bash
# Check PHP version (must be >= 8.1)
php -v

# Check Composer
composer --version

# Check MySQL
mysql --version

# Check Node.js (optional for assets)
node -v
npm -v
```

### Step-by-Step Installation

#### 1. Clone & Install Dependencies

```bash
# Clone repository
git clone <repo-url> qc-monitoring
cd qc-monitoring

# Install PHP dependencies
composer install

# Install npm dependencies (optional)
npm install
```

#### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 3. Database Setup

**Option A: Local MySQL**

```bash
# Create database
mysql -u root -p
CREATE DATABASE qc_monitoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'qc_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON qc_monitoring.* TO 'qc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Option B: Docker MySQL**

```yaml
# docker-compose.yml
version: '3.8'
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: qc_monitoring
      MYSQL_ROOT_PASSWORD: root
      MYSQL_USER: qc_user
      MYSQL_PASSWORD: secure_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

```bash
docker-compose up -d
```

#### 4. Configure `.env`

```env
APP_NAME="QC Monitoring"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qc_monitoring
DB_USERNAME=qc_user
DB_PASSWORD=secure_password

# Cache & Session (use file for better performance)
CACHE_DRIVER=file
SESSION_DRIVER=file

# For production, use Redis for best performance
# CACHE_DRIVER=redis
# SESSION_DRIVER=redis

# Filament
FILAMENT_FILESYSTEM_DISK=public
```

#### 5. Run Migrations & Seeders

```bash
# Run migrations (creates tables)
php artisan migrate

# Optional: Run seeders (sample data)
php artisan db:seed

# Or fresh start (drop all + migrate + seed)
php artisan migrate:fresh --seed
```

#### 6. Create Admin User

```bash
# Interactive admin creation
php artisan make:filament-user

# Follow prompts:
# Name: Admin User
# Email: admin@example.com
# Password: password
```

#### 7. Start Development Server

```bash
# Start Laravel server
php artisan serve

# Access admin panel
http://127.0.0.1:8000/admin/login
```

#### 8. Optional: Compile Assets

```bash
# Development mode (watch for changes)
npm run dev

# Production build
npm run build
```

### IDE Setup (VS Code Recommended)

**Extensions**:
- PHP Intelephense
- Laravel Extension Pack
- Tailwind CSS IntelliSense
- Livewire Language Support

**settings.json**:
```json
{
  "php.suggest.basic": false,
  "intelephense.completion.fullyQualifyGlobalConstantsAndFunctions": true,
  "[blade]": {
    "editor.defaultFormatter": "shufo.vscode-blade-formatter"
  }
}
```

---

## 🗄️ Database Design & Optimization

### Schema Overview

```yaml
Core Tables:
  - inspections: Main transaction table (~600K records/year)
  - products: Product master (~1K records)
  - lines: Production line master (~10-50 records)
  - defect_types: Defect classification (~50-100 records)
  - components: Component master (~100-200 records)
  - daily_targets: Daily planning (~300 records/month)

Auth & RBAC:
  - users: Inspector accounts
  - roles: Permission roles (via Spatie)
  - permissions: Access control
  - model_has_roles: User-role pivot
  - model_has_permissions: Direct permissions
  - role_has_permissions: Role-permission pivot
```

### Critical Indexes (Performance)

**inspections table** (14 indexes total):

```sql
-- 1. Single column indexes
CREATE INDEX idx_inspection_date ON inspections(inspection_date);
CREATE INDEX idx_status ON inspections(status);
CREATE INDEX idx_created_at ON inspections(created_at);

-- 2. Foreign key indexes (auto-created)
CREATE INDEX idx_product_id ON inspections(product_id);
CREATE INDEX idx_line_id ON inspections(line_id);
CREATE INDEX idx_defect_type_id ON inspections(defect_type_id);
CREATE INDEX idx_component_id ON inspections(component_id);
CREATE INDEX idx_inspector_id ON inspections(inspector_id);

-- 3. Composite indexes (CRITICAL for performance)
CREATE INDEX idx_status_date ON inspections(status, inspection_date);
CREATE INDEX idx_line_date ON inspections(line_id, inspection_date);
```

**Why These Indexes?**

| Index | Used By | Benefit |
|-------|---------|---------|
| `idx_inspection_date` | All widgets, date filters | 90% faster date queries |
| `idx_status_date` | StatsOverview, pass rate calculations | Covers `WHERE status = X AND date = Y` |
| `idx_line_date` | Line performance reports | Covers `WHERE line_id = X AND date BETWEEN` |
| `idx_created_at` | `->latest()` queries | Fast ORDER BY created_at DESC |

### Query Optimization Examples

**❌ Before Optimization (N+1 Problem)**:
```php
// ProductResource table
Tables\Columns\TextColumn::make('inspections_count')
    ->counts('inspections'); // Triggers N+1!
    
// Result: 11+ queries for 10 products
```

**✅ After Optimization**:
```php
// ListProducts.php
public function getTableQuery(): Builder
{
    return Product::query()->withCount('inspections'); // 1 query!
}
```

**Performance Gain**: 82% reduction (11 queries → 2 queries)

---

**❌ Before: Inefficient Widget Query**:
```php
// StatsOverview.php - Loading ALL data into memory
$today = Inspection::whereDate('inspection_date', today())->get();
$totalToday = $today->count(); // PHP count on collection
$passedToday = $today->where('status', 'pass')->count(); // PHP filter
```

**✅ After: SQL Aggregation**:
```php
// Direct SQL COUNT queries
$totalToday = Inspection::whereDate('inspection_date', today())->count();
$passedToday = Inspection::whereDate('inspection_date', today())
    ->where('status', 'pass')
    ->count();
```

**Performance Gain**: 80% faster, 90% less memory

---

### Database Best Practices

1. **Always use Eloquent ORM** (prevents SQL injection)
2. **Index foreign keys** (improves JOIN performance)
3. **Use composite indexes** for multi-column WHERE clauses
4. **Avoid `SELECT *`** - specify columns needed
5. **Eager load relationships** (`with()`) to prevent N+1
6. **Use `whereHas()` for existence checks**, not `->has()->count()`

---

## 📦 Code Structure & Patterns

### Filament Resources

**Location**: `app/`
├── Filament/
│   ├── Resources/
│   │   ├── ProductResource.php
│   │   ├── LineResource.php
│   │   ├── InspectionResource.php       # CORE: with quantity fields
│   │   ├── DefectTypeResource.php
│   │   ├── ComponentResource.php
│   │   └── DailyTargetResource.php
│   └── Widgets/
│       ├── StatsOverviewWidget.php      # Dashboard KPI cards
│       ├── InspectionChart.php          # Daily inspection trend
│       ├── DefectChart.php              # Defect distribution
│       └── HourlyDefectTable.php        # Hourly analytics widget ✅
├── Exports/
│   ├── InspectionExport.php             # Excel export (Maatwebsite) ✅
│   └── InspectionPdfExport.php          # PDF export (DomPDF) ✅
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Line.php
│   ├── Inspection.php                   # CORE: quantity validation logic
│   ├── DefectType.php
│   ├── Component.php
│   └── DailyTarget.php
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php

**Anatomy of a Resource**:
```php
app/Filament/Resources/
└── ProductResource/
    ├── ProductResource.php          # Main resource definition
    └── Pages/
        ├── ListProducts.php         # List page (table)
        ├── CreateProduct.php        # Create form
        └── EditProduct.php          # Edit form
```

**Key Methods**:

```php
class ProductResource extends Resource
{
    // 1. Model binding
    protected static ?string $model = Product::class;
    
    // 2. Navigation
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;
    
    // 3. Form definition
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('style_number')->required()->unique(),
            Textarea::make('description'),
            Toggle::make('is_active')->default(true),
        ]);
    }
    
    // 4. Table definition
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('style_number')->searchable()->sortable(),
            TextColumn::make('inspections_count')->counts('inspections'),
            IconColumn::make('is_active')->boolean(),
        ]);
    }
}
```

### List Page Optimization (IMPORTANT!)

**Always override `getTableQuery()` for count columns**:

```php
// app/Filament/Resources/ProductResource/Pages/ListProducts.php
class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
    
    // ✅ CRITICAL: Eager load counts to prevent N+1
    public function getTableQuery(): Builder
    {
        return Product::query()->withCount([
            'inspections',
            // Add other counts here
        ]);
    }
}
```

### Filament Widgets

**Location**: `app/Filament/Widgets/`

**Types**:
1. **Stats Widget**: Number cards
2. **Chart Widget**: Line/bar/pie charts  
3. **Table Widget**: Data tables

**Example: Optimized Stats Widget**

```php
class StatsOverview extends BaseWidget
{
    // ✅ Enable lazy loading (non-blocking)
    protected static bool $isLazy = true;
    
    protected function getStats(): array
    {
        // ✅ Use SQL COUNT, not collection operations
        $totalToday = Inspection::whereDate('inspection_date', today())->count();
        
        $passedToday = Inspection::whereDate('inspection_date', today())
            ->where('status', 'pass')
            ->count();
        
        $passRate = $totalToday > 0 
            ? round(($passedToday / $totalToday) * 100, 1) 
            : 0;
        
        return [
            Stat::make('Total Today', $totalToday),
            Stat::make('Passed', $passedToday),
            Stat::make('Pass Rate', $passRate . '%'),
        ];
    }
}
```

**Example: Optimized Chart Widget**

```php
class TopDefectsChart extends ChartWidget
{
    protected static bool $isLazy = true;
    
    protected function getData(): array
    {
        // ✅ Use SQL GROUP BY, not PHP groupBy()
        $defects = Inspection::selectRaw('defect_type_id, COUNT(*) as count')
            ->where('status', 'reject')
            ->whereDate('inspection_date', '>=', now()->subDays(7))
            ->whereNotNull('defect_type_id')
            ->groupBy('defect_type_id')
            ->orderByDesc('count')
            ->limit(5)
            ->with('defectType:id,name')  // ✅ Eager load, selective columns
            ->get();
        
        return [
            'labels' => $defects->pluck('defectType.name'),
            'datasets' => [[
                'data' => $defects->pluck('count'),
            ]],
        ];
    }
}
```

### Model Relationships

**Best Practices**:

```php
class Inspection extends Model
{
    // ✅ Define inverse relationships for eager loading
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }
    
    // ✅ Nullable relationships
    public function defectType(): BelongsTo
    {
        return $this->belongsTo(DefectType::class);
    }
    
    // ✅ Scopes for common queries
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('inspection_date', today());
    }
    
    public function scopePassed(Builder $query): void
    {
        $query->where('status', 'pass');
    }
    
    public function scopeRejected(Builder $query): void
    {
        $query->where('status', 'reject');
    }
}

// Usage:
$todayPassed = Inspection::today()->passed()->count();
```

---

## ⚡ Performance Optimization Guide

### Current Performance Metrics (Post Sprint 10-11)

- **Page Load Time**: ~150-180ms (95% faster from 3s baseline)
- **Dashboard Refresh**: <300ms
- **Query Count**: Reduced from 13+ to 1-2 per page
- **Memory Usage**: 40% reduction via selective loading

### Optimization Levels

| Level | Complexity | Effort | Impact | Status | When to Apply |
|-------|-----------|--------|--------|--------|---------------|
| **Level 1: N+1 Fixes** | Low | 10 min | 70-80% | ✅ Done | **ALWAYS** |
| **Level 2: Query Optimization** | Medium | 20 min | 10-15% | ✅ Done | **ALWAYS** |
| **Level 3: Indexing** | Low | 10 min | 40-50% | ✅ Done | **ALWAYS** |
| **Level 4: Selective Loading** | Low | 15 min | 15-20% | ✅ Done | Recommended |
| **Level 4.5: Cache/Session Drivers** | Low | 5 min | 30-40% | ✅ Sprint 10 | **ALWAYS** |
| **Level 5: Caching** | Medium | 30 min | 80-95%* | ✅ Sprint 10 | High traffic only |
| **Level 6: Advanced** | High | 2+ hours | 5-10% | 🔄 Future | Enterprise scale |

*On cache hits

### Level 1: Fix N+1 Queries (CRITICAL)

**Problem Detection**:
```bash
# Enable query logging in .env
DB_LOG_QUERIES=true

# Or use Laravel Telescope
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Common N+1 Patterns**:

```php
// ❌ N+1: Loading counts without withCount
TextColumn::make('inspections_count')->counts('inspections')

// ✅ Fix in ListRecords page
public function getTableQuery(): Builder
{
    return Product::query()->withCount('inspections');
}
```

```php
// ❌ N+1: Loading relationships in loop
@foreach($inspections as $inspection)
    {{ $inspection->product->style_number }}  // N+1!
@endforeach

// ✅ Fix with eager loading
$inspections = Inspection::with('product')->get();
```

### Level 2: Optimize Widget Queries

**Rules**:
1. **Use SQL aggregation**, not PHP collection methods
2. **Use `selectRaw()` with COUNT/SUM/AVG**
3. **Use `groupBy()` at SQL level**
4. **Limit result sets early** (`->limit()`)

**Example**:
```php
// ❌ BAD: Loads all data into memory
$defects = Inspection::where('status', 'reject')
    ->with('defectType')
    ->get()
    ->groupBy('defect_type_id')
    ->map(fn($group) => $group->count())
    ->sortDesc()
    ->take(5);

// ✅ GOOD: SQL does the work
$defects = Inspection::selectRaw('defect_type_id, COUNT(*) as count')
    ->where('status', 'reject')
    ->groupBy('defect_type_id')
    ->orderByDesc('count')
    ->limit(5)
    ->with('defectType:id,name')
    ->get();
```

### Level 3: Database Indexing

**Index Strategy**:
1. Index ALL foreign keys
2. Index date columns used in filters
3. Index status/enum columns  
4. Create composite indexes for multi-column WHERE clauses

**When to Add Index**:
```sql
-- If query uses WHERE on column → Add index
WHERE inspection_date = '2026-02-05'  → INDEX(inspection_date)

-- If query uses WHERE with multiple columns → Composite index
WHERE status = 'pass' AND inspection_date = '...'  
→ INDEX(status, inspection_date)

-- If query uses ORDER BY → Index
ORDER BY created_at DESC  → INDEX(created_at)
```

**Migration Template**:
```php
public function up(): void
{
    Schema::table('inspections', function (Blueprint $table) {
        // Check if index exists first (production safety)
        if (!$this->indexExists('inspections', 'idx_inspection_date')) {
            $table->index('inspection_date', 'idx_inspection_date');
        }
    });
}

private function indexExists(string $table, string $index): bool
{
    $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
    return count($indexes) > 0;
}
```

### Level 4: Selective Column Loading

**Always specify columns explicitly**:

```php
// ❌ Loads ALL columns (wasteful)
Inspection::with(['product', 'line'])->get();

// ✅ Load only needed columns
Inspection::select([
    'id', 
    'inspection_date', 
    'status',
    'product_id',
    'line_id',
])->with([
    'product:id,style_number',
    'line:id,code',
])->get();
```

**Benefits**:
- 40% less memory usage
- 15-20% faster queries
- Smaller result sets

### Level 4.5: Cache & Session Drivers ✅ Sprint 10

**Optimization**: Changed from `database` to `file` drivers.

**Configuration** (`.env`):
```env
CACHE_DRIVER=file
SESSION_DRIVER=file
```

**Benefits**:
- **30-40% faster** than database driver
- Reduced database load
- Better concurrent user handling

**Production Recommendation**: Use Redis for best performance
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

### Level 5: Query Result Caching ✅ Sprint 10

**Implementation**: Centralized caching via `CacheHelper` class.

**CacheHelper.php**:
```php
namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    const TTL = 300; // 5 minutes
    
    public static function getResourceCount(string $model, string $key): int
    {
        return Cache::remember($key, self::TTL, function () use ($model) {
            return $model::count();
        });
    }
    
    public static function getWidgetStats(string $key, callable $callback)
    {
        return Cache::remember($key, self::TTL, $callback);
    }
}
```

**Usage in Resources**:
```php
// InspectionResource.php
public static function getNavigationBadge(): ?string
{
    return CacheHelper::getResourceCount(
        Inspection::class,
        'inspections.count'
    );
}
```

**Benefits**:
- Reduced database queries for navigation badges
- Faster dashboard widget rendering
- Centralized cache management

**Cache Invalidation**:
```php
// In Inspection model
protected static function booted()
{
    static::created(fn() => Cache::forget('inspections.count'));
    static::updated(fn() => Cache::forget('inspections.count'));
}
```

### Performance Checklist

Before deploying to production:

- [ ] All list pages use `withCount()` for relationship counts
- [ ] All widgets use SQL aggregation (not collection methods)
- [ ] All widgets have `protected static bool $isLazy = true`
- [ ] Foreign keys have indexes
- [ ] Date columns used in WHERE have indexes
- [ ] No `SELECT *` queries in critical paths
- [ ] Eager loading used for relationships
- [ ] No N+1 queries (check with Telescope)
- [ ] Cache driver configured (`file` for offline, Redis for networked)

---

## 🧪 Testing Strategy

### Test Structure

```
tests/
├── Feature/
│   ├── InspectionTest.php       # User flows
│   ├── ProductManagementTest.php
│   └── DashboardTest.php
│
└── Unit/
    ├── Models/
    │   ├── InspectionTest.php   # Model logic
    │   └── ProductTest.php
    └── Services/
```

### Writing Tests

**Feature Test Example**:
```php
// tests/Feature/InspectionTest.php
use Tests\TestCase;
use App\Models\{User, Product, Line, Inspection};

class InspectionTest extends TestCase
{
    public function test_inspector_can_create_pass_inspection(): void
    {
        $inspector = User::factory()->create();
        $product = Product::factory()->create();
        $line = Line::factory()->create();
        
        $this->actingAs($inspector)
            ->post(route('filament.resources.inspections.create'), [
                'inspection_date' => today(),
                'product_id' => $product->id,
                'line_id' => $line->id,
                'status' => 'pass',
            ])
            ->assertRedirect();
        
        $this->assertDatabaseHas('inspections', [
            'product_id' => $product->id,
            'status' => 'pass',
            'defect_type_id' => null,
        ]);
    }
    
    public function test_reject_inspection_requires_defect_type(): void
    {
        // Test validation...
    }
}
```

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/InspectionTest.php

# With coverage
php artisan test --coverage

# Parallel execution (faster)
php artisan test --parallel
```

---

## 🚀 Deployment Guide

### Production Checklist

**1. Environment Configuration (Offline/Local)**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://127.0.0.1:8085

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qc_monitoring
DB_USERNAME=root
DB_PASSWORD=your_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

**2. Optimization Commands**:
```bash
# Clear all caches
php artisan optimize:clear

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache  

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

**3. Database Migration**:
```bash
# Backup first!
mysqldump -u user -p db_name > backup_$(date +%Y%m%d).sql

# Run migrations
php artisan migrate --force

# Seed if needed
php artisan db:seed --force
```

**4. File Permissions**:
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

**5. Web Server Configuration**:

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/qc-monitoring/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**6. SSL Certificate** (Let's Encrypt):
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### Deployment Script

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

echo "✅ Deployment complete!"
```

---

## 🐛 Troubleshooting

### Common Issues

**1. "Class not found" error**
```bash
# Solution: Clear autoload cache
composer dump-autoload
php artisan clear-compiled
php artisan optimize:clear
```

**2. "SQLSTATE[42S02]: Base table or view not found"**
```bash
# Solution: Run migrations
php artisan migrate

# Or fresh install
php artisan migrate:fresh --seed
```

**3. Slow page load (>2 seconds)**
```bash
# Check for N+1 queries
composer require barryvdh/laravel-debugbar --dev

# Enable query logging
DB::enableQueryLog();
// ... your code
dd(DB::getQueryLog());
```

**4. "Permission denied" errors**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

**5. Widget not showing data**
- Check Filament widget registration in `Panel\Provider`
- Verify widget `$sort` property (lower number = higher priority)
- Check SQL query in widget (use `dd()` or `toSql()`)

### Debug Mode

**Enable detailed errors** (.env):
```env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

**View Logs**:
```bash
tail -f storage/logs/laravel.log
```

---

## 🗺️ Future Optimization Roadmap

### Phase 1: Completed ✅

- [x] N+1 query elimination
- [x] Widget query optimization
- [x] Database indexing
- [x] Selective column loading
- [x] Lazy widget loading

**Result**: 92% performance improvement (3000ms → 250ms)

---

### Phase 2: Next Steps (Q2 2026)

#### A. Advanced Pre-aggregated Reporting

**Objective**: Laporan bulanan/tahunan tanpa performance hit

**Implementation**:
1. Create `inspection_summaries` table (pre-aggregated data)
2. Daily cron job to aggregate (via Laravel scheduler)
3. Excel/PDF report enhancements: charts, summary statistics
4. Custom date-range reporting with filters

**Effort**: 4-6 hours

---

#### B. Automated Backup System

**Objective**: Zero-data-loss offline system

**Implementation**:
1. Spatie Laravel Backup package
2. Hourly MySQL dump + gzip compression
3. 7-day retention policy
4. Backup status dashboard widget

**Effort**: 2-3 hours

---

### Phase 3: Scale Planning (Q3-Q4 2026)

#### A. Network Update Distribution

**When**: System ready for multi-workstation support

**Actions**:
- Share update package via network share (UNC path)
- `version.json` manifest for version checking
- Automated update checker in application
- Rollback mechanism

---

#### B. Multi-Line Support

**When**: Multiple production lines need separate QC workstations

**Strategy**:
- Separate MySQL databases or schemas per line
- Central aggregation server
- Read replica for reporting

---

#### C. Performance Scaling

1. **Redis Cache** for high-frequency data
2. **Queue Workers** for report generation
3. **Database Partitioning** for inspections > 5M records
4. **Migration to PostgreSQL** (better GENERATED column support)

---

## 👨‍💻 Junior Developer Onboarding

### Week 1: Environment Setup & Basics

**Day 1-2: Setup**
- [ ] Install PHP 8.1, Composer, MySQL
- [ ] Clone repository
- [ ] Run migrations & seeders
- [ ] Login to admin panel
- [ ] Explore all pages (Products, Lines, Inspections, Dashboard)

**Day 3-4: Code Reading**
- [ ] Read `User` model - understand authentication
- [ ] Read `Product` model - understand basic CRUD
- [ ] Read `Inspection` model - understand relationships
- [ ] Read `ProductResource` - understand Filament structure

**Day 5: First Contribution**
- [ ] Add validation rule to Product description (max 500 chars)
- [ ] Test the change
- [ ] Create PR

### Week 2: Understanding Filament

**Learning Tasks**:
1. Modify `ProductResource` table - add new column
2. Modify `InspectionResource` form - change field order
3. Create new widget: "Total Products Count"
4. Add filter to Inspections: "This Week"

**Resources**:
- Filament Docs: https://filamentphp.com/docs
- Laravel Docs: https://laravel.com/docs
- Internal: `ENGINEER.md` (this file)

### Week 3-4: Database & Performance

**Learning Objectives**:
- Understand N+1 queries
- Practice with eager loading
- Learn indexing strategy
- Optimize a slow query

**Exercises**:
1. Find N+1 query using Telescope
2. Fix it with `withCount()` or `with()`
3. Add index to a column
4. Measure before/after performance

### Code Review Checklist for Juniors

Before submitting PR:
- [ ] No `SELECT *` in queries
- [ ] All relationships eager loaded
- [ ] Form validation complete
- [ ] No hardcoded values (use config/env)
- [ ] Code follows PSR-12 standard
- [ ] Tests written (if applicable)
- [ ] No `dd()` or `dump()` left in code
- [ ] Comments explain "why", not "what"

---

## 📚 Additional Resources

### Documentation
- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [Filament 3 Documentation](https://filamentphp.com/docs/3.x/panels/installation)
- [Spatie Permission](https://spatie.be/docs/laravel-permission/v5)

### Learning Paths
- [Laracasts - Laravel From Scratch](https://laracasts.com/series/laravel-10-from-scratch)
- [Filament Daily - Administrator Panel](https://www.youtube.com/c/FilamentDaily)

### Tools
- **Laravel Telescope**: Query debugging
- **Laravel Debugbar**: Performance profiling
- **PHPStan**: Static analysis
- **Laravel Pint**: Code formatting

---

## 📞 Getting Help

### Internal Team
- **Tech Lead**: technical decisions
- **Senior Dev**: code reviews, architecture
- **DevOps**: deployment, infrastructure

### External Resources
- [Laravel Discord](https://discord.gg/laravel)
- [Filament Discord](https://discord.gg/filamentphp)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)

---

## ✅ Final Notes

This system is designed for **performance**, **scalability**, and **maintainability**. 

**Key Principles**:
1. **Always optimize queries first** before adding caching
2. **Measure before optimizing** - profile to find bottlenecks
3. **Keep it simple** - premature optimization is evil
4. **Document as you go** - future you will thank you
5. **Test rigorously** - don't break production

**Current Status**: ✅ **Production Ready**
- Performance: 95% optimized (3000ms → 150ms)
- Export: Excel ✅ & PDF ✅ implemented
- Code Quality: Enterprise-grade
- Security: Laravel best practices applied
- Scalability: Optimized for single-workstation, 20K+ inspections/year

---

**Last Updated**: 2026-02-19  
**Version**: 1.2.0  
**Author**: Development Team  

🚀 **Happy Coding!**
