# 📝 Developer Notes — QC Monitoring System

> Catatan internal developer. Bukan untuk klien.

**Last Updated**: 2026-05-13

---

## 🔄 Spring 13 Update (Admin-Only Mode)

### Changes Made

1. **Database Schema**
   - Rename `inspector_id` → `user_id` di tabel `inspections`
   - Tambah field `approved_by` dan `approved_at` untuk audit trail

2. **Services Directory**
   - `app/Services/BaseService.php` - Base class untuk semua services
   - `app/Services/ExportService.php` - Excel & PDF export
   - `app/Services/QueryOptimizerService.php` - Optimized dashboard queries

3. **Query Optimization**
   - Dashboard: 7 queries → 2 queries (perbaikan 70%)
   - Widget chart: 3 queries → 1 query
   - Caching dengan 5 menit TTL

4. **Export Integration**
   - Excel: Filter by date, product, line, status
   - PDF: Professional report dengan summary statistik

---

## ✅ Security Hardening Checklist

### Phase 1 — CRITICAL (Selesai)

- [x] Disable debug mode (`APP_DEBUG=false` di production)
- [x] Force HTTPS via `.htaccess`
- [x] Tambah security headers (X-Frame-Options, HSTS, dll)
- [x] Ganti DB credentials default
- [x] Rate limiting pada login
- [x] Secure session config (`SESSION_DRIVER=file`)
- [x] Admin-only mode (auto-set user_id on inspection)

### Phase 2 — HIGH (Target)

> Estimasi effort: 4–6 jam

- [x] Query optimization (7→2 queries) ✅ Selesai
- [ ] Audit logging (siapa ubah apa, kapan)
- [ ] Session timeout warning (auto-logout)
- [ ] Konfigurasi firewall server
- [ ] Setup monitoring & alerting

### Phase 3 — MEDIUM (Nice to Have)

> Estimasi effort: 8–10 jam

- [ ] Two-factor authentication (2FA)
- [ ] Advanced audit log dengan filter
- [ ] Automated security scanning (CI pipeline)
- [ ] Penetration testing

---

## 🔐 Default Credentials (Seeder)

| Akun        | Email                | Password     |
|-------------|----------------------|--------------|
| Admin QC    | `admin@qc.com`       | `tegal*2020` |

> **Catatan**: Semua user adalah admin. Ganti password setelah deploy ke production.

---

## 🗃️ Database

- **Nama DB**: `qc_monitorr`
- **User**: `root` (local dev), ganti ke dedicated user di production
- **Engine**: MySQL 8.0+ / MariaDB 10.3+

### Tables

| Table | Purpose |
|-------|---------|
| `users` | Admin accounts |
| `products` | Master produk |
| `lines` | Production lines |
| `defect_types` | Jenis defect dengan severity |
| `components` | Komponen produk |
| `daily_targets` | Target harian per line |
| `inspections` | Core transaksi inspeksi |

---

## 📁 Aset Statis

| File                    | Lokasi                          | Digunakan di                    |
|-------------------------|---------------------------------|---------------------------------|
| `logo-poltek.png`       | `public/images/`                | `AdminPanelProvider` (brandLogo)|
| `qc-logo-dashboard.png` | `public/images/`                | Siap digunakan                  |
| `favicon.ico`           | `public/`                       | Semua halaman                   |

---

## 🔗 Repository

- **GitHub**: `https://github.com/ginganomercy/qc-monitoring-sys`
- **Akun**: `ginganomercy` (rafly1955@gmail.com)
- **Branch utama**: `main`

---

## 📊 Performance Metrics

| Metric | Before | After |
|--------|--------|-------|
| Dashboard queries | 21+ | 2-3 |
| Page load | ~3s | ~150-180ms |
| Memory usage | High | 40% reduction |
| Cache hit | N/A | 5 min TTL |

---

## 🛠️ Troubleshooting

| Error | Solution |
|-------|----------|
| `Class QueryOptimizerService not found` | Jalankan `composer dump-autoload` |
| `Table 'inspections' doesn't have 'user_id'` | Jalankan `php artisan migrate` |
| Export Excel/PDF error | Cek paket `maatwebsite/excel` dan `barryvdh/laravel-dompdf` |
| Cache stale | `php artisan cache:clear` |

---

## 🚀 Deployment Checklist

- [ ] Jalankan migration baru (`php artisan migrate`)
- [ ] Clear cache (`php artisan cache:clear`)
- [ ] Rebuild config (`php artisan config:cache`)
- [ ] Test export Excel & PDF
- [ ] Verify dashboard widgets
- [ ] Push ke GitHub dan pull di server production