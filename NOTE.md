# 📝 Developer Notes — QC Monitoring System

> Catatan internal developer. Bukan untuk klien.

**Last Updated**: 2026-03-02

---

## ✅ Security Hardening Checklist

### Phase 1 — CRITICAL (Selesai)

- [x] Disable debug mode (`APP_DEBUG=false` di production)
- [x] Force HTTPS via `.htaccess`
- [x] Tambah security headers (X-Frame-Options, HSTS, dll)
- [x] Ganti DB credentials default
- [x] Rate limiting pada login
- [x] Secure session config (`SESSION_DRIVER=file`)

### Phase 2 — HIGH (Target)

> Estimasi effort: 4–6 jam

- [ ] Input validation & sanitization tambahan
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
| Inspector   | `alisa2891@qc.com`   | `tegal*2020` |

> **Catatan**: Ganti password setelah deploy ke production.

---

## 🗃️ Database

- **Nama DB**: `qc_monitorr`
- **User**: `root` (local dev), ganti ke dedicated user di production
- **Engine**: MySQL 8.0+ / MariaDB 10.3+

---

## 📁 Aset Statis

| File                    | Lokasi                          | Digunakan di                    |
|-------------------------|---------------------------------|---------------------------------|
| `logo-poltek.png`       | `public/images/`                | `AdminPanelProvider` (brandLogo)|
| `qc-logo-dashboard.png` | `public/images/`                | Siap digunakan (belum terpasang)|
| `favicon.ico`           | `public/`                       | Semua halaman                   |

---

## 🔗 Repository

- **GitHub**: `https://github.com/ginganomercy/qc-monitoring-sys`
- **Akun**: `ginganomercy` (rafly1955@gmail.com)
- **Branch utama**: `main`