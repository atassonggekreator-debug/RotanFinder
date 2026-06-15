# TASK.MD – RotanFinder Frontend Build

**Project:** RotanFinder  
**Scope:** Frontend UI Layer (Next.js 14 → Laravel 13 API → FastAPI)  
**Reference:** `/home/iwan/RotanFinder/PLANNING.md`, `/home/iwan/RotanFinder/prd.md`  
**Status:** 🔄 IN PROGRESS  
**Rule:** Append only — jangan edit/hapus entry yang sudah selesai. Tambahkan baris baru di bawah.

---

## PRE-BUILD

- [x] Review dan approve PLANNING oleh Iwan (2026-06-13)
- [x] Fix Context7 MCP connection (API key updated, full connect after gateway restart)
- [x] **GATEWAY RESTART**: Done — dev server verified working (port 3000)
- [x] Verify Laravel 13 docs via Context7 — Context7 MCP belum connect, skipped for now

---

## PHASE 1: PROJECT SETUP

### 1.1 Create Next.js Project
- [x] `npx create-next-app@latest frontend --typescript --tailwind --app --use-npm`
- [x] Remove `src/` di root jika ada konflik (tidak ada src/ — bersih)
- [x] Configure `next.config.ts`: output: 'standalone', images: unoptimized
- [x] Verify: `npm run dev` works (error-free, port 3000)

### 1.2 Install Frontend Dependencies
- [x] `npm install axios lucide-react`
- [x] `.npmrc` for `shadcn@latest` to auto-confirm
- [x] `npx shadcn@latest init` (configure components.json, `baseColor` `neutral`, `iconLibrary` `lucide`)
- [x] `npx shadcn@latest add button card input select badge dialog sonner table skeleton form label separator`
- [x] Install React Context di `/home/iwan/RotanFinder/frontend/context/AppContext.tsx`

### 1.3 Project Structure
- [x] Create folder structure:
  ```
  frontend/
  ├── app/
  │   ├── layout.tsx
  │   ├── page.tsx (Discovery)
  │   ├── shortlist/page.tsx
  │   ├── settings/page.tsx (optional MVP)
  │   └── globals.css
  ├── components/
  │   ├── ui/ (shadcn/ui components)
  │   ├── DiscoveryForm.tsx
  │   ├── ResultsList.tsx
  │   ├── CandidateCard.tsx
  │   ├── CandidateDetail.tsx
  │   ├── ShortlistView.tsx
  │   ├── Navbar.tsx
  │   ├── LoadingSkeleton.tsx
  │   └── ExportButton.tsx
  ├── lib/
  │   ├── api.ts
  │   ├── types.ts
  │   ├── utils.ts
  │   └── constants.ts
  ├── context/
  │   └── AppContext.tsx
  ```
- [x] Verify import paths: all imports from correct depth (../, ../../, etc) — using @/ path aliases
- [x] Test build: `npm run build` error-free

---

## PHASE 2: CORE COMPONENTS

### 2.1 Types & Constants (lib/)
- [x] Define `types.ts`:
  - `SearchQuery` interface
  - `Candidate` interface
  - `CandidateBreakdown` interface
  - `ShortlistAction` type
  - `ExportFormat` type
  - `UserPreferences` interface
  - `AppState` interface
  - `AppAction` union type for reducer
- [x] Define `constants.ts`:
  - `PLATFORMS` list
  - `DEFAULT_DURATION_MIN`, `DEFAULT_DURATION_MAX`
  - `DEFAULT_BUDGET_USD`
  - `SORT_OPTIONS`
  - `CONFIDENCE_COLORS`
  - `PLATFORM_BADGES`

### 2.2 API Client (lib/api.ts)
- [x] Create axios instance with `baseURL: process.env.NEXT_PUBLIC_LARAVEL_API_URL || 'http://localhost:8000/api'`
- [x] Implement functions:
  - `submitDiscovery(query)` → POST /api/discover
  - `getResults(searchId)` → GET /api/discover/{id}
  - `toggleShortlist(id, action)` → POST /api/shortlist
  - `getShortlist()` → GET /api/shortlist
  - `exportData(format, ids)` → POST /api/export (blob response)
  - `getPreferences()` → GET /api/preferences (optional)
  - `updatePreferences(prefs)` → PUT /api/preferences (optional)
- [x] Error interceptor: catch 422, 500, 429 → throw typed errors
- [x] Request interceptor: attach headers

### 2.3 State Management (context/AppContext.tsx)
- [x] Define AppState interface + initial state
- [x] Implement reducer with actions:
  - `SET_LOADING` • `SET_RESULTS` • `SET_ERROR`
  - `TOGGLE_SHORTLIST` • `SET_ACTIVE_TAB`
  - `SELECT_CANDIDATE` • `CLEAR_ERROR`
- [x] Create AppProvider component
- [x] Create `useAppContext` custom hook
- [x] localStorage sync untuk shortlist (fallback)
- [x] Test: provider wraps app, context accessible in components

### 2.4 Navbar Component
- [x] Render: Logo + "RotanFinder" + tab navigation (Discovery | Shortlist | Settings)
- [x] Active tab indicator
- [x] Shortlist count badge
- [x] Responsive: sticky, backdrop-blur
- [ ] Test: navigation switches active tab

### 2.5 DiscoveryForm Component
- [x] Fields: niche, keywords (chips), platforms (checkbox), duration, aiRerank, budget
- [x] Validation: niche required, min 1 platform, duration_min ≤ duration_max
- [x] Submit → set loading → call submitDiscovery → on success set results → on error set error
- [x] Loading state on submit button

