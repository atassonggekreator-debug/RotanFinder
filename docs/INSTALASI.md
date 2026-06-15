# Panduan Instalasi RotanFinder 🎬🔥

> **RotanFinder** — AI-powered clip potential finder untuk konten YouTube long-form.
> Mencari, menskor, dan menyimpan video potensial untuk dijadikan clip viral.

---

## 📋 Daftar Isi

- [Persyaratan Sistem](#persyaratan-sistem)
- [Clone Repository](#1-clone-repository)
- [Backend — Laravel API](#2-backend--laravel-api)
- [Frontend — Next.js](#3-frontend--nextjs)
- [Queue Worker (Opsional)](#4-queue-worker-opsional)
- [Menjalankan Aplikasi](#5-menjalankan-aplikasi)
- [Verifikasi](#6-verifikasi)
- [Troubleshooting](#troubleshooting)
- [Referensi](#referensi)

---

## Persyaratan Sistem

| Komponen | Minimal | Rekomendasi |
|----------|---------|-------------|
| OS | Linux / macOS / WSL2 | Linux (Arch/Ubuntu/Debian) |
| PHP | ^8.3 | **8.5** (digunakan开发) |
| Composer | ^2.5 | **2.8.12** |
| Node.js | ^18 | **22.22.2** |
| npm | ^9 | **10.9.7** |
| SQLite | ^3 | **3.53+** (built-in via PHP) |
| yt-dlp | ^2024 | **2026.03.17** |

### Cek Prasyarat

```bash
php --version       # harus ≥ 8.3
composer --version  # harus ≥ 2.5
node --version      # harus ≥ 18
npm --version       # harus ≥ 9
yt-dlp --version    # opsional, untuk scraping YouTube
sqlite3 --version   # harus terinstall
```

### Install yt-dlp (jika belum ada)

```bash
# Arch Linux (CachyOS)
sudo pacman -S yt-dlp

# Ubuntu/Debian
sudo apt install yt-dlp

# macOS
brew install yt-dlp

# Manual (semua OS)
pip install yt-dlp
```

---

## 1. Clone Repository

```bash
git clone https://github.com/atassonggekreator-debug/RotanFinder.git
cd RotanFinder
```

Struktur direktori setelah clone:

```
RotanFinder/
├── backend/        # Laravel 13 API (port 8000)
├── frontend/       # Next.js 16 (port 3000)
├── docs/           # Dokumentasi
└── README.md
```

---

## 2. Backend — Laravel API

### 2a. Install Dependencies PHP

```bash
cd backend
composer install --no-dev --optimize-autoloader
```

> Untuk development: `composer install` (termasuk dev dependencies).

### 2b. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env` jika perlu. Konfigurasi default yang digunakan:

| Parameter | Value | Keterangan |
|-----------|-------|------------|
| `DB_CONNECTION` | `sqlite` | Database file-based, tanpa setup MySQL |
| `SESSION_DRIVER` | `database` | Session pakai SQLite |
| `QUEUE_CONNECTION` | `database` | Queue pakai SQLite |
| `CACHE_STORE` | `database` | Cache pakai SQLite |

> **Catatan**: Project ini menggunakan **SQLite dual database**:
> - `database/database.sqlite` — data utama (videos, shortlists, users)
> - `database/queue.sqlite` — queue jobs (terpisah agar tidak mengganggu performa)

### 2c. Buat Database SQLite

```bash
touch database/database.sqlite
touch database/queue.sqlite
```

### 2d. Jalankan Migration

```bash
php artisan migrate --force
```

Ini akan membuat semua tabel:
- `videos` — data video hasil scraping
- `shortlists` — daftar video yang disimpan user
- `sessions` — session management
- `cache` + `cache_locks` — cache storage
- `job_batches` + `jobs` + `failed_jobs` — queue system

### 2e. Storage Link (untuk export)

```bash
php artisan storage:link
```

### 2f. Cek API Key dan Konfigurasi

```bash
php artisan config:clear
php artisan route:list
```

### 2g. Test Backend

```bash
php artisan serve --port=8000 &
curl http://localhost:8000/api/discover
```

Hasil: `{"data":[],"message":"No videos found"}` (wajar, belum ada video).

---

## 3. Frontend — Next.js

### 3a. Install Dependencies

```bash
cd ../frontend   # dari folder RotanFinder/
npm install
```

### 3b. Konfigurasi Environment

Buat file `.env.local`:

```bash
echo "NEXT_PUBLIC_LARAVEL_API_URL=http://localhost:8000/api" > .env.local
```

> **Penting**: URL ini harus sesuai dengan alamat backend Laravel.
> - Development: `http://localhost:8000/api`
> - Production: `https://api.domainkamu.com/api`

### 3c. Build (cek error)

```bash
npm run build
```

Harus selesai tanpa error — semua route static (4/4).

### 3d. Test Frontend

```bash
npm run dev &
curl http://localhost:3000
```

---

## 4. Queue Worker (Opsional)

Queue worker memproses **YouTube scraping** di background.
Tanpa queue worker, scraping tetap jalan tapi **synchronous** (lambat untuk banyak video).

### 4a. Setup Worker Manual

```bash
cd backend
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=256
```

### 4b. Setup Systemd Service (Auto-start setelah reboot)

```bash
# Copy file service yang sudah disediakan
cp rotan-queue.service ~/.config/systemd/user/

# Reload systemd
systemctl --user daemon-reload

# Enable auto-start
systemctl --user enable rotan-queue

# Start worker
systemctl --user start rotan-queue

# Cek status
systemctl --user status rotan-queue

# Enable linger (biar service tetap jalan meskipun user logout)
sudo loginctl enable-linger $(whoami)
```

### 4c. Logs Queue Worker

```bash
# Live log
journalctl --user -u rotan-queue -f

# Log 100 baris terakhir
journalctl --user -u rotan-queue -n 100

# Log sejak hari ini
journalctl --user -u rotan-queue --since today
```

### 4d. Perintah Queue Worker

```bash
systemctl --user start   rotan-queue   # Start
systemctl --user stop    rotan-queue   # Stop
systemctl --user restart rotan-queue   # Restart
systemctl --user status  rotan-queue   # Status
```

---

## 5. Menjalankan Aplikasi

### Terminal 1 — Backend API

```bash
cd RotanFinder/backend
php artisan serve --port=8000
```

### Terminal 2 — Frontend

```bash
cd RotanFinder/frontend
npm run dev
```

### Terminal 3 — Queue Worker (optional)

```bash
cd RotanFinder/backend
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=256
```

Atau lewat systemd:

```bash
systemctl --user start rotan-queue
```

### Semua dalam satu perintah (development)

```bash
cd RotanFinder/backend
composer run dev
```

Ini menjalankan: `php artisan serve` + `queue:listen` + `pail` (logs) + `npm run dev` (Vite) secara concurrent.

---

## 6. Verifikasi

### Cek Backend

```bash
curl -s http://localhost:8000/api/discover | head -c 200
```

Response: JSON array video (bisa kosong).

### Cek Frontend

Buka browser: **http://localhost:3000**

### Cek Queue Worker

```bash
curl -X POST http://localhost:8000/api/discover \
  -H "Content-Type: application/json" \
  -d '{"query": "TEDx talk motivation"}'
```

Queue worker akan memproses scraping di background. Cek log:

```bash
journalctl --user -u rotan-queue -n 20
```

### Cek Shortlist

```bash
# Add to shortlist (ganti VIDEO_ID dengan id video yang ada)
curl -X POST http://localhost:8000/api/shortlist \
  -H "Content-Type: application/json" \
  -d '{"video_id": 1}'

# List shortlist
curl http://localhost:8000/api/shortlist

# Export
curl -X POST http://localhost:8000/api/export \
  -H "Content-Type: application/json" \
  -d '{"format": "csv"}'
```

---

## Troubleshooting

### ❌ `php artisan key:generate` gagal

```bash
# Manual set APP_KEY
php -r "echo 'base64:'.base64_encode(random_bytes(32));"
# Copy output dan paste ke .env: APP_KEY=base64:...
```

### ❌ `SQLSTATE[HY000]: General error: 1 no such table`

```bash
php artisan migrate --force
php artisan cache:clear
```

### ❌ Frontend tidak bisa connect ke API

```bash
# Cek .env.local
cat frontend/.env.local
# Harus: NEXT_PUBLIC_LARAVEL_API_URL=http://localhost:8000/api

# Cek backend jalan
curl http://localhost:8000/api/discover

# Restart backend
pkill -f "artisan serve" || true
php artisan serve --port=8000 &
```

### ❌ Queue worker restart terus

```bash
# Cek memory limit
systemctl --user status rotan-queue

# Jika OOM, naikkan memory limit
# Edit ~/.config/systemd/user/rotan-queue.service
# Ubah --memory=256 → --memory=512
systemctl --user daemon-reload
systemctl --user restart rotan-queue
```

### ❌ yt-dlp tidak ditemukan

```bash
# Install via pip
pip install yt-dlp

# Atau via package manager
sudo pacman -S yt-dlp      # Arch
sudo apt install yt-dlp    # Debian
```

### ❌ Port 8000/3000 sudah dipakai

```bash
# Cek PID pemakai port
lsof -i :8000
lsof -i :3000

# Kill proses
kill -9 <PID>

# Atau ganti port backend
php artisan serve --port=8001

# Update frontend .env.local
echo "NEXT_PUBLIC_LARAVEL_API_URL=http://localhost:8001/api" > frontend/.env.local
```

### ❌ Composer out of memory

```bash
# Tambah memory limit
php -d memory_limit=2G $(which composer) install
```

---

## Referensi

| Resource | Link |
|----------|------|
| GitHub Repo | https://github.com/atassonggekreator-debug/RotanFinder |
| PRD | `docs/PRD.md` |
| UAT Checklist | `docs/UAT_CHECKLIST.md` |
| Progress Log | `docs/Agents.md` |
| Laravel 13 Docs | https://laravel.com/docs/13.x |
| Next.js 16 Docs | https://nextjs.org/docs |
| shadcn/ui | https://ui.shadcn.com |
| yt-dlp | https://github.com/yt-dlp/yt-dlp |

---

## 📊 Tech Stack

| Layer | Tech | Version |
|-------|------|---------|
| Frontend Framework | Next.js (App Router) | 16.2.9 |
| UI Library | React | 19.2.4 |
| Styling | Tailwind CSS | v4 |
| Components | shadcn/ui (Radix UI) | latest |
| State Management | React Context + useReducer | — |
| HTTP Client | Axios | 1.17 |
| Backend Framework | Laravel | 13.8 |
| PHP | PHP CLI | 8.5 |
| Database | SQLite | 3.53 |
| Scraping | yt-dlp | 2026.03.17 |
| Package Manager (PHP) | Composer | 2.8 |
| Package Manager (JS) | npm | 10.9 |

---

### ✅ Setup Checklist

- [ ] PHP + Composer + Node.js + SQLite terinstall
- [ ] yt-dlp terinstall (untuk scraping YouTube)
- [ ] Repo sudah di-clone
- [ ] `composer install` sukses
- [ ] `.env` sudah di-copy dan `APP_KEY` sudah di-generate
- [ ] Database SQLite sudah dibuat dan di-migrate
- [ ] `npm install` sukses
- [ ] Frontend `.env.local` sudah diisi
- [ ] Backend `php artisan serve` berjalan di port 8000
- [ ] Frontend `npm run dev` berjalan di port 3000
- [ ] Bisa akses http://localhost:3000 dan melihat halaman
- [ ] Queue worker berjalan (opsional)
- [ ] `loginctl enable-linger` sudah dijalankan (jika pakai systemd)
