export type Platform = "youtube" | "twitch" | "facebook" | "tiktok";

export type Recommendation = "high" | "medium" | "low";

export type ShortlistAction = "add" | "remove";

export type ExportFormat = "csv" | "json";

export type SortKey = "score" | "views" | "engagement" | "freshness";

export type ConfidenceLevel = "high" | "medium" | "low";

export interface SearchQuery {
  niche: string;
  keywords: string[];
  platforms: Platform[];
  durationMin: number;
  durationMax: number;
  aiRerank: boolean;
  budget?: number;
  maxResults?: number;
}

export interface CandidateBreakdown {
  engagement: number;
  velocity: number;
  longform: number;
  momentum: number;
}

export interface Candidate {
  id: number;
  title: string;
  url: string;
  platform: Platform;
  creator: string;
  duration: number;
  views: number;
  likes: number;
  comments: number;
  score: number;
  confidence: ConfidenceLevel;
  breakdown: CandidateBreakdown;
  reason: string[];
  clipAngles: string[];
  viralPotential: number;
  monetizationPotential: number;
  riskNote?: string;
  isShortlisted: boolean;
  thumbnail?: string;
}

export interface UserPreferences {
  defaultPlatforms: Platform[];
  defaultDurationMin: number;
  defaultDurationMax: number;
  defaultBudget: number;
}

export interface AppState {
  isLoading: boolean;
  results: Candidate[];
  error: string | null;
  shortlist: number[];
  activeTab: "discovery" | "shortlist" | "settings";
  selectedCandidate: Candidate | null;
}

export type AppAction =
  | { type: "SET_LOADING"; payload: boolean }
  | { type: "SET_RESULTS"; payload: Candidate[] }
  | { type: "SET_ERROR"; payload: string | null }
  | { type: "TOGGLE_SHORTLIST"; payload: number }
  | { type: "SET_ACTIVE_TAB"; payload: AppState["activeTab"] }
  | { type: "SELECT_CANDIDATE"; payload: Candidate | null }
  | { type: "CLEAR_ERROR" }
  | { type: "HYDRATE_SHORTLIST"; payload: number[] };
