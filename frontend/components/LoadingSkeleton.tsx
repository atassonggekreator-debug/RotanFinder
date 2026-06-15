"use client";

import { Skeleton } from "@/components/ui/skeleton";

interface LoadingSkeletonProps {
  count?: number;
}

export default function LoadingSkeleton({ count = 6 }: LoadingSkeletonProps) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      {Array.from({ length: count }).map((_, i) => (
        <div
          key={i}
          className="rounded-xl border border-gray-800 bg-gray-900/60 overflow-hidden"
        >
          {/* Thumbnail placeholder */}
          <Skeleton className="aspect-video w-full rounded-none bg-gray-800" />

          {/* Body */}
          <div className="p-3 space-y-3">
            {/* Title line */}
            <Skeleton className="h-4 w-3/4 bg-gray-800" />
            <Skeleton className="h-4 w-1/2 bg-gray-800" />

            {/* Views / Likes line */}
            <div className="flex gap-3">
              <Skeleton className="h-3 w-16 bg-gray-800" />
              <Skeleton className="h-3 w-12 bg-gray-800" />
            </div>

            {/* Score bar placeholder */}
            <div className="space-y-1">
              <div className="flex justify-between">
                <Skeleton className="h-3 w-10 bg-gray-800" />
                <Skeleton className="h-3 w-14 bg-gray-800" />
              </div>
              <Skeleton className="h-1.5 w-full rounded-full bg-gray-800" />
            </div>

            {/* Confidence badge */}
            <Skeleton className="h-5 w-20 rounded-full bg-gray-800" />
          </div>

          {/* Footer actions */}
          <div className="px-3 pb-3 flex gap-2">
            <Skeleton className="h-8 flex-1 rounded-md bg-gray-800" />
            <Skeleton className="h-8 flex-1 rounded-md bg-gray-800" />
          </div>
        </div>
      ))}
    </div>
  );
}
