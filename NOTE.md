# 📝 Developer Notes — QC Monitoring System

> Catatan internal developer. Bukan untuk klien.

**Last Updated**: 2026-05-20

---

## 🔄 Update Terakhir (2026-05-20) — Deployment Fixes

### Changes Made

1. **Database Schema**
   - Rename `inspector_id` → `user_id` di tabel `inspections` (migration `2026_05_13_000001`)
   - Tambah field `approved_by` dan `approved_at` untuk audit trail

2. **Docker Infrastructure**
   - `entrypoint.sh`: Ganti `mysqladmin ping` → **PHP PDO check** (kompatibel MySQL 8 `caching_sha2_password`)
   - `Dockerfile`: `COPY --chown=www-data:www-data` agar `storage:link` tidak `Permission denied`
   - `DatabaseSeeder`: Hapus `InspectionSeeder` — data contoh tidak diperlukan di production

3. **Services Directory**
   - `app/Services/BaseService.php` - Base class untuk semua services
   - `app/Services/ExportService.php` - Excel & PDF export
   - `app/Services/QueryOptimizerService.php` - Optimized dashboard queries

4. **Query Optimization**
   - Dashboard: 21+ queries → 2-3 queries
   - Widget chart: 3 queries → 1 query
   - Caching dengan 5 menit TTL (Redis)

5. **Export Integration**
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

| Akun        | Email                | Password                              |
|-------------|----------------------|---------------------------------------|
| Admin QC    | `admin@qc.com`       | Nilai `SEED_ADMIN_PASSWORD` di `.env` |

> **Catatan**: Semua user adalah admin. Ganti password setelah deploy ke production.
> Password seeder dikontrol lewat variabel `SEED_ADMIN_PASSWORD` di `.env` server.

---

## 🗃️ Database

- **Nama DB**: `qc_monitoring` (production Docker), `qc_monitoring` (local dev)
- **User**: `root` (local dev), `qc_user` (Docker production)
- **Engine**: MySQL 8.0+ (production), MariaDB 10.3+ (kompatibel local)

> ⚠️ Nama database yang benar adalah `qc_monitoring` (satu 'r'). Jangan pakai `qc_monitorr`.

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
| `Unknown column 'inspector_id'` | Sudah di-fix. Kolom direname ke `user_id` via migration `2026_05_13_000001`. Pull image terbaru. |
| `MySQL is unavailable - sleeping` (loop) | `mysqladmin` tidak support MySQL 8. Fix: PHP PDO check di `entrypoint.sh` (commit `e751702`) |
| `symlink(): Permission denied` | Fix: `COPY --chown=www-data` di Dockerfile (commit `fb3f784`) |
| Export Excel/PDF error | Cek paket `maatwebsite/excel` dan `barryvdh/laravel-dompdf` |
| Cache stale | `php artisan cache:clear` atau `docker compose exec app php artisan optimize` |

---

## 🚀 Deployment Checklist (Docker Production)

- [ ] Push kode ke `main` → tunggu GitHub Actions build selesai (hijau)
- [ ] Di server: `docker compose pull` untuk tarik image terbaru
- [ ] Di server: `docker compose down && docker compose up -d`
- [ ] Cek log: `docker compose logs app --tail=20` — pastikan `MySQL is up and running`
- [ ] Jika fresh install: seed data master per class (lihat README)
- [ ] `docker compose exec app php artisan optimize`
- [ ] Verifikasi login di domain
- [ ] Test form inspeksi (create, pass, reject)
- [ ] Test export Excel & PDF
- [ ] Verify dashboard widgets berjalan