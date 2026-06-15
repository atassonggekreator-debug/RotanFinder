import type { Platform, ConfidenceLevel } from "./types";

export const PLATFORMS: { value: Platform; label: string; color: string }[] = [
  { value: "youtube", label: "YouTube", color: "#FF0000" },
  { value: "twitch", label: "Twitch", color: "#9146FF" },
  { value: "facebook", label: "Facebook", color: "#1877F2" },
  { value: "tiktok", label: "TikTok", color: "#000000" },
];

export const DEFAULT_DURATION_MIN = 480; // 8 minutes
export const DEFAULT_DURATION_MAX = 7200; // 2 hours
export const DEFAULT_BUDGET_USD = 500;

export const SORT_OPTIONS: { value: string; label: string }[] = [
  { value: "score", label: "Score" },
  { value: "views", label: "Views" },
  { value: "engagement", label: "Engagement" },
  { value: "freshness", label: "Freshness" },
];

export const CONFIDENCE_COLORS: Record<ConfidenceLevel, string> = {
  high: "bg-green-500/10 text-green-400 border-green-500/20",
  medium: "bg-yellow-500/10 text-yellow-400 border-yellow-500/20",
  low: "bg-red-500/10 text-red-400 border-red-500/20",
};

export const PLATFORM_BADGES: Record<Platform, string> = {
  youtube: "bg-red-500/10 text-red-400",
  twitch: "bg-purple-500/10 text-purple-400",
  facebook: "bg-blue-500/10 text-blue-400",
  tiktok: "bg-white/10 text-white",
};

export const SCORE_THRESHOLDS = {
  HIGH: 7.0,
  MEDIUM: 4.0,
  LOW: 0,
} as const;

export const API_BASE_URL =
  process.env.NEXT_PUBLIC_LARAVEL_API_URL || "http://localhost:8000/api";

export const APP_NAME = "RotanFinder — Clip Discovery Engine";
