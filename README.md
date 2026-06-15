# RotanFinder 🎬🔥

AI-powered clip potential finder for long-form YouTube content. Scrapes YouTube, scores videos by engagement/velocity/longform/momentum, and helps you find the best clips.

## Stack

- **Frontend:** Next.js 14 (App Router) + Tailwind + shadcn/ui
- **Backend:** Laravel 13 (PHP) + SQLite
- **Scraping:** yt-dlp (YouTube search)
- **Scoring:** ClipPotentialService (40% engagement, 30% velocity, 20% longform, 10% momentum)

## Structure

```
RotanFinder/
├── frontend/     — Next.js 14 app (port 3000)
├── backend/      — Laravel 13 API (port 8000)
├── docs/         — Progress logs, UAT checklists
└── README.md
```

## Quick Start

```bash
# Backend
cd backend
cp .env.example .env
php artisan key:generate
php artisan serve --port=8000

# Frontend
cd frontend
npm install
npm run dev  # port 3000
```

## MVP Status: PHASE 4 ✅ (~95%)

- Real YouTube scraping via yt-dlp
- 22+ videos in DB with full metadata
- Scoring engine with 4 dimensions
- Shortlist + export CSV/JSON
- 4-column responsive grid
- Hydration-safe (Next.js SSR)
