# Cara Install RotanFinder — Super Cepat ⚡

> RotanFinder = **Laravel (backend) + Next.js (frontend)**

---

## 1. Clone dari GitHub

```bash
git clone https://github.com/atassonggekreator-debug/RotanFinder.git
cd RotanFinder
```

---

## 2. Install Backend (seperti `pip install -r requirements.txt`)

```bash
cd backend

# Install semua library PHP (mirip pip install)
composer install

# Setup konfigurasi
cp .env.example .env
php artisan key:generate

# Buat database
touch database/database.sqlite

# Buat tabel-tabelnya
php artisan migrate --force

cd ..
```

---

## 3. Install Frontend (juga seperti `pip install -r requirements.txt`)

```bash
cd frontend

# Install semua library JS
npm install

# Set alamat backend
echo "NEXT_PUBLIC_LARAVEL_API_URL=http://localhost:8000/api" > .env.local

cd ..
```

---

## 4. Jalankan

### Terminal 1 — Backend API (port 8000)

```bash
cd backend
php artisan serve --port=8000
```

### Terminal 2 — Frontend (port 3000)

```bash
cd frontend
npm run dev
```

Buka browser: **http://localhost:3000** 🚀

---

## 5. Queue Worker (biar scraping gak lemot)

```bash
cd backend
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=256
```

---

## Biar auto-start setelah reboot laptop:

```bash
systemctl --user enable rotan-queue
systemctl --user start rotan-queue
sudo loginctl enable-linger iwan
```

---

## 💡 Analogi buat yang baru belajar

| Konsep | Python | **Project ini** |
|--------|--------|----------------|
| File daftar dependency | `requirements.txt` | `composer.json` (backend) + `package.json` (frontend) |
| Perintah install | `pip install -r requirements.txt` | **`composer install`** + **`npm install`** |
| Folder hasil install | `.venv/` atau `venv/` | `vendor/` (PHP) + `node_modules/` (JS) |
| Jalanin project | `python main.py` | `php artisan serve` + `npm run dev` |

---

## Cuma 4 langkah:

```
1. git clone
2. composer install
3. npm install
4. php artisan serve + npm run dev ✅
```

Gampang kan, Yang? 😄
