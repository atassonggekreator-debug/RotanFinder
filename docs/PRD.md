# PRD — RotanFinder (Updated: 2026-06-15)

## 1. Executive Summary

RotanFinder adalah aplikasi laptop-first untuk menemukan **video panjang (long-form)** yang paling layak di-clip menjadi short-form content. Sistem tidak memberi rekomendasi berdasarkan tebakan, tetapi berdasarkan metadata YouTube, engagement rate, view velocity, durasi long-form, dan scoring yang bisa dijelaskan. Target awalnya adalah content creator, clipper, dan growth operator yang butuh shortlist video potensial tanpa harus manual scroll berjam-jam. MVP difokuskan pada **discovery, scoring, shortlist, dan export** menggunakan satu stack monolitik Laravel 13 untuk backend dan Next.js 14 untuk frontend.

---

## 2. Tech Stack (Aktual)

### Backend — Laravel 13 (PHP 8.4)
- **Framework**: Laravel 13 (PHP 8.4 via herd-lite)
- **Database**: SQLite WAL mode
- **Scraping**: yt-dlp (YouTube search via `ytsearch{N}:{query}`)
- **Scoring**: ClipPotentialService (PHP native, no external AI)
- **Server**: `php artisan serve --port=8000`

### Frontend — Next.js 14
- **Framework**: Next.js 14 App Router + TypeScript
- **UI**: Tailwind CSS + shadcn/ui components (dark theme)
- **State**: React Context + useReducer + localStorage sync
- **Server**: `npm run dev` (port 3000)

### Deployment (MVP)
- **Dua server terpisah**: Frontend :3000, Backend :8000
- **No Docker/Kubernetes** — local-first
- **No external AI** — semua scoring rules-based di PHP
- **No queue worker systemd** — scraping sinkron (masih dalam antrian)

---

## 3. Architecture — Duo Servers

```
Browser → Next.js :3000 ──HTTP──→ Laravel :8000 ──yt-dlp──→ YouTube
```

| Layer | Teknologi | Port | Peran |
|-------|-----------|------|-------|
| Frontend | Next.js 14 | 3000 | UI, state management, SSR |
| Backend | Laravel 13 | 8000 | API, scraping, scoring, database |

**Tidak ada FastAPI / Python service terpisah.** Semua logika scraping dan scoring ada di Laravel.

### Alur Discovery
```
User isi form → POST /api/discover
  → Laravel ScraperService → yt-dlp ytsearch → metadata
  → ClipPotentialService → score (engagement 40%, velocity 30%, longform 20%, momentum 10%)
  → Simpan ke SQLite → return candidates
```

### Alur Shortlist
```
User shortlist video → dispatch TOGGLE_SHORTLIST → simpan ID ke localStorage
  → Sync ke localStorage key "rotanfinder_state"
  → Shortlist page filter state.results by shortlist IDs
  → Export CSV/JSON via POST /api/export
```

---

## 4. Product Scope — MVP (Selesai ✅)

### ✅ Discovery
- Input niche, keywords (chips), platform preference, duration min/max, max results
- YouTube search via yt-dlp real-time
- Durasi filter 8 menit - 2 jam (long-form only)
- Deduplikasi URL otomatis
- Sort by: Score / Views / Engagement / Freshness

### ✅ Scoring Engine (ClipPotentialService)
| Dimensi | Bobot | Sumber Data |
|---------|-------|-------------|
| Engagement | 40% | like-to-view ratio, comment rate |
| Velocity | 30% | views per hour |
| Longform | 20% | durasi video (semakin panjang >8min, semakin tinggi) |
| Momentum | 10% | kombo engagement + comments |

Total score 0-10 per video. Confidence: high (≥7), medium (≥4), low (<4).

### ✅ Output Rekomendasi
Setiap candidate berisi:
- total score
- score breakdown per 4 dimensi (engagement, velocity, longform, momentum)
- reason bullets 2-4 poin
- clip angle ideas minimal 2
- viral potential (0-10)
- monetization potential (0-10)
- risk note jika ada
- confidence level: low / medium / high

