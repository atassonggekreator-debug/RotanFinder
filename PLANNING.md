# PLANNING – RotanFinder Frontend (Laravel 13)

**Date:** 2026-06-13  
**Scope:** Frontend UI layer only  
**Reference:** `/home/iwan/RotanFinder/prd.md`  
**Status:** PLANNING (awaiting approval)

---

## 1. ARCHITECTURE OVERVIEW

### Tech Stack Decision
| Layer | Tech | Notes |
|---|---|---|
| **API Server** | Laravel 13 | HTTP API, routing, auth, ORM, validation |
| **Frontend** | Next.js 14 App Router | SSR/CSR, TailwindCSS, TypeScript |
| **UI Components** | shadcn/ui | Accessible, copy-paste, no vendor lock |
| **State Management** | React Context + localStorage | Lightweight for MVP |
| **HTTP Client** | axios → call Laravel API | Not Next.js API routes |
| **Icons** | lucide-react | Standard, lightweight |
| **Communication Pattern** | Next.js → Laravel API → FastAPI (scoring engine) | |

### Architecture: Next.js → Laravel → FastAPI
```
User (Browser) → Next.js Frontend (port 3000)
                    → Laravel API (port 8000)
                          → FastAPI Scoring Engine (port 8001)
```

- **Next.js**: presents UI, handles routing, client components
- **Laravel 13**: HTTP API layer, handles auth, request validation, database ORM, job queue, caching
- **FastAPI (Python)**: source discovery, metadata collection, scoring engine, AI provider abstraction

Next.js **tidak** memanggil FastAPI langsung. Semua traffic melalui Laravel API terlebih dahulu.

### Why NOT Next.js API Routes
- API routes di Next.js tidak punya middleware stack bawaan (auth, rate limit, validation)
- Laravel 13 menyediakan API Resources, Form Requests, Middleware, Queue out-of-the-box
- Separation of concerns: Laravel handles HTTP concerns, Next.js handles UI concerns

---

## 2. UI FEATURES (Frontend Only)

### 2.1 Discovery Input Form
- **Fields:**
  - Niche / Topic (text input)
  - Keywords (multi-tag input, chip-based)
  - Platform selection (checkbox: YouTube, Twitch, Facebook, TikTok)
  - Duration filter (min–max seconds, slider)
  - AI rerank toggle (checkbox)
  - Budget cap (USD, number input, optional, default <$0.50)

- **Validation:**
  - niche wajib tidak kosong
  - minimal 1 platform
  - duration_min ≤ duration_max

- **Submit action:**
  - POST `/api/discover` (Laravel API)
  - Loading spinner + disable button
  - Error toast jika gagal

### 2.2 Results Display (Recommendation List)
- **Layout:** Card grid or Table (decide based on UX)
- **Per candidate:**
  - Title (link ke source URL)
  - Platform badge (YouTube, Twitch, etc)
  - Score total + mini bar breakdown
  - Creator name
  - Views + likes + comments (compact)
  - Confidence badge (low/medium/high)
  - Risk badge (if exists)
  - Action buttons: Shortlist ✅, Reject ❌, Detail ℹ️

- **Sort & filter:**
  - Sort by: Score, Views, Engagement, Freshness
  - Filter by: Platform, Confidence, Risk level
  - Search within results by title/creator

- **Detail panel (expandable):**
  - URL, full breakdown, clip ideas, viral potential, monetization potential

### 2.3 Shortlist Management
- **View:** List shortlisted candidates
- **Actions:**
  - Remove from shortlist
  - Export CSV / JSON
- **Persistence:** localStorage (future via Laravel API user account)

### 2.4 Approve / Reject Workflow
- Approve → move to shortlist
- Reject → mark not suitable (hide from list)
- Feedback toast

### 2.5 Navigation
- Logo + "RotanFinder"
- Tabs: Discovery | Shortlist | Settings
- Active tab indicator

