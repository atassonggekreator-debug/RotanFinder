---
# ✅ UAT CHECKLIST — RotanFinder (Round 2)
**URL:** http://localhost:3000
**Tools:** F12 → Console tab

---

## ⚡️ 1. DISCOVERY FORM

| # | Step | Expected | ✅/❌ |
|---|------|----------|:----:|
| 1.1 | Buka http://localhost:3000 | Halaman muncul + header "RotanFinder" | |
| 1.2 | Isi Niche: `"interview"` | Text terisi | |
| 1.3 | Ketik keyword `"inspiring"` → Enter | Chip muncul | |
| 1.4 | Platform: YouTube tercentang | Tombol YouTube aktif | |
| 1.5 | Slider Max Results → geser ke `3` | Angka 3 muncul | |
| 1.6 | Klik **"Discover Clip Potential"** | Loading skeleton muncul | |
| 1.7 | Tunggu loading selesai | Card muncul + thumbnail + score bar | |
| 1.8 | Cek Console tab (F12) | **⚠️ BOLEH ADA WARNING, TAPI TIDAK BOLEH ADA ERROR MERAH** | |
| 1.9 | Ganti Sort by ke "Views" | Urutan card berubah (highest views first) | |
| 1.10 | Klik salah satu card | Modal dialog muncul + breakdown bars + badges | |

## 🔖 2. SHORTLIST

| # | Step | Expected | ✅/❌ |
|---|------|----------|:----:|
| 2.1 | Klik ❤️ **Shortlist** di card | Icon merah + counter navbar naik | |
| 2.2 | Klik ❤️ di card lain | Counter naik lagi | |
| 2.3 | Klik ❤️ yg pertama lagi (toggle off) | Icon normal + counter turun | |
| 2.4 | Klik tab **Shortlist** di navbar | Cards yg di-shortlist muncul | |
| 2.5 | Refresh halaman (F5) | Shortlist masih ada (localStorage) | |

## 📁 3. EXPORT

| # | Step | Expected | ✅/❌ |
|---|------|----------|:----:|
| 3.1 | Di Shortlist tab, klik **CSV** | File `candidates.csv` terdownload | |
| 3.2 | Klik **JSON** | File `candidates.json` terdownload | |

## 🚨 4. EDGE CASES

| # | Step | Expected | ✅/❌ |
|---|------|----------|:----:|
| 4.1 | Hapus isi Niche → klik Submit | Text merah "Niche is required" muncul di bawah input | |
| 4.2 | Isi niche `"asdfghjkl12345xyz"` → Submit | Empty state "No results yet" | |
| 4.3 | Isi niche `"podcast"` + Max Results `10` → Submit | 10 card muncul (~30-60s scraping) | |

## 📱 5. RESPONSIVE

| # | Step | Expected | ✅/❌ |
|---|------|----------|:----:|
| 5.1 | Resize browser → **375px lebar** | Cards 1 kolom, gak overflow | |
| 5.2 | Resize → **1024px** | Cards 2 kolom | |
| 5.3 | Resize → **1440px+** | Cards 4 kolom | |

---

## ✅ VERDICT

| Area | ✅ Pass | ❌ Fail |
|------|:-------:|:-------:|
| Discovery Form | /10 | /10 |
| Shortlist | /5 | /5 |
| Export | /2 | /2 |
| Edge Cases | /3 | /3 |
| Responsive | /3 | /3 |
| **TOTAL** | **/23** | **/23** |

---

**Catat:** Kalau ada ❌, tulis **error message** yg muncul di Console (F12) biar bisa langsung fix.