### ✅ Shortlist & Export
- Shortlist toggle di card → icon merah + navbar counter
- Shortlist page dengan CandidateCards
- Export CSV + JSON ke file download
- Clear all shortlist

### ✅ UI/UX
- Dark theme
- Loading skeleton saat scraping
- Empty state "No results yet"
- Error toast (console)
- Modal breakdown dengan score bars
- Responsive grid: 1 → 2 → 3 → 4 kolom
- Hydration-safe (Next.js SSR)

### ❌ Belum Termasuk MVP
- AI reranking (tidak diimplementasikan — scoring rule-based sudah cukup)
- Multi-platform (YouTube-only saat ini)
- Queue worker systemd (scraping masih sinkron)
- User auth / multi-tenant
- FastAPI integration
- Auto video download
- Auto clipping/editing pipeline
- Vector database

---

## 5. User Workflow (Aktual)

1. User buka http://localhost:3000
2. Isi **Niche**, **Keywords** (chip input), pilih **Platform**, atur **Duration** dan **Max Results**
3. Klik **"Discover Clip Potential"** → loading skeleton
4. Backend scraping → scoring → return candidates (2-30 detik tergantung query)
5. Card muncul dengan: thumbnail, title, creator, views/likes, score bar, confidence badge
6. Klik card → modal breakdown (4 dimensi + reasons + clip angles)
7. Shortlist video (❤️) → counter navbar naik → tab Shortlist nampilin card
8. Export CSV/JSON dari tab Shortlist

---

## 6. User Stories (Aktual)

### US-01: Discovery Form
**As a** content creator
**I want** to submit niche and keywords
**So that** the system searches YouTube for relevant long-form videos

**Acceptance Criteria**:
- ✅ Given user is on discovery form, when user submits niche + keywords + platform filters, then system starts YouTube scraping with those exact filters
- ✅ Given user submits invalid empty input, when user clicks submit, then system shows inline validation error

### US-02: Real Scraping & Scoring
**As a** clipper
**I want** the system to scrape YouTube and score each video
**So that** I can see which videos are most clip-worthy without manual browsing

**Acceptance Criteria**:
- ✅ Given user submits a valid query, when scraping completes, then system returns candidates with score, breakdown, confidence, and reason
- ✅ Given a video has no transcript data, when scoring runs, then scoring still produces a valid score (minus transcript-dependent signals)

### US-03: Explainable Scoring
**As a** researcher
**I want** to see why a video is recommended
**So that** I can make informed decisions about which clips to pursue

**Acceptance Criteria**:
- ✅ Given scoring engine processes a candidate, when result is displayed, then user sees total score, 4-dimension breakdown, reason bullets, and clip angle ideas
- ✅ Given a candidate is low-confidence, when displayed, then confidence badge shows "Low"

### US-04: Shortlist & Export
**As a** growth operator
**I want** to shortlist videos and export them
**So that** I can move selected candidates into my clipping workflow

**Acceptance Criteria**:
- ✅ Given ranked recommendations are shown, when user clicks shortlist, then system persists shortlist status and updates counter
- ✅ Given shortlisted candidates exist, when user exports CSV or JSON, then system downloads a valid file

### US-05: State Persistence
**As a** user
**I want** my shortlist to survive page refresh
**So that** I don't lose my selections

**Acceptance Criteria**:
- ❌ **Belum**: Shortlist persistent ke DB (masih localStorage-only — hilang jika ganti browser/device)
- ⚠️ **Partial**: Data bertahan di localStorage, namun results juga perlu di-restore agar cards bisa ditampilkan setelah refresh

---

## 7. Non-Functional Requirements (Aktual)

| Requirement | Status | Notes |
|------------|--------|-------|
| Response time <15 detik untuk 100 kandidat | ⚠️ **Tergantung query** | yt-dlp bisa lambat untuk query kompleks |
| Local memory idle <600MB | ✅ | Laravel + Next.js idle ~200MB |
| SQLite WAL mode | ✅ | Database SQLite WAL default |
| Idempotent writes | ✅ | Deduplikasi by URL |
| Timeout dan retry pada scraping | ✅ | HTTP timeout 120s (axios), retry di ScraperService |
| Structural JSON logs | ✅ | Laravel default logging |
| Local-only security | ✅ | Tidak ada auth di MVP |