### 2.6 Error & Loading States
- **Loading:** skeleton placeholders
- **API error:** toast + Retry button
- **Empty results:** message + suggest different keywords
- **Form validation:** inline field errors

### 2.7 Settings (Optional MVP)
- Default platforms, duration range, budget cap
- Save preferences to localStorage

---

## 3. PAGE STRUCTURE (Next.js App Router)

```
app/
├── layout.tsx               # Root layout (navbar, footer)
├── page.tsx                 # Discovery (form + results)
├── shortlist/
│   └── page.tsx             # Shortlist view + export
├── settings/
│   └── page.tsx             # User preferences (optional MVP)
├── components/
│   ├── ui/                  # shadcn/ui components (Button, Card, Input, Select, Badge, Dialog, Table, etc)
│   ├── DiscoveryForm.tsx
│   ├── ResultsList.tsx
│   ├── CandidateCard.tsx
│   ├── CandidateDetail.tsx
│   ├── ShortlistView.tsx
│   ├── Navbar.tsx
│   ├── LoadingSkeleton.tsx
│   ├── ErrorToast.tsx
│   └── ExportButton.tsx
├── lib/
│   ├── api.ts               # axios client → Laravel API
│   ├── types.ts             # TypeScript interfaces
│   ├── utils.ts             # Formatting, helpers
│   └── constants.ts         # Platforms, default values
├── context/
│   └── AppContext.tsx        # React Context for state
└── styles/
    └── globals.css          # Tailwind imports + custom
```

---

## 4. API ENDPOINTS (Laravel 13)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/discover` | Submit discovery query → return candidates |
| GET | `/api/discover/{id}` | Get results for previous query |
| POST | `/api/shortlist` | Add/remove from shortlist |
| GET | `/api/shortlist` | Get all shortlisted candidates |
| POST | `/api/export` | Export shortlist as CSV/JSON |
| GET | `/api/preferences` | Get user preferences |
| PUT | `/api/preferences` | Update user preferences |

### Request/Response Contracts (Frontend → Laravel)

#### POST /api/discover
```json
{
  "niche": "AI tools",
  "keywords": ["AI agent", "workflow"],
  "platforms": ["youtube", "twitch"],
  "duration_seconds_min": 20,
  "duration_seconds_max": 180,
  "ai_rerank_enabled": true,
  "budget_usd_max": 0.25
}
```

Response:
```json
{
  "data": {
    "search_id": "q_001",
    "candidates": [
      {
        "id": "rec_001",
        "source_url": "https://youtube.com/watch?v=abc",
        "platform": "youtube",
        "title": "AI Agent Demo",
        "creator": "ExampleCreator",
        "duration_seconds": 620,
        "views": 240000,
        "likes": 14000,
        "comments": 820,
        "score": 86.4,
        "confidence": "high",
        "breakdown": {
          "trend_velocity": 14.2,
          "engagement_ratio": 11.4,
          "hook_strength": 9.1
        },
        "reason_summary": ["views growth tinggi", "engagement kuat"],
        "risk_note": "Check copyright",
        "clip_angle_ideas": ["3-step demo", "before-after speed"],
        "viral_potential": "high",
        "monetization_potential": "medium",
        "shortlisted": false
      }
    ],
    "total": 45,
    "page": 1,
    "per_page": 20
  }
}
```

#### POST /api/shortlist
```json
{
  "recommendation_id": "rec_001",
  "action": "add"
}
```

Response:
```json
{
  "data": {
    "success": true,
    "shortlist_count": 5
  }
}
```

#### POST /api/export
```json
{
  "format": "csv",
  "recommendation_ids": ["rec_001", "rec_002"]
}
```

Response: downloadable file (CSV/JSON)

---

## 5. STATE MANAGEMENT

