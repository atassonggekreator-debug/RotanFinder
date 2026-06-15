# RotanFinder — Recovery Agent

## 📅 SESSION: Monday, 15 June 2026 (17:12 WITA)

**Project:** RotanFinder Full Stack  
**Stack:** Next.js 14 (Frontend :3000) + Laravel 13 (API :8000)  
**DB:** SQLite at `/home/iwan/AI_Company/projects/rotan-finder/database/database.sqlite`  
**Status:** PHASE 4 ✅ (~95% complete)

---

## ✅ YANG SELESAI HARI INI

### Core Deliverables
- ✅ **Real YouTube scraping** via yt-dlp `ytsearch` — POST /api/discover triggers live search, dedup by URL, filter shorts <8min
- ✅ **22 videos** in DB with full metadata (title, thumbnail, views, likes, comments, duration, uploader)
- ✅ **Scoring engine** — ClipPotentialService: 40% engagement, 30% velocity, 20% longform, 10% momentum
- ✅ **Breakdown modal** — Real calculated values (not 0%) for engagement/velocity/longform/momentum
- ✅ **Shortlist flow** complete — toggle on card, counter in navbar, shortlist tab, export CSV/JSON
- ✅ **4-column responsive grid** — `grid-cols-1` → `sm:2` → `lg:3` → `xl:4`
- ✅ **Hydration error fixed** — Navbar badge (`isClient` guard), ShortlistPage (`ready` guard)
- ✅ **Anonymous yt-dlp** — `--no-cookies` flag added for non-personalized results

### Bugs Fixed (11)
1. Card rendering — field name mismatch API vs frontend (`view_count`→`views`, `rec`→`confidence`)
2. Max Results slider — added to DiscoveryForm (1-20 range)
3. API timeout — 30s → 120s (yt-dlp scraping was timing out)
4. Modal dialog — click card opens breakdown modal
5. Breakdown 0% — now calculates from real video data
6. Navbar hydration — badge mismatch SSR vs client
7. Shortlist page hydration — `ready` guard
8. Export buttons — shortlist page rewritten with cards + export
9. 4-column grid — `xl:grid-cols-4` for >1280px
10. State persistence — `results` now saved alongside `shortlist` in localStorage key `rotanfinder_state`
11. Sort by — data types verified (int/float), sort logic confirmed working

### Improvements (2)
1. **`--no-cookies`** — yt-dlp now runs without cookies (anonymous YouTube search, non-personalized)
2. **`--exclude-scraped`** — CLI flag for `rotan:list` command, filters videos <24h

### UAT Final
- Round 1: 1/23 pass → 22 bugs found
- Round 2: 18/23 pass → 5 remaining (mostly cascading/expected)
- Final: Modal ✓, Sort ✓, Console clean ✓, Responsive ✓

---

## ⏳ YANG BELUM SELESAI

### Phase 4 Remaining
- ⏳ **Shortlist persistent ke DB** — Saat ini masih localStorage-only. Perlu pindah ke backend (Laravel ShortlistController + video_shortlists pivot table)
- ⏳ **Queue Worker systemd** — Auto-scrape scheduler. `php artisan queue:work` atau systemd timer untuk batch scraping terjadwal

### Phase 5: QA
- ⏳ Security audit (rate limiting, input validation, SQL injection, XSS)
- ⏳ Code review checklist
- ⏳ Performance audit (Lighthouse, bundle analysis)
- ⏳ Error boundaries per page
- ⏳ Network error → toast UX
- ⏳ Settings page (still placeholder)
- ⏳ README.md

### Future
- User auth + multi-tenant (SaaS conversion)
- FastAPI integration (AI-powered scoring with LLM)
- Export to X/TikTok (auto-post)
- Dark/Light theme toggle
- Chart analytics dashboard
- Notification system (WebSocket)

---

## 📌 KEPUTUSAN PENTING