---

## 8. Scoring Framework (Aktual)

Score total = rata-rata tertimbang dari 4 dimensi (0-10):

| Dimensi | Bobot | Formula | Cap |
|---------|-------|---------|-----|
| Engagement | 40% | `(likes/views * 100 / 2.5) * 10` | min(10, value) |
| Velocity | 30% | `(views / max(1, duration_hrs) / 10000) * 10` | min(10, value) |
| Longform | 20% | `min(1, duration_hrs) * 10` | min(10, value) |
| Momentum | 10% | `((engagement_rate + comment_rate) / 3) * 10` | min(10, value) |

Output:
- **Score**: 0.0 - 10.0
- **Confidence**: high (≥7), medium (≥4), low (<4)

---

## 9. API Endpoints (Aktual)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/discover` | List semua video di DB |
| POST | `/api/discover` | Trigger scraping + scoring baru |
| POST | `/api/toggle-shortlist` | Toggle shortlist (session-based) |
| GET | `/api/shortlist` | List shortlist IDs (session) |
| POST | `/api/export` | Export CSV/JSON file download |
| GET | `/api/preferences` | Get user preferences |
| POST | `/api/preferences` | Update user preferences |

---

## 10. Data Model (Aktual)

### Entity: `videos`
| Field | Type | Notes |
|-------|------|-------|
| id | integer (PK) | Auto-increment |
| url | string | Unique |
| video_id | string | YouTube video ID |
| title | string | |
| description | text | |
| duration | integer | Seconds |
| view_count | integer | |
| like_count | integer | |
| comment_count | integer | |
| uploader | string | |
| upload_date | string | |
| thumbnail | string | YouTube thumbnail URL |
| clip_potential_score | float | 0.0 - 10.0 |
| recommendation | string | high / medium / low |
| created_at | timestamp | |
| updated_at | timestamp | |

### Frontend State (AppContext)
```typescript
interface AppState {
  isLoading: boolean;
  results: Candidate[];     // Array of fetched/restored videos
  error: string | null;
  shortlist: number[];       // Array of video IDs
  activeTab: string;
  selectedCandidate: Candidate | null;
}
```

### Response Format (Candidate)
```json
{
  "id": 23,
  "title": "Podcast Inspiratif",
  "url": "https://youtube.com/watch?v=abc123",
  "platform": "youtube",
  "creator": "ChannelName",
  "duration": 3827,
  "views": 2700000,
  "likes": 53237,
  "comments": 1700,
  "thumbnail": "https://i.ytimg.com/vi/abc123/maxresdefault.jpg",
  "score": 9.42,
  "confidence": "high",
  "breakdown": {
    "engagement": 10.0,
    "velocity": 10.0,
    "longform": 10.0,
    "momentum": 9.8
  },
  "reason": [
    "High engagement rate suggests strong audience retention",
    "Above-average view velocity for this niche",
    "Long-form content with multiple clip angles",
    "Strong like-to-view ratio indicates quality content"
  ],
  "clipAngles": ["Highlight reel", "Key insight moment"],
  "viralPotential": 9,
  "monetizationPotential": 5,
  "riskNote": null,
  "isShortlisted": false
}
```

---

## 11. Timeline & Milestones (Aktual)

| Phase | Status | Deliverables |
|-------|--------|--------------|
| **Phase 1: Setup** | ✅ **COMPLETE** | Next.js 14 scaffolding, Laravel 13 setup, SQLite, shadcn/ui, dark theme, Docker Compose |
| **Phase 2: Components** | ✅ **COMPLETE** | 13 components: DiscoveryForm, CandidateCard, CandidateDetail, ResultsList, Navbar, ExportButton, ErrorToast, LoadingSkeleton, 6 shadcn/ui components |
| **Phase 3: Pages** | ✅ **COMPLETE** | `/` (Discovery), `/shortlist`, `/settings`, App layout with AppProvider + Navbar + Toaster |
| **Phase 4: Integration** | ✅ **~95%** | 8 API routes, real yt-dlp scraping, ClipPotentialService, CORS, 22 videos in DB, shortlist flow, export CSV/JSON |
| **Phase 5: QA** | ⏳ **Belum** | Security audit, error boundaries, performance, settings page |

