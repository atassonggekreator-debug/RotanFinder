"use client";

import { cn } from "@/lib/utils";
import type { Candidate } from "@/lib/types";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { ExternalLink, AlertTriangle } from "lucide-react";

interface CandidateDetailProps {
  candidate: Candidate | null;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

function breakdownLabel(key: string): string {
  return key.charAt(0).toUpperCase() + key.slice(1);
}

function roundScore(n: number): number {
  return Math.round(n * 10) / 10;
}

export default function CandidateDetail({
  candidate,
  open,
  onOpenChange,
}: CandidateDetailProps) {
  if (!candidate) return null;

  const {
    title,
    url,
    platform,
    creator,
    duration,
    views,
    likes,
    comments,
    score,
    breakdown,
    reason,
    clipAngles,
    viralPotential,
    monetizationPotential,
    riskNote,
    isShortlisted,
  } = candidate;

  const breakdownEntries = (breakdown && typeof breakdown === 'object')
    ? Object.entries(breakdown) as [keyof typeof breakdown, number][]
    : [];

  const formatDuration = (sec: number) => {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s.toString().padStart(2, "0")}`;
  };

  const formatCount = (n: number) => {
    if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`;
    if (n >= 1_000) return `${(n / 1_000).toFixed(1)}K`;
    return String(n);
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-lg max-h-[85vh] overflow-y-auto bg-gray-900 border-gray-800 text-gray-200">
        <DialogHeader>
          <DialogTitle className="text-lg font-semibold text-gray-100 pr-6 leading-snug">
            {title}
          </DialogTitle>
        </DialogHeader>

        <div className="space-y-5">
          {/* Meta row */}
          <div className="flex flex-wrap items-center gap-3 text-sm text-gray-400">
            <span className="text-xs font-semibold uppercase tracking-wider text-gray-500">
              {platform}
            </span>
            <span className="text-gray-600">|</span>
            <span>{creator}</span>
            <span className="text-gray-600">|</span>
            <span>{formatDuration(duration)}</span>
            <span className="text-gray-600">|</span>
            <span>{formatCount(views)} views</span>
          </div>

          {/* URL link */}
          <a
            href={url}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 underline underline-offset-2 transition-colors"
          >
            <ExternalLink className="w-3.5 h-3.5" />
            Open original video
          </a>

          {/* Score breakdown */}
          <div className="space-y-3">
            <h4 className="text-sm font-semibold text-gray-300">
              Score Breakdown
            </h4>

            {/* Overall score */}
            <div className="flex items-center gap-3 mb-4">
              <span
                className={cn(
                  "text-2xl font-bold",
                  score >= 7
                    ? "text-emerald-400"
                    : score >= 4
                    ? "text-amber-400"
                    : "text-red-400"
                )}
              >
                {score.toFixed(1)}
              </span>
              <span className="text-xs text-gray-500">/ 10 overall</span>
            </div>

            {breakdownEntries.map(([key, val]) => {
              const pct = Math.min(Math.max(val, 0), 1);
              return (
                <div key={key} className="space-y-1">
                  <div className="flex items-center justify-between text-xs">
                    <span className="text-gray-400 capitalize">
                      {breakdownLabel(key)}
                    </span>
                    <span className="text-gray-500">
                      {roundScore(pct * 100)}%
                    </span>
                  </div>
                  <div className="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div
                      className="h-full rounded-full transition-all duration-500"
                      style={{
                        width: `${pct * 100}%`,
                        background:
                          pct >= 0.7
                            ? "linear-gradient(90deg, #059669, #10b981)"
                            : pct >= 0.4
                            ? "linear-gradient(90deg, #d97706, #f59e0b)"
                            : "linear-gradient(90deg, #dc2626, #ef4444)",
                      }}
                    />
                  </div>
                </div>
              );
            })}
          </div>

          {/* Separator */}
          <div className="border-t border-gray-800" />

          {/* Reason bullets */}
          <div className="space-y-2">
            <h4 className="text-sm font-semibold text-gray-300">
              Why this clip?
            </h4>
            <ul className="space-y-1.5">
              {(reason ?? []).map((r, i) => (
                <li
                  key={i}
                  className="text-sm text-gray-400 flex items-start gap-2"
                >
                  <span className="mt-1 block w-1.5 h-1.5 rounded-full bg-indigo-500/60 shrink-0" />
                  {r}
                </li>
              ))}
            </ul>
          </div>

          {/* Clip angles */}
          {(clipAngles ?? []).length > 0 && (
            <div className="space-y-2">
              <h4 className="text-sm font-semibold text-gray-300">
                Clip Angles
              </h4>
              <div className="flex flex-wrap gap-2">
                {clipAngles.map((angle, i) => (
                  <Badge
                    key={i}
                    variant="outline"
                    className="text-xs text-indigo-300 border-indigo-500/20 bg-indigo-500/5"
                  >
                    {angle}
                  </Badge>
                ))}
              </div>
            </div>
          )}

          {/* Viral / Monetization badges */}
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-2">
              <span className="text-xs text-gray-500">Viral</span>
              <span
                className={cn(
                  "inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold",
                  (viralPotential ?? 0) >= 7
                    ? "bg-green-500/10 text-green-400 border border-green-500/20"
                    : (viralPotential ?? 0) >= 4
                    ? "bg-yellow-500/10 text-yellow-400 border border-yellow-500/20"
                    : "bg-red-500/10 text-red-400 border border-red-500/20"
                )}
              >
                {viralPotential ?? 0}
              </span>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-xs text-gray-500">Monetization</span>
              <span
                className={cn(
                  "inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold",
                  (monetizationPotential ?? 0) >= 7
                    ? "bg-green-500/10 text-green-400 border border-green-500/20"
                    : (monetizationPotential ?? 0) >= 4
                    ? "bg-yellow-500/10 text-yellow-400 border border-yellow-500/20"
                    : "bg-red-500/10 text-red-400 border border-red-500/20"
                )}
              >
                {monetizationPotential ?? 0}
              </span>
            </div>
          </div>

          {/* Risk note */}
          {riskNote && (
            <div className="flex items-start gap-2.5 p-3 rounded-lg bg-amber-500/5 border border-amber-500/15">
              <AlertTriangle className="w-4 h-4 text-amber-400 mt-0.5 shrink-0" />
              <p className="text-sm text-amber-300/90">{riskNote}</p>
            </div>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
}