1. **Pivot dari short-form → long-form videos** — Sistem difokuskan untuk long-form content (YouTube, podcast) yang punya potensi klip (clip angles), bukan short-form seperti TikTok/Reels
2. **Pivot dari Python/FastAPI → Laravel 13 + Next.js 14** — Backend pindah dari FastAPI ke Laravel 13 (PHP) untuk MVP speed. Next.js 14 untuk frontend modern
3. **Scoring algorithm** — ClipPotentialService: `40% engagement + 30% velocity + 20% longform + 10% momentum`
4. **No wrapper in API response** — Backend returns raw data directly, not `{ success, data }`. Matches frontend expectations
5. **Two separate servers** — Frontend :3000 (Next.js dev) + Backend :8000 (Laravel serve)
6. **Synchronous scraping** — POST /api/discover blocks until yt-dlp finishes. Timeout 120s. Acceptable for MVP
7. **CDN instead of Vite** — Laravel Blade dashboard forced to CDN (Tailwind + Alpine) due to ViteManifestNotFoundException

---

## 🎯 RENCANA BESOK (16 June 2026)

1. **Shortlist persistent ke DB** — Create `video_shortlists` pivot table, update ShortlistController, change frontend to use API instead of localStorage
2. **Queue Worker systemd** — Setup systemd service + timer for auto-scrape scheduler
3. **QA Phase 5** — Security audit, error boundaries, performance audit, settings page

---

## 🖥️ ENVIRONMENT

```bash
# Frontend (Next.js 14)
cd /home/iwan/RotanFinder/frontend && npm run dev  # port 3000

# Backend (Laravel 13)
cd /home/iwan/AI_Company/projects/rotan-finder && php artisan serve --port=8000

# PHP binary
/home/iwan/.config/herd-lite/bin/php

# Database
/home/iwan/AI_Company/projects/rotan-finder/database/database.sqlite

# Task tracking
/home/iwan/RotanFinder/task.md

# UAT checklist
/home/iwan/RotanFinder/UAT_CHECKLIST.md
```

---

## 🔑 FILES & LINKS (Quick Reference)

| Area | Path |
|------|------|
| Types | `/home/iwan/RotanFinder/frontend/lib/types.ts` |
| API Client | `/home/iwan/RotanFinder/frontend/lib/api.ts` |
| Context | `/home/iwan/RotanFinder/frontend/context/AppContext.tsx` |
| Constants | `/home/iwan/RotanFinder/frontend/lib/constants.ts` |
| Discovery Form | `/home/iwan/RotanFinder/frontend/components/DiscoveryForm.tsx` |
| Results List | `/home/iwan/RotanFinder/frontend/components/ResultsList.tsx` |
| Candidate Card | `/home/iwan/RotanFinder/frontend/components/CandidateCard.tsx` |
| Candidate Detail | `/home/iwan/RotanFinder/frontend/components/CandidateDetail.tsx` |
| Navbar | `/home/iwan/RotanFinder/frontend/components/Navbar.tsx` |
| Export Button | `/home/iwan/RotanFinder/frontend/components/ExportButton.tsx` |
| Shortlist Page | `/home/iwan/RotanFinder/frontend/app/shortlist/page.tsx` |
| API Routes | `/home/iwan/AI_Company/projects/rotan-finder/routes/api.php` |
| Discovery Ctrl | `/home/iwan/AI_Company/projects/rotan-finder/app/Http/Controllers/Api/DiscoveryController.php` |
| Scraper Service | `/home/iwan/AI_Company/projects/rotan-finder/app/Services/ScraperService.php` |
| Scoring Engine | `/home/iwan/AI_Company/projects/rotan-finder/app/Services/ClipPotentialService.php` |
| Shortlist Ctrl | `/home/iwan/AI_Company/projects/rotan-finder/app/Http/Controllers/Api/ShortlistController.php` |
| Export Ctrl | `/home/iwan/AI_Company/projects/rotan-finder/app/Http/Controllers/Api/ExportController.php` |
| RotanList Cmd | `/home/iwan/AI_Company/projects/rotan-finder/app/Console/Commands/RotanList.php` |