### Next Actions (16 June 2026)
1. **Shortlist persistent ke DB** — Migrate from localStorage to Laravel + SQLite (`video_shortlists` pivot table)
2. **Queue Worker systemd** — Systemd service + timer for auto-scrape
3. **QA Phase 5** — Security audit, error boundaries, performance audit, settings page

---

## 12. Build Order (Aktual)

1. ✅ Project skeleton (Next.js + Laravel)
2. ✅ Database schema (migrations: users, videos, clip_analyses, jobs)
3. ✅ TypeScript types + constants + API client
4. ✅ AppContext (useReducer + localStorage sync)
5. ✅ Navbar + layout
6. ✅ DiscoveryForm
7. ✅ API routes + controllers
8. ✅ ScraperService (yt-dlp integration)
9. ✅ ClipPotentialService (scoring engine)
10. ✅ CandidateCard + ResultsList
11. ✅ CandidateDetail (breakdown modal)
12. ✅ Shortlist flow + ExportButton
13. ✅ LoadingSkeleton + ErrorToast
14. ✅ UAT Round 1 + 2 + fixes
15. ✅ GitHub push (MVP complete)

**MVP selesai jika:**
- ✅ YouTube search jalan via yt-dlp
- ✅ Scoring engine memproses setiap video
- ✅ Shortlist + export CSV/JSON functional
- ✅ UI real-time render candidates
- ✅ Build zero errors

---

## 13. Limitation & Known Gaps

| Gap | Dampak | Rencana Fix |
|-----|--------|-------------|
| Shortlist localStorage-only | Hilang jika ganti browser/device | Migrasi ke DB (pivot table) |
| Scraping sinkron | UI loading sampai scraping selesai | Queue worker + polling |
| YouTube-only | Tidak support Twitch/TikTok/etc | Adapter pattern future |
| Tidak ada auth | Siapapun bisa akses API | Auth basic di Phase 5 |
| Settings page placeholder | Preferences tidak bisa diubah | Implement form di Phase 5 |
| Error belum semua ke toast | Beberapa error hanya di console | Error boundary + toast handler |
| Scoring cap di 100% untuk video populer | Semua video bagus dapat score max | Tuning scaling function |

---

## 14. Failure Modes & Recovery (Aktual)

### yt-dlp gagal / timeout
- **Symptom**: Console error "network issue", response error
- **Cause**: YouTube rate limit, network issue, query terlalu kompleks
- **Fix**: Retry otomatis di ScraperService, timeout 120s di axios

### Hydration error (Next.js SSR)
- **Symptom**: Console error "Hydration failed" + component crash
- **Cause**: localStorage data tidak cocok dengan server-rendered HTML
- **Fix**: `isClient` / `ready` guard + suppressHydrationWarning

### Duplikasi video
- **Symptom**: Video sama muncul di multiple queries
- **Cause**: URL yang sama discrape lagi
- **Fix**: Deduplikasi by URL di ScraperService → skip + gunakan record lama

### Database SQLite concurrent write
- **Symptom**: "database is locked" error
- **Cause**: Multiple concurrent writes ke SQLite
- **Fix**: WAL mode minimal, satu job at a time

---

## 15. Glossary

| Term | Definisi |
|------|----------|
| **Long-form** | Video dengan durasi ≥8 menit (480 detik) |
| **Clip potential** | Skor 0-10 yang mengukur seberapa layak video dijadikan short clip |
| **Breakdown** | 4 dimensi scoring: engagement, velocity, longform, momentum |
| **Confidence** | high/medium/low — reliability dari score berdasarkan data yang ada |
| **Clip angle** | Ide sudut potong untuk short clip (e.g., "Highlight reel", "Key insight") |
| **yt-dlp** | YouTube downloader CLI tool yang digunakan untuk scraping metadata |
| **Scraping** | Proses mencari dan mengambil metadata video dari YouTube |