```typescript
interface AppState {
  // Discovery
  searchQuery: SearchQuery | null;
  results: Recommendation[];
  isLoading: boolean;
  error: string | null;
  
  // Shortlist
  shortlistedIds: Set<string>;
  
  // UI
  activeTab: 'discovery' | 'shortlist' | 'settings';
  selectedCandidate: Recommendation | null;
  
  // Actions
  submitSearch: (q: SearchQuery) => Promise<void>;
  toggleShortlist: (id: string) => Promise<void>;
  exportShortlist: (format: 'csv' | 'json', ids: string[]) => Promise<void>;
  clearError: () => void;
}
```

### Persistence
- **localStorage:** `rotanfinder_shortlist` (fallback, for quick recovery)
- **Laravel API:** authoritative shortlist storage (database)

---

## 6. COMPONENT SPECIFICATIONS

### DiscoveryForm
- **Props:** none (uses Context)
- **State:** form fields, validation errors, submitting
- **Behavior:** validates, calls Laravel POST /api/discover, sets results in Context

### ResultsList
- **Props:** candidates array, isLoading, sortKey, filters
- **Behavior:** sort + filter client-side (API returns all candidates for current page)
- **Child:** CandidateCard, Pagination (load more)

### CandidateCard
- **Props:** candidate object, isShortlisted, onToggleShortlist, onReject, onDetail
- **Display:** compact card with badges, score bar, action buttons

### CandidateDetail
- **Props:** candidate, open, onClose
- **Display:** dialog/modal with full breakdown, clip ideas, risk note, viral estimate

### ShortlistView
- **Props:** shortlistedIds (from Context)
- **Behavior:** fetches shortlist from Laravel GET /api/shortlist
- **Child:** ExportButton

### ExportButton
- **Props:** format ('csv'|'json'), ids
- **Behavior:** POST /api/export → trigger download

### Navbar
- **Props:** activeTab, onTabChange
- **Display:** app name + tab navigation

---

## 7. API INTEGRATION (axios → Laravel)

```typescript
// lib/api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_LARAVEL_API_URL || 'http://localhost:8000/api',
  timeout: 30000,
  headers: { 'Accept': 'application/json' }
});

export const rotanFinderApi = {
  discover: (query: SearchQuery) => api.post('/discover', query),
  getResults: (searchId: string) => api.get(`/discover/${searchId}`),
  toggleShortlist: (id: string, action: 'add' | 'remove') => api.post('/shortlist', { recommendation_id: id, action }),
  getShortlist: () => api.get('/shortlist'),
  exportData: (format: 'csv'|'json', ids: string[]) => api.post('/export', { format, recommendation_ids: ids }, { responseType: 'blob' }),
  getPreferences: () => api.get('/preferences'),
  updatePreferences: (prefs: Preferences) => api.put('/preferences', prefs),
};
```

### Error Handling Strategy
- Network error → toast "Connection failed. Check if Laravel is running."
- 422 Validation → field errors di form
- 500 Server → toast "Something went wrong. Try again."
- 429 Rate limit → toast "Too many requests. Wait a moment."

---

## 8. TESTING STRATEGY

### Unit Tests (Jest + React Testing Library)
- Component snapshot tests
- Form validation (empty fields, invalid inputs)
- Context actions (add/remove shortlist, submit search)
- Utility functions (format numbers, truncate text)

### Integration Tests
- Discovery form submit → mock API → results render
- Shortlist toggle → state update → UI re-render
- Export → blob response → download trigger

### Mocking Strategy
- MSW (Mock Service Worker) to mock Laravel API endpoints
- or jest.mock for simplicity in MVP

### Manual UAT Checklist
- [ ] Form validation works (empty niche → error)
- [ ] Submit → loading state → results appear
- [ ] Shortlist add/remove works
- [ ] Export CSV downloads valid file
- [ ] Error states show correct messages
- [ ] localStorage persistence survives page reload
- [ ] Responsive layout (mobile + desktop)

---

## 9. BUILD & DEPLOYMENT

### Development

```bash
# 1. Install deps and run Laravel API
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --port=8000

# 2. Run Python FastAPI for scoring
cd fastapi-engine
uvicorn main:app --port=8001

# 3. Run Next.js frontend
npm install
npm run dev  # http://localhost:3000
```