### 2.6 ResultsList Component
- [x] Props: candidates from AppContext, isLoading, error
- [x] Sort dropdown (Score/Views/Engagement/Freshness)
- [x] Filter: platform chips (All / YouTube / Twitch / FB / TikTok)
- [x] Render: grid of CandidateCard
- [x] Loading state: skeleton cards (6)
- [x] Empty state: "No results found" + suggestion
- [x] Error state: error message + Retry button (CLEAR_ERROR)

### 2.7 CandidateCard Component
- [x] Display: thumbnail, title + tooltip, platform badge, views/likes, score bar (gradient), confidence badge, risk note
- [x] Actions: Shortlist toggle (heart), Detail button
- [x] Hover: lift/shadow effect

### 2.8 CandidateDetail Component (Dialog)
- [x] shadcn/ui Dialog: full title + URL link
- [x] Score breakdown per dimension (4 bars: engagement/velocity/longform/momentum)
- [x] Reason bullets, Clip angle badges
- [x] Viral + Monetization badges, Risk note

### 2.9 ShortlistView Component
- [x] Render: shortlist from AppContext on mount
- [x] Empty state: "No shortlisted candidates yet"
- [x] Export section with ExportButton

### 2.10 LoadingSkeleton Component
- [x] Props: count (default: 6)
- [x] Skeleton cards matching CandidateCard layout
- [x] Animated pulse

### 2.11 ErrorToast Component (Sonner)
- [x] Toast notification on error (Sonner)
- [x] Auto-dismiss 5s + CLEAR_ERROR

### 2.12 ExportButton Component
- [x] Props: format (csv|json), ids
- [x] On click: exportData → Blob download
- [x] Loading state: spinner

---

## PHASE 3: PAGES & ROUTING

### 3.1 Root Layout (app/layout.tsx)
- [x] Import TailwindCSS globals
- [x] Wrap with AppProvider
- [x] Include Navbar
- [x] ErrorToast + Sonner Toaster
- [x] Metadata: title "RotanFinder — Clip Discovery Engine"

### 3.2 Discovery Page (app/page.tsx)
- [x] Layout: DiscoveryForm on top → ResultsList below
- [x] State: form + results co-exist (via AppContext)
- [x] Initial state: form visible, results empty
- [x] After submit: results section renders

### 3.3 Shortlist Page (app/shortlist/page.tsx)
- [x] Layout: empty state with icon
- [x] Shows shortlist from cache
- [x] Route: /shortlist

### 3.4 Settings Page (app/settings/page.tsx) (Optional MVP)
- [x] Placeholder page
- [x] Route: /settings

### 3.5 Error Boundary (optional)
- [ ] Wrap main content in error boundary
- [ ] Fallback: "Something went wrong" + refresh button

---

## PHASE 4: INTEGRATION & POLISH

### 4.1 API Integration Testing
- [x] Laravel API routes created: discover, shortlist, export, preferences
- [x] GET /api/discover returns all 8 videos (real data)
- [x] POST /api/discover — **REAL yt-dlp scraping + scoring LIVE** 🔥
- [x] POST /api/shortlist — session-based mock CRUD
- [x] POST /api/export — JSON/CSV blob response
- [x] GET/PUT /api/preferences — user preferences
- [x] CORS: Access-Control-Allow-Origin: * enabled
- [x] All routes smoke-tested via curl
- [x] Response format harmonized with frontend expectations
- [x] ScraperService.searchAndScrape() — searches YouTube, skips shorts, deduplicates
- [x] New videos auto-scored via ClipPotentialService
- [x] Duration filter applied server-side
- [x] `meta` returned: new/existing/filtered counts
- [ ] Manual UAT via browser (Discovery → submit → results render)

### 4.2 UI/UX Polish
- [ ] Responsive design verification (mobile 360px → desktop 1400px)
- [ ] Score bar color gradient (low → medium → high)
- [ ] Badge consistency (confidence: red/yellow/green)
- [ ] Loading skeleton animation duration
- [ ] Toast position and duration

### 4.3 Error Recovery
- [ ] Retry logic: max 2 retries for timeout
- [ ] Empty states: proper messages
- [ ] Form data persisten setelah error submit

### 4.4 Build & Deploy Test
- [ ] `npm run build` error-free
- [ ] `npm run start` → localhost:3000 loads correctly
- [ ] Static file serving works
- [ ] Environment variables loaded correctly

---

## PHASE 5: QA & REVIEW

### 5.1 Self-Review
- [ ] All components handle loading, error, empty states
- [ ] All API calls have error handling
- [ ] All forms have validation
- [ ] No console errors
- [ ] No TypeScript errors

### 5.2 QA Reviewer Audit
- [ ] Security: no XSS vulnerabilities in API response rendering
- [ ] Edge cases: empty result, API timeout, rapid clicks
- [ ] Performance: no unnecessary re-renders
- [ ] Accessibility: basic form labels, keyboard navigation

### 5.3 Manual UAT
- [ ] 5+ test queries with different niches
- [ ] Shortlist add/remove across sessions (localStorage)
- [ ] Export CSV/JSON format validation
- [ ] Mobile responsive test

---

## FUTURE (Phase 2+)

- [ ] Dark mode
- [ ] Real-time query status polling
- [ ] Candidate comparison mode
- [ ] Search history
- [ ] Multi-user auth (Laravel Sanctum)
- [ ] Notification on long-running query completion
- [ ] OAuth/social login for team workflow

---

## NOTES

- Context7 gateway restart diperlukan untuk full MCP connection
- PLANNING.md di `/home/iwan/RotanFinder/PLANNING.md`
- PRD di `/home/iwan/RotanFinder/prd.md`
- Build dengan `standalone` output mode (Next.js)
- API calls selalu ke Laravel (port 8000), bukan ke FastAPI (port 8001) langsung
