# Panduan Penggunaan RotanFinder 🎬🔥

> **RotanFinder** membantu content creator menemukan video YouTube long-form yang punya potensi tinggi untuk dijadikan clip viral — berdasarkan skor 4 dimensi.

---

## 📋 Daftar Isi

- [Akses Aplikasi](#1-akses-aplikasi)
- [Discovery — Mencari Video Potensial](#2-discovery--mencari-video-potensial)
- [Memahami Hasil Pencarian](#3-memahami-hasil-pencarian)
- [Sistem Skor](#4-sistem-skor)
- [Shortlist — Menyimpan Video](#5-shortlist--menyimpan-video)
- [Export Data](#6-export-data)
- [Navigasi](#7-navigasi)
- [Tips & Trik](#8-tips--trik)
- [Contoh Skenario](#9-contoh-skenario)

---

## 1. Akses Aplikasi

Pastikan backend dan frontend sudah jalan.

### Cek status

```bash
# Backend (Laravel API)
curl -s http://localhost:8000/api/discover | head -c 100

# Frontend (Next.js)
curl -s -o /dev/null -w "%{http_code}" http://localhost:3000
```

Keduanya harus return **200**.

### Buka di browser

```
http://localhost:3000
```

Tampilan: halaman gelap (**dark mode**) dengan form pencarian di tengah.

---

## 2. Discovery — Mencari Video Potensial

Ini adalah fitur utama. Kalian memasukkan topik → RotanFinder scraping YouTube → menskor tiap video → menampilkan hasil.

### Form Discovery

![Discovery Form](https://via.placeholder.com/400x600/1f2937/ffffff?text=Discovery+Form)

#### 2a. Niche (wajib)

Topik atau niche yang ingin dicari.

```
Contoh: "tech reviews", "fitness motivation", "gaming highlights", "TEDx talks"
```

> Niche ini jadi kata kunci utama untuk mencari video di YouTube.

#### 2b. Keywords (opsional)

Kata kunci tambahan untuk mempersempit hasil. Bisa tambah banyak.

```
Cara:
1. Ketik kata kunci
2. Tekan Enter atau klik "Add"
3. Muncul sebagai tag chip biru
4. Klik X untuk hapus keyword
```

Contoh:

| Niche | Keywords |
|-------|----------|
| tech reviews | smartphone, laptop, 2026 |
| fitness | weight loss, home workout, no equipment |
| gaming | minecraft, speedrun, survival |

#### 2c. Platform

Pilih platform sumber video. Saat ini support:

| Platform | Status |
|----------|--------|
| YouTube | ✅ Aktif (scraping via yt-dlp) |
| TikTok | 🚧 Coming soon |
| Instagram | 🚧 Coming soon |

#### 2d. Durasi (detik)

Filter panjang video (dalam detik).

| Parameter | Default | Rentang |
|-----------|---------|---------|
| Min Duration | 480s (8 menit) | ≥ 60s |
| Max Duration | 7200s (120 menit) | ≥ min |

> **Tips**: Untuk konten long-form, set min 8 menit (480s) dan max 2 jam (7200s). Sesuaikan lebih sempit kalau mau tipe konten spesifik (misal podcast 20-60 menit).

#### 2e. Max Results

Jumlah hasil yang mau di-scrape.

```
Slider: 1 — 20
Default: 5
```

> Semakin banyak results → semakin lama proses scraping. Mulai dengan 5 untuk tes, naikkan kalau sudah yakin.

#### 2f. AI Re-ranking

Toggle untuk mengaktifkan/menonaktifkan **AI re-ranking**.

| ON | OFF |
|----|-----|
| Video diurutkan ulang pakai AI algorithm | Video diurutkan berdasarkan skor mentah |
| Rekomendasi: **ON** (default) | Hanya untuk debugging |

#### 2g. Budget (opsional)

Budget dalam USD untuk konteks bisnis (belum dipakai untuk filtering, tapi bisa untuk development ke depan).

### Eksekusi Discovery

Klik tombol **"Discover Clip Potential"** → loading spinner muncul → tunggu hasil.

Prosesnya:

```
1. Form divalidasi (niche wajib, platform minimal 1, durasi valid)
2. Request dikirim ke backend Laravel
3. Backend jalankan yt-dlp scraping YouTube
4. Video di-score pake algoritma 4 dimensi
5. Hasil dikembalikan ke frontend
6. Card-card video muncul di bawah form
```

⏱️ **Estimasi waktu**:
- 5 hasil: ~10-30 detik (tergantung koneksi)
- 20 hasil: ~30-60 detik
- Queue worker: scraping jalan di background, notifikasi via toast

---

## 3. Memahami Hasil Pencarian

Setelah discovery selesai, hasilnya tampil sebagai **grid card** dengan layout responsif:

| Layar | Kolom |
|-------|-------|
| Mobile (< 640px) | 1 kolom |
| Tablet (640-1023px) | 2 kolom |
| Desktop (1024-1279px) | 3 kolom |
| Wide (≥ 1280px) | 4 kolom |

### Setiap Card Menampilkan:

```
┌──────────────────────────────┐
│ [THUMBNAIL]                  │
│ ┌────────┐          ┌──────┐ │
│ │YOUTUBE │          │ 7.2  │ │  ← Platform badge + Score
│ └────────┘          └──────┘ │
├──────────────────────────────┤
│ Title video (max 60 char)…   │
│                              │
│ 1.2M views   ♥ 45K           │  ← Views & Likes
│                              │
│ Score  ████████░░  7.2 / 10  │  ← Score bar (warna)
│                              │
│            [ high ]          │  ← Confidence level
├──────────────────────────────┤
│ [♥ Shortlist]   [Details  →] │  ← Actions
└──────────────────────────────┘
```

### Elemen Card:

| Elemen | Deskripsi |
|--------|-----------|
| **Thumbnail** | Gambar preview video dari YouTube |
| **Platform badge** | Label "YOUTUBE" di pojok kiri atas |
| **Score** | Angka skor di pojok kanan atas (0.0 — 10.0) |
| **Title** | Judul video (max 60 karakter, hover untuk full) |
| **Views** | Jumlah view (diformat: 1.2M, 45K, 890) |
| **Likes** | Jumlah like dengan icon heart |
| **Score bar** | Progress bar warna: 🔴 merah (<4), 🟡 kuning (4-6.9), 🟢 hijau (≥7) |
| **Confidence** | Badge: `low`, `medium`, `high` — seberapa yakin sistem dengan skor |
| **Shortlist btn** | ♥ Tambah/hapus dari shortlist |
| **Details btn** | Buka detail video |

### Warna Score Bar:

| Skor | Warna | Arti |
|------|-------|------|
| ≥ 7.0 | 🟢 Hijau | **Excellent** — potensi clip tinggi |
| 4.0 — 6.9 | 🟡 Kuning | **Good** — potensi menengah |
| < 4.0 | 🔴 Merah | **Low** — potensi rendah |

---

## 4. Sistem Skor

RotanFinder menskor video berdasarkan **4 dimensi**:

| Dimensi | Bobot | Parameter |
|---------|-------|-----------|
| **Engagement** | **40%** | Rasio likes + comments terhadap views. Semakin tinggi interaksi per view → semakin tinggi skor. |
| **Velocity** | **30%** | Rate interaksi per hari sejak video diupload. Video baru dengan engagement tinggi → skor tinggi. |
| **Longform Fit** | **20%** | Seberapa dekat durasi video dengan range ideal clip (8-15 menit). 8-15 menit = skor maksimal. |
| **Momentum** | **10%** | Sinyal gabungan: score × engagement intensity. Bonus untuk video yang lagi "panas". |

### Rumus Akhir

```
SKOR AKHIR = (Engagement × 40%) + (Velocity × 30%) + (Longform × 20%) + (Momentum × 10%)
```

### Contoh Interpretasi

| Skor | Interpretasi | Tindakan |
|------|-------------|----------|
| **8.5 — 10** | 🚀 Viral potensial. Engagement tinggi, velositas cepat. | **Segera** ambil clip |
| **6.0 — 8.4** | 👍 Bagus. Potensi bagus, mungkin kurang optimal di satu dimensi. | Masuk shortlist, pantau |
| **4.0 — 5.9** | ⚠️ Cukup. Satu atau dua dimensi lemah. | Evaluasi niche/keyword |
| **< 4.0** | ❌ Rendah. Engagement kecil atau video terlalu pendek/panjang. | Skip, refine pencarian |

---

## 5. Shortlist — Menyimpan Video

Shortlist adalah fitur **bookmark** untuk menyimpan video potensial.

### Menambah ke Shortlist

**Dari halaman Discovery:**
- Klik tombol **♥ Shortlist** di card video
- Tombol berubah jadi **♥ Saved** (merah)
- Icon heart terisi (fill)

### Melihat Shortlist

**Klik menu "Shortlist"** di navigasi atas (icon hati).

Halaman Shortlist menampilkan:
- Semua video yang sudah disimpan
- Card yang sama seperti di Discovery
- Tombol **♥ Remove** untuk hapus

### Sinkronisasi

Shortlist menggunakan sistem **dual-layer**:

```
1. CACHE (localStorage) → Tampil instan
2. API (database SQLite) → Source of truth
```

- ✅ Data tetap ada meskipun browser di-refresh
- ✅ Data tetap ada meskipun backend di-restart (tersimpan di DB)
- ✅ Kalau API gagal, fallback ke cache + notifikasi toast
- ❌ Tidak perlu login — shortlist per perangkat (single-user MVP)

### Menghapus dari Shortlist

**Dari halaman Shortlist:**
- Klik tombol **♥ Remove** pada card
- Video hilang dari shortlist
- Data terhapus dari database

**Dari halaman Discovery:**
- Klik tombol **♥ Saved** (sudah terisi merah)
- Kembali ke status **♥ Shortlist** (kosong)

---

## 6. Export Data

Export shortlist ke file CSV atau JSON.

### Lokasi

Halaman **Shortlist** → pojok kanan atas → tombol **CSV** dan **JSON**.

### Cara Export

```bash
1. Buka halaman Shortlist
2. Klik "CSV" untuk export format spreadsheet
3. Atau klik "JSON" untuk export format data
4. File langsung ter-download otomatis
```

### Format CSV

```csv
id,title,platform,views,likes,score,confidence,url,thumbnail
1,"How to Build a Startup","youtube",1200000,45000,7.2,high,https://...,https://...
```

### Format JSON

```json
[
  {
    "id": 1,
    "title": "How to Build a Startup",
    "platform": "youtube",
    "views": 1200000,
    "likes": 45000,
    "score": 7.2,
    "confidence": "high"
  }
]
```

### Gunakan Export Untuk:

- 📊 Analisis lebih lanjut di Excel / Google Sheets
- 📋 Report ke tim
- 💾 Backup data shortlist
- 🔄 Migrasi ke alat lain

---

## 7. Navigasi

### Layout Aplikasi

```
┌─────────────────────────────────────────┐
│  [🏠 RotanFinder]  [♥ Shortlist]  [⚙]  │ ← Navbar
├─────────────────────────────────────────┤
│                                         │
│  (Konten halaman — Discovery/           │
│   Shortlist/Settings)                   │
│                                         │
└─────────────────────────────────────────┘
```

### Halaman

| Menu | Path | Deskripsi |
|------|------|-----------|
| 🏠 **Discovery** | `/` | Form pencarian + hasil skor |
| ♥ **Shortlist** | `/shortlist` | Video tersimpan + export |
| ⚙ **Settings** | `/settings` | Pengaturan (coming soon) |

### Error Handling

- **Error di satu card** → card yang error diisolasi, card lain tetap tampil (ErrorBoundary)
- **Backend mati** → toast notifikasi, halaman tetap tampil
- **Global crash** → fallback page dengan tombol "Try again" (global-error.tsx)

---

## 8. Tips & Trik

### 🎯 Discovery Optimal

```
✅ LAKUKAN:
  - Gunakan niche spesifik ("vegan recipes high protein")
  - Tambah 2-3 keywords relevan
  - Set durasi 480-3600s (8-60 menit) untuk sweet spot
  - Mulai dengan 5 hasil untuk quick test
  - AI Re-ranking = ON

❌ JANGAN:
  - Niche terlalu umum ("video")
  - Keyword duplikat dengan niche
  - Durasi terlalu pendek (< 120s) — itu sudah short-form
  - Max results 20 langsung — tunggu sampai yakin
```

### 📊 Interpretasi Skor

```
Skor ≥ 7.0 + Confidence "high"
  → 🔥 Potensi viral. Ambil clip sekarang.

Skor ≥ 7.0 + Confidence "medium"
  → 👍 Bagus. Masuk shortlist, pantau beberapa hari.

Skor 4.0-6.9 + Confidence "high"
  → 💡 Potensi tersembunyi. Cek judul/thumnail dulu.

Skor < 4.0
  → 🔄 Ganti niche/keyword. Coba pendekatan lain.
```

### 📈 Workflow Ideal

```
1. Buka RotanFinder → http://localhost:3000
2. Masukkan niche + keywords
3. Klik "Discover Clip Potential"
4. Review hasil — lihat skor, views, likes
5. ★ Klik "Shortlist" untuk video ≥ 7.0
6. Ulangi step 2-5 untuk niche berbeda
7. Buka halaman Shortlist
8. Export CSV → analisis di Excel
9. Pilih top 3 video → buat clip
```

### 💾 Data Persistence

| Data | Location | Persistent |
|------|----------|------------|
| Video hasil discovery | In-memory (state) | ❌ Hilang setelah refresh |
| Shortlist | SQLite DB + localStorage | ✅ Permanen |
| Queue scraping jobs | SQLite queue DB | ✅ Sampai diproses |
| Preferences | Not yet implemented | 🚧 Coming soon |

> **Tips**: Selalu export shortlist ke CSV sebelum refresh jika ingin backup data penuh.

---

## 9. Contoh Skenario

### Skenario 1: Content Creator Gaming

**Tujuan**: Cari video Minecraft survival dengan potensi clip tinggi.

```
Niche: "minecraft survival let's play"
Keywords: [speedrun, base tour, redstone]
Duration: 600 - 4800 (10-80 menit)
Max Results: 10
AI Rerank: ON
```

**Hasil**: 10 video dengan skor 3.2 — 8.9.
**Tindakan**: Shortlist 3 video dengan skor ≥ 7.0. Ambil clip bagian speedrun.

### Skenario 2: Motivational Clips

**Tujuan**: Cari TEDx talks buat clip motivasi viral.

```
Niche: "TEDx motivation success"
Keywords: [mindset, discipline, habits]
Duration: 480 - 1200 (8-20 menit)
Max Results: 5
```

**Hasil**: 5 video dengan skor 5.1 — 9.2.
**Tindakan**: Shortlist semua ≥ 8.0. Export CSV untuk diedit tim.

### Skenario 3: Tech Reviewer

**Tujuan**: Cari review smartphone terbaru buat clip perbandingan.

```
Niche: "smartphone review 2026"
Keywords: [iphone, samsung, comparison]
Duration: 480 - 3600
Max Results: 8
```

**Hasil**: 8 video, skor 4.5 — 8.7.
**Tindakan**: Sort manual. Ambil video dengan views tinggi + skor bagus.

---

## 🔧 Referensi Cepat

### Semua API Endpoints

| Method | Endpoint | Fungsi | Rate Limit |
|--------|----------|--------|------------|
| `GET` | `/api/discover` | List semua video | 60/min |
| `POST` | `/api/discover` | Scrape YouTube | 5/min |
| `GET` | `/api/shortlist` | List shortlist | 60/min |
| `POST` | `/api/shortlist` | Tambah shortlist | 30/min |
| `DELETE` | `/api/shortlist/{id}` | Hapus shortlist | 30/min |
| `GET` | `/api/preferences` | Lihat preferensi | 60/min |
| `PUT` | `/api/preferences` | Update preferensi | 30/min |
| `POST` | `/api/export` | Export CSV/JSON | 30/min |

### Queue Worker Commands

```bash
# Manual
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=256

# Systemd
systemctl --user start   rotan-queue
systemctl --user stop    rotan-queue
systemctl --user status  rotan-queue
journalctl --user -u rotan-queue -f
```

---

### ✅ Quick Checklist Penggunaan

- [ ] Backend jalan di port 8000
- [ ] Frontend jalan di port 3000
- [ ] Queue worker aktif (opsional)
- [ ] Bisa akses http://localhost:3000
- [ ] Discovery form muncul
- [ ] Bisa submit niche → dapat hasil video
- [ ] Card video menampilkan skor dengan benar
- [ ] Bisa shortlist video
- [ ] Shortlist persist setelah refresh
- [ ] Bisa export CSV / JSON
- [ ] Buka shortlist → lihat semua video tersimpan
- [ ] Bisa hapus dari shortlist
