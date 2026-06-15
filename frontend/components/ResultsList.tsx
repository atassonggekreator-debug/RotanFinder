"use client";

import { useState, useMemo, useCallback } from "react";
import { useAppContext } from "@/context/AppContext";
import { cn } from "@/lib/utils";
import type { Platform, SortKey } from "@/lib/types";
import { SORT_OPTIONS, PLATFORMS } from "@/lib/constants";
import { Search, RotateCw, ChevronDown, Filter } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import CandidateCard from "@/components/CandidateCard";
import CandidateDetail from "@/components/CandidateDetail";
import { Skeleton } from "@/components/ui/skeleton";

export default function ResultsList() {
  const { state, dispatch, isShortlisted, toggleShortlist } = useAppContext();
  const { isLoading, results, error, selectedCandidate } = state;

  const [sortKey, setSortKey] = useState<SortKey>("score");
  const [platformFilter, setPlatformFilter] = useState<Platform | "all">("all");
  const [detailOpen, setDetailOpen] = useState(false);

  const openDetail = useCallback(
    (c: (typeof results)[number]) => {
      dispatch({ type: "SELECT_CANDIDATE", payload: c });
      setDetailOpen(true);
    },
    [dispatch]
  );

  const closeDetail = useCallback(
    (open: boolean) => {
      setDetailOpen(open);
      if (!open) {
        dispatch({ type: "SELECT_CANDIDATE", payload: null });
      }
    },
    [dispatch]
  );

  // Filter and sort
  const displayed = useMemo(() => {
    let list = [...results];

    // Platform filter
    if (platformFilter !== "all") {
      list = list.filter((c) => c.platform === platformFilter);
    }

    // Sort
    switch (sortKey) {
      case "score":
        list.sort((a, b) => b.score - a.score);
        break;
      case "views":
        list.sort((a, b) => b.views - a.views);
        break;
      case "engagement": {
        // engagement rate ≈ (likes + comments) / views
        const rate = (c: (typeof results)[number]) =>
          c.views > 0 ? (c.likes + c.comments) / c.views : 0;
        list.sort((a, b) => rate(b) - rate(a));
        break;
      }
      case "freshness":
        // No date field on Candidate; fallback to id descending
        list.sort((a, b) => b.id - a.id);
        break;
    }

    return list;
  }, [results, sortKey, platformFilter]);

  // Loading skeleton grid
  if (isLoading) {
    return (
      <div className="space-y-4">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <div
              key={i}
              className="rounded-xl border border-gray-800 bg-gray-900/60 overflow-hidden"
            >
              <Skeleton className="aspect-video w-full rounded-none bg-gray-800" />
              <div className="p-3 space-y-3">
                <Skeleton className="h-4 w-3/4 bg-gray-800" />
                <Skeleton className="h-3 w-1/2 bg-gray-800" />
                <Skeleton className="h-1.5 w-full bg-gray-800 rounded-full" />
                <Skeleton className="h-4 w-16 rounded-full bg-gray-800" />
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <div className="w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mb-4">
          <RotateCw className="w-6 h-6 text-red-400" />
        </div>
        <p className="text-sm text-red-400 font-medium mb-1">
          Something went wrong
        </p>
        <p className="text-xs text-gray-500 mb-6 max-w-xs">{error}</p>
        <Button
          variant="outline"
          size="sm"
          onClick={() => dispatch({ type: "CLEAR_ERROR" })}
          className="border-gray-700 text-gray-300 hover:bg-gray-800"
        >
          <RotateCw className="w-3.5 h-3.5 mr-1.5" />
          Retry
        </Button>
      </div>
    );
  }

  // Empty state
  if (results.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-20 text-center">
        <div className="w-14 h-14 rounded-full bg-gray-800 flex items-center justify-center mb-4">
          <Search className="w-6 h-6 text-gray-600" />
        </div>
        <p className="text-sm text-gray-400 font-medium mb-1">No results yet</p>
        <p className="text-xs text-gray-600 max-w-xs">
          Fill in the discovery form and hit &ldquo;Discover Clip Potential&rdquo; to find
          viral clip candidates.
        </p>
      </div>
    );
  }

  // Results
  return (
    <div className="space-y-4">
      {/* Toolbar: sort + platform filter */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <Filter className="w-4 h-4 text-gray-500" />
          <div className="flex flex-wrap gap-1.5">
            <button
              onClick={() => setPlatformFilter("all")}
              className={cn(
                "px-2.5 py-1 rounded-lg text-xs font-medium transition-colors",
                platformFilter === "all"
                  ? "bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
                  : "bg-gray-800 text-gray-500 border border-gray-700 hover:border-gray-600"
              )}
            >
              All
            </button>
            {PLATFORMS.map((p) => (
              <button
                key={p.value}
                onClick={() => setPlatformFilter(p.value)}
                className={cn(
                  "px-2.5 py-1 rounded-lg text-xs font-medium transition-colors",
                  platformFilter === p.value
                    ? "bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
                    : "bg-gray-800 text-gray-500 border border-gray-700 hover:border-gray-600"
                )}
              >
                {p.label}
              </button>
            ))}
          </div>
        </div>

        <div className="flex items-center gap-2">
          <span className="text-xs text-gray-500">Sort by</span>
          <Select
            value={sortKey}
            onValueChange={(v) => setSortKey(v as SortKey)}
          >
            <SelectTrigger className="w-36 h-8 bg-gray-800 border-gray-700 text-xs text-gray-300">
              <SelectValue />
            </SelectTrigger>
            <SelectContent className="bg-gray-900 border-gray-700 text-gray-300">
              {SORT_OPTIONS.map((opt) => (
                <SelectItem
                  key={opt.value}
                  value={opt.value}
                  className="text-xs focus:bg-gray-800 focus:text-gray-200"
                >
                  {opt.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      {/* Result count */}
      <p className="text-xs text-gray-600">
        Showing {displayed.length} of {results.length} results
      </p>

      {/* Grid */}
      {displayed.length === 0 ? (
        <div className="flex flex-col items-center justify-center py-16 text-center">
          <Filter className="w-8 h-8 text-gray-600 mb-2" />
          <p className="text-sm text-gray-500">
            No results match the selected platform filter.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {displayed.map((candidate) => (
            <CandidateCard
              key={candidate.id}
              candidate={{
                ...candidate,
                isShortlisted: isShortlisted(candidate.id),
              }}
              onShortlist={() => toggleShortlist(candidate.id)}
              onDetail={() => openDetail(candidate)}
            />
          ))}
        </div>
      )}

      {/* Detail dialog */}
      <CandidateDetail
        candidate={selectedCandidate}
        open={detailOpen}
        onOpenChange={closeDetail}
      />
    </div>
  );
}
