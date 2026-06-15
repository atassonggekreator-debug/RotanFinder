"use client";

import { cn } from "@/lib/utils";
import type { Candidate } from "@/lib/types";
import { PLATFORM_BADGES, CONFIDENCE_COLORS } from "@/lib/constants";
import { Heart, ExternalLink } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface CandidateCardProps {
  candidate: Candidate;
  onShortlist: () => void;
  onDetail: () => void;
}

function formatCount(n: number): string {
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`;
  if (n >= 1_000) return `${(n / 1_000).toFixed(1)}K`;
  return String(n);
}

function truncate(str: string, len: number): string {
  if (str.length <= len) return str;
  return str.slice(0, len) + "…";
}

export default function CandidateCard({
  candidate,
  onShortlist,
  onDetail,
}: CandidateCardProps) {
  const {
    title,
    thumbnail,
    platform,
    views,
    likes,
    score,
    confidence,
    isShortlisted,
  } = candidate;

  const scorePct = Math.min(score / 10, 1);

  return (
    <div
      onClick={onDetail}
      className={cn(
        "group relative rounded-xl border border-gray-800 bg-gray-900/60 overflow-hidden",
        "cursor-pointer transition-all duration-200",
        "hover:border-gray-700 hover:bg-gray-900 hover:shadow-lg hover:shadow-indigo-500/5",
        "hover:-translate-y-0.5"
      )}
    >
      {/* Thumbnail */}
      <div className="relative aspect-video bg-gray-800 overflow-hidden">
        {thumbnail ? (
          <img
            src={thumbnail}
            alt={title}
            className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-gray-600 text-sm">
            No thumbnail
          </div>
        )}
        {/* Platform badge */}
        <span
          className={cn(
            "absolute top-2 left-2 px-2.5 py-0.5 rounded-md text-[11px] font-semibold uppercase tracking-wider",
            PLATFORM_BADGES[platform]
          )}
        >
          {platform}
        </span>
        {/* Score badge */}
        <span className="absolute top-2 right-2 px-2 py-0.5 rounded-md text-xs font-bold bg-gray-900/80 text-gray-200 backdrop-blur-sm">
          {score.toFixed(1)}
        </span>
      </div>

      {/* Body */}
      <div className="p-3 space-y-3">
        {/* Title with tooltip */}
        <div className="group/title relative">
          <p className="text-sm font-medium text-gray-200 leading-snug line-clamp-2">
            {truncate(title, 60)}
          </p>
          {title.length > 60 && (
            <div className="absolute bottom-full left-0 mb-1 hidden group-hover/title:block z-10">
              <div className="bg-gray-800 text-gray-200 text-xs rounded-lg px-3 py-2 shadow-xl border border-gray-700 max-w-sm whitespace-normal break-words">
                {title}
              </div>
            </div>
          )}
        </div>

        {/* Views / Likes compact */}
        <div className="flex items-center gap-3 text-xs text-gray-500">
          <span>
            {formatCount(views)} views
          </span>
          <span className="flex items-center gap-1">
            <Heart className="w-3 h-3" />
            {formatCount(likes)}
          </span>
        </div>

        {/* Score bar */}
        <div className="space-y-1">
          <div className="flex items-center justify-between text-[11px]">
            <span className="text-gray-500">Score</span>
            <span className="text-gray-300 font-medium">
              {score.toFixed(1)} / 10
            </span>
          </div>
          <div className="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden">
            <div
              className="h-full rounded-full transition-all duration-500"
              style={{
                width: `${scorePct * 100}%`,
                background:
                  score >= 7
                    ? "linear-gradient(90deg, #059669, #10b981)"
                    : score >= 4
                    ? "linear-gradient(90deg, #d97706, #f59e0b)"
                    : "linear-gradient(90deg, #dc2626, #ef4444)",
              }}
            />
          </div>
        </div>

        {/* Confidence badge */}
        <Badge
          className={cn(
            "text-[11px] font-medium px-2.5 py-0.5 rounded-full border",
            CONFIDENCE_COLORS[confidence]
          )}
        >
          {confidence}
        </Badge>
      </div>

      {/* Footer actions */}
      <div className="px-3 pb-3 flex items-center gap-2">
        <Button
          variant="ghost"
          size="sm"
          onClick={(e) => {
            e.stopPropagation();
            onShortlist();
          }}
          className={cn(
            "flex-1 h-8 text-xs gap-1.5",
            isShortlisted
              ? "text-red-400 hover:text-red-300 hover:bg-red-500/10"
              : "text-gray-500 hover:text-gray-300 hover:bg-gray-800"
          )}
        >
          <Heart
            className={cn("w-3.5 h-3.5", isShortlisted && "fill-red-400")}
          />
          {isShortlisted ? "Saved" : "Shortlist"}
        </Button>
        <Button
          variant="ghost"
          size="sm"
          onClick={(e) => {
            e.stopPropagation();
            onDetail();
          }}
          className="flex-1 h-8 text-xs gap-1.5 text-gray-500 hover:text-gray-300 hover:bg-gray-800"
        >
          <ExternalLink className="w-3.5 h-3.5" />
          Details
        </Button>
      </div>
    </div>
  );
}
