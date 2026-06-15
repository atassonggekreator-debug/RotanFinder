"use client";

import { useMemo, useState, useEffect } from "react";
import { useAppContext } from "@/context/AppContext";
import CandidateCard from "@/components/CandidateCard";
import ExportButton from "@/components/ExportButton";
import { Trash2, Heart } from "lucide-react";

export default function ShortlistPage() {
  const [ready, setReady] = useState(false);
  useEffect(() => { setReady(true); }, []);

  const { state, dispatch, isShortlisted, toggleShortlist } = useAppContext();

  const shortlistedCandidates = useMemo(
    () => state.results.filter((c) => isShortlisted(c.id)),
    [state.results, state.shortlist]
  );

  const shortlistIds = useMemo(
    () => shortlistedCandidates.map((c) => c.id),
    [shortlistedCandidates]
  );

  function openDetail(candidate: (typeof state.results)[number]) {
    dispatch({ type: "SELECT_CANDIDATE", payload: candidate });
  }

  if (!ready) {
    return (
      <div className="flex flex-1 flex-col p-8 max-w-6xl mx-auto w-full">
        <h1 className="text-2xl font-bold text-white flex items-center gap-2">
          <Heart className="w-6 h-6 text-red-400" />
          Shortlist
        </h1>
        <div className="flex flex-1 items-center justify-center text-gray-500">
          <p className="text-sm text-gray-400">Loading shortlist...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-1 flex-col p-8 max-w-6xl mx-auto w-full">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold text-white flex items-center gap-2">
            <Heart className="w-6 h-6 text-red-400" />
            Shortlist
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            {state.shortlist.length} candidate(s) shortlisted
          </p>
        </div>
        {shortlistIds.length > 0 && (
          <div className="flex items-center gap-2">
            <ExportButton format="csv" ids={shortlistIds} />
            <ExportButton format="json" ids={shortlistIds} />
            <button
              onClick={() =>
                shortlistIds.forEach((id) =>
                  dispatch({ type: "TOGGLE_SHORTLIST", payload: id })
                )
              }
              className="px-3 py-2 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg border border-red-500/20 transition-colors flex items-center gap-1.5"
            >
              <Trash2 className="w-3.5 h-3.5" />
              Clear all
            </button>
          </div>
        )}
      </div>

      {state.shortlist.length === 0 ? (
        <div className="flex flex-1 items-center justify-center text-gray-500">
          <div className="text-center">
            <div className="w-14 h-14 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-4">
              <Heart className="w-6 h-6 text-gray-600" />
            </div>
            <p className="text-lg font-medium">No shortlisted candidates yet</p>
            <p className="text-sm mt-1 text-gray-600">
              Explore Discovery to find clip-worthy content
            </p>
          </div>
        </div>
      ) : shortlistedCandidates.length === 0 ? (
        <div className="flex flex-1 items-center justify-center text-gray-500">
          <div className="text-center">
            <p className="text-sm text-gray-400">
              Results have been cleared. Shortlist IDs are still saved.
            </p>
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {shortlistedCandidates.map((candidate) => (
            <CandidateCard
              key={candidate.id}
              candidate={{
                ...candidate,
                isShortlisted: true,
              }}
              onShortlist={() => toggleShortlist(candidate.id)}
              onDetail={() => openDetail(candidate)}
            />
          ))}
        </div>
      )}
    </div>
  );
}
