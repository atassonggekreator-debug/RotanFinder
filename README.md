# RotanFinder 🎬🔥

AI-powered clip potential finder for long-form YouTube content. Scrapes YouTube, scores videos by engagement/velocity/longform/momentum, and helps content creators find viral-worthy clip moments.

## 🚀 Features

- **YouTube Discovery** — Real-time search via yt-dlp with duration filters (8-120 min)
- **4-Dimension Scoring** — Engagement (40%) + Velocity (30%) + Longform (20%) + Momentum (10%)
- **Shortlist** — Persistent to database with localStorage cache
- **Export** — CSV/JSON of your shortlisted videos
- **Queue Worker** — Background job processing via systemd
- **Responsive Grid** — 1→2→3→4 columns (mobile to desktop)

## 🏗️ Stack

| Layer | Tech |
|-------|------|
| Frontend | Next.js 14 (App Router) + Tailwind CSS + shadcn/ui |
| Backend | Laravel 13 (PHP 8.5) |
| Database | SQLite (dual: `database.sqlite` + `queue.sqlite`) |
| Scraping | yt-dlp (YouTube search, anonymous) |
| Queue | systemd user service (database driver) |
| Auth | None (MVP — single-user laptop-first) |

## 📁 Structure

```
RotanFinder/
├── frontend/     — Next.js 14 app (port 3000)
├── backend/      — Laravel 13 API (port 8000)
├── docs/         — PRD, progress logs
└── README.md
```

## ⚡ Quick Start

```bash
# 1. Backend API
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --port=8000

# 2. Frontend
cd frontend
npm install
npm run dev          # → localhost:3000

# 3. Queue Worker (optional — for background scraping)
systemctl --user start rotan-queue
```

## 🔒 Security

- **Rate limiting**: `5 req/min` (scrape), `30 req/min` (writes), `60 req/min` (reads)
- **Input validation**: All endpoints validated (string max, integer bounds, enum constraints)
- **XSS**: No `dangerouslySetInnerHTML` — React auto-escapes all output
- **SQL injection**: Prevented via Eloquent ORM + parameterized queries

## 📊 Scoring Algorithm

| Dimension | Weight | What it measures |
|-----------|--------|------------------|
| Engagement | 40% | likes + comments vs view count |
| Velocity | 30% | rate of engagement per day since upload |
| Longform Fit | 20% | how close duration is to ideal clip range (8-15 min = max) |
| Momentum | 10% | combined signal: score × engagement intensity |

## 🛠️ Queue Worker

```bash
systemctl --user start   rotan-queue   # Start worker
systemctl --user stop    rotan-queue   # Stop worker
systemctl --user status  rotan-queue   # Check status
journalctl --user -u rotan-queue -f    # Live logs
```

Config: `--sleep=3 --tries=3 --max-time=3600 --memory=256`

## 📈 API Endpoints

| Method | Path | Description | Rate Limit |
|--------|------|-------------|------------|
| GET | `/api/discover` | List scraped videos | 60/min |
| POST | `/api/discover` | Scrape YouTube | 5/min |
| GET | `/api/shortlist` | Get shortlisted videos | 60/min |
| POST | `/api/shortlist` | Add to shortlist | 30/min |
| DELETE | `/api/shortlist/{id}` | Remove from shortlist | 30/min |
| GET | `/api/preferences` | Get preferences | 60/min |
| PUT | `/api/preferences` | Update preferences | 30/min |
| POST | `/api/export` | Export as CSV/JSON | 30/min |

## 📋 MVP Status

```
Phase 1: Discovery       ✅ COMPLETE
Phase 2: Shortlist       ✅ COMPLETE
Phase 3: Queue Worker    ✅ COMPLETE
Phase 4: MVP Polish      ✅ COMPLETE (~99%)
Phase 5: QA              ✅ COMPLETE (rate limit, validation, XSS, perf)
```

## 🔗 Links

- GitHub: https://github.com/atassonggekreator-debug/RotanFinder
- PRD: `docs/PRD.md`