### Production / Local MVP

```bash
# Build Next.js
npm run build  # → .next/standalone/

# Systemd services (3 total)
# 1. laravel.service  → php artisan serve
# 2. fastapi.service  → uvicorn
# 3. rotanfinder-ui.service → node server.js (Next.js standalone)
```

### Environment Variables
```
NEXT_PUBLIC_LARAVEL_API_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_NAME=RotanFinder
NEXT_PUBLIC_APP_VERSION=1.0.0
```

---

## 10. UI/UX Design Notes

### Layout
- **Max-width container:** 1200px, centered
- **Responsive:** mobile-first breakpoints: sm(640), md(768), lg(1024)
- **Spacing:** Tailwind default (4px grid)

### Color Palette (TailwindCSS)
- Primary: Indigo (for active elements, score badges)
- Success: Emerald (shortlisted)
- Danger: Red (reject, risk)
- Muted: Gray (neutral, secondary)
- Background: White / Gray 50

### Dark Mode
- Optional for Phase 2
- Use Tailwind `dark:` variant

---

## 11. PHASES & MILESTONES

### Phase 1 (MVP)
- [x] Discovery input form (validation + submit)
- [x] Results card list with badges + score
- [x] Shortlist add/remove
- [x] Export CSV/JSON
- [x] Basic error + loading states
- [x] Responsive layout

### Phase 2
- [ ] Settings page (preferences)
- [ ] Dark mode
- [ ] Advanced filtering + search within results
- [ ] Pagination (load more / infinite scroll)
- [ ] Candidate comparison
- [ ] Real-time status for long-running queries

---

## 12. WORKER ASSIGNMENTS

| Worker | Role |
|---|---|
| **Architect** (you) | Approve planning, delegate to coders |
| **Coder** | Build all frontend components, pages, API integration |
| **QA Reviewer** | Audit code after build (accessibility, security, edge cases) |

### Coder Execution Instructions
After planning approved:
1. Create Next.js project `npx create-next-app@latest`
2. Install dependencies: shadcn/ui, lucide-react, axios
3. Build components starting from `AppContext` → `api.ts` → UI components → pages
4. Use Context7 to verify Laravel 13 API docs, shadcn/ui component usage
5. Append progress to `task.md`

---

## 13. REFERENCE DOCUMENTS

- **PRD:** `/home/iwan/RotanFinder/prd.md` (Section 15, 17, 18)
- **Workers:** architect.md, coder.md, qa_reviewer.md
- **Skill:** nextjs-frontend (import paths, output mode, TypeScript gotchas)

---

## 14. DECISION LOG (Updated for Laravel 13)

| Decision | Choice | Rationale | Tradeoff |
|---|---|---|---|
| API Layer | Laravel 13 | ORM, middleware, auth built-in | More setup than Next.js API routes |
| Frontend | Next.js 14 | Modern SSR, TypeScript, App Router | Overkill if pure static, but flexibility wins |
| UI Components | shadcn/ui | Accessible, copy-paste, no vendor lock | Maintenance on custom variants needed |
| State Mgmt | React Context | Lightweight for MVP | Redux if complexity grows later |
| HTTP Client | axios | Simple, interceptors, error handling | fetch also works |
| CSS | TailwindCSS | Utility-first, minimal overhead | Some inline classes |
| Persistence | localStorage + Laravel DB | Fallback + authoritative | Sync needed between frontend/backend |
| Export | CSV + JSON | Universal, human-readable | Parquet/XML overkill for MVP |

---

## APPROVAL CHECKLIST

- [ ] Architect reviews PLANNING
- [ ] Iwan (Yang/Mandor) approves PLANNING
- [ ] Fix Context7 MCP connection → verify it works
- [ ] Convert PLANNING → TASK.md (execution checklist)
- [ ] Coder starts building frontend

---

**NEXT STEP:** Kirim PLANNING yang direvisi ke Yang untuk review. Setelah approve → fix Context7 → buat TASK.md → mulai build.
