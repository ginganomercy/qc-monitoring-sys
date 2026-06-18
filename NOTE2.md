# 📝 Catatan Deployment — VPS Docker (qc-tengfei.momoi.my.id)

> **Status Saat Ini**: Deployment aktif ke VPS `webperpus` via Docker Compose.
> Domain: `qc-tengfei.momoi.my.id` | Server path: `/opt/qc-monitor/`

---

## ✅ Ringkasan Infrastruktur

### Stack Deployment

| Komponen | Detail |
|----------|--------|
| Platform | VPS Linux (webperpus) |
| Deployment | Docker Compose |
| Image Registry | GHCR (`ghcr.io/ginganomercy/qc-monitoring-sys`) |
| CI/CD | GitHub Actions (`.github/workflows/qcdeploy.yml`) |
| Reverse Proxy | Nginx host (port 8088) di-proxy dari Nginx sistem |
| Domain | `qc-tengfei.momoi.my.id` (via Cloudflare/Nginx host) |

### Container yang Berjalan

| Service | Image | Port |
|---------|-------|------|
| `app` | `qc-monitoring-sys:latest` | 9000 (PHP-FPM internal) |
| `nginx` | `qc-monitoring-sys-nginx:latest` | 127.0.0.1:8088 |
| `mysql` | `mysql:8.0` | 3306 (internal) |
| `redis` | `redis:7-alpine` | 6379 (internal) |
| `cron` | `qc-monitoring-sys:latest` | (schedule runner) |

---

## 🛠️ Konfigurasi `.env` di Server

File ada di `/opt/qc-monitor/.env`. Template bersih (tanpa komentar inline):

```env
# --- APLIKASI ---
APP_NAME="QC Monitor"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://qc-tengfei.momoi.my.id

# --- DATABASE ---
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=qc_monitoring
DB_USERNAME=qc_user
DB_PASSWORD=<password_kuat>

# --- SEEDER ADMIN ---
SEED_ADMIN_PASSWORD=<password_admin>

APP_KEY=base64:<generated_key>
```

> ⚠️ **JANGAN** tambah komentar `# ...` pada baris yang sama dengan nilai variabel `DB_*`.
> Komentar inline menyebabkan nilai credentials terbaca salah oleh Docker, membuat `entrypoint.sh` loop tanpa henti.

---

## 🔄 Alur Update Deployment

```
Lokal → push ke main
    ↓
GitHub Actions: pint --test → docker build → push GHCR
    ↓
Di server (/opt/qc-monitor):
    docker compose pull
    docker compose down
    docker compose up -d
```

### Verifikasi Setelah Update

```bash
# 1. Cek semua container running
docker compose ps

# 2. Cek log app — harus muncul "MySQL is up and running"
docker compose logs app --tail=20

# 3. Test dari dalam server
curl -H "Host: qc-tengfei.momoi.my.id" -I http://127.0.0.1
# Expected: HTTP/1.1 200 OK atau 302 Found
```

---

## 🐛 Masalah yang Pernah Terjadi & Solusinya

Lihat detail lengkap di [`problem.md`](../../problem.md).

| Masalah | Fix | Commit |
|---------|-----|--------|
| `inspector_id` not found saat seeding | Hapus `InspectionSeeder` dari `DatabaseSeeder` | `da58701` |
| Loop `MySQL is unavailable` | Ganti `mysqladmin` → PHP PDO di `entrypoint.sh` | `e751702` |
| `Permission denied` saat `storage:link` | `COPY --chown=www-data` di Dockerfile | `fb3f784` |
| `.env` komentar inline merusak credentials | Edit manual di server, hapus komentar | - |

---

## 📋 Checklist Pertama Kali Deploy (Fresh Server)

```bash
# 1. Masuk ke direktori project
cd /opt/qc-monitor

# 2. Buat file .env (bersih, tanpa komentar inline pada baris DB_*)
nano .env

# 3. Pull image dan jalankan
docker compose up -d

# 4. Tunggu 15-20 detik, cek log
docker compose logs app --tail=20

# 5. Seed data master
docker compose exec app php artisan db:seed --class=UserSeeder --force
docker compose exec app php artisan db:seed --class=ProductSeeder --force
docker compose exec app php artisan db:seed --class=LineSeeder --force
docker compose exec app php artisan db:seed --class=DefectTypeSeeder --force
docker compose exec app php artisan db:seed --class=ComponentSeeder --force
docker compose exec app php artisan db:seed --class=DailyTargetSeeder --force

# 6. Optimasi
docker compose exec app php artisan optimize
```

---

## 🔧 Nginx Host Server (Reverse Proxy)

File konfigurasi Nginx host ada di `/etc/nginx/sites-enabled/app_cluster.conf`.
Proxy dari port 80/443 → `127.0.0.1:8088` (container nginx Docker).

```nginx
server {
    server_name qc-tengfei.momoi.my.id;
    location / {
        proxy_pass http://127.0.0.1:8088;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

Setelah edit config: `nginx -t && systemctl reload nginx`

---

*Catatan ini menggantikan NOTE2.md lama (cPanel deployment) yang sudah tidak relevan.*
