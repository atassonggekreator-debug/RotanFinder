"use client";

import { useState, type FormEvent } from "react";
import { useAppContext } from "@/context/AppContext";
import { submitDiscovery } from "@/lib/api";
import {
  PLATFORMS,
  DEFAULT_DURATION_MIN,
  DEFAULT_DURATION_MAX,
  DEFAULT_BUDGET_USD,
} from "@/lib/constants";
import type { Platform, SearchQuery } from "@/lib/types";
import { Loader2, Search, X } from "lucide-react";

export default function DiscoveryForm() {
  const { state, dispatch } = useAppContext();

  const [niche, setNiche] = useState("");
  const [keywordInput, setKeywordInput] = useState("");
  const [keywords, setKeywords] = useState<string[]>([]);
  const [platforms, setPlatforms] = useState<Platform[]>(["youtube"]);
  const [durationMin, setDurationMin] = useState(DEFAULT_DURATION_MIN);
  const [durationMax, setDurationMax] = useState(DEFAULT_DURATION_MAX);
  const [aiRerank, setAiRerank] = useState(true);
  const [budget, setBudget] = useState(DEFAULT_BUDGET_USD);
  const [maxResults, setMaxResults] = useState(5);
  const [errors, setErrors] = useState<Record<string, string>>({});

  function addKeyword() {
    const kw = keywordInput.trim();
    if (kw && !keywords.includes(kw)) {
      setKeywords([...keywords, kw]);
    }
    setKeywordInput("");
  }

  function removeKeyword(kw: string) {
    setKeywords(keywords.filter((k) => k !== kw));
  }

  function togglePlatform(p: Platform) {
    setPlatforms((prev) =>
      prev.includes(p) ? prev.filter((x) => x !== p) : [...prev, p]
    );
  }

  function validate(): boolean {
    const e: Record<string, string> = {};
    if (!niche.trim()) e.niche = "Niche is required";
    if (platforms.length === 0) e.platforms = "Select at least one platform";
    if (durationMin > durationMax)
      e.duration = "Min duration cannot exceed max";
    if (budget && budget <= 0) e.budget = "Budget must be positive";
    setErrors(e);
    return Object.keys(e).length === 0;
  }

  async function handleSubmit(ev: FormEvent) {
    ev.preventDefault();
    if (!validate()) return;

    const query: SearchQuery = {
      niche: niche.trim(),
      keywords,
      platforms,
      durationMin,
      durationMax,
      aiRerank,
      budget: budget || undefined,
      maxResults,
    };

    dispatch({ type: "SET_LOADING", payload: true });

    try {
      const result = await submitDiscovery(query);
      dispatch({ type: "SET_RESULTS", payload: result.candidates ?? [] });
    } catch (err) {
      dispatch({
        type: "SET_ERROR",
        payload: err instanceof Error ? err.message : "Discovery failed",
      });
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      {/* Niche */}
      <div>
        <label className="block text-sm font-medium text-gray-300 mb-1.5">
          Niche <span className="text-red-400">*</span>
        </label>
        <input
          type="text"
          value={niche}
          onChange={(e) => setNiche(e.target.value)}
          placeholder="e.g., tech reviews, fitness, gaming..."
          className="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        />
        {errors.niche && (
          <p className="mt-1 text-xs text-red-400">{errors.niche}</p>
        )}
      </div>

      {/* Keywords */}
      <div>
        <label className="block text-sm font-medium text-gray-300 mb-1.5">
          Keywords
        </label>
        <div className="flex gap-2">
          <input
            type="text"
            value={keywordInput}
            onChange={(e) => setKeywordInput(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && (e.preventDefault(), addKeyword())}
            placeholder="Type and press Enter..."
            className="flex-1 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
          <button
            type="button"
            onClick={addKeyword}
            className="px-3 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm text-gray-300 transition-colors"
          >
            Add
          </button>
        </div>
        {keywords.length > 0 && (
          <div className="flex flex-wrap gap-1.5 mt-2">
            {keywords.map((kw) => (
              <span
                key={kw}
                className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
              >
                {kw}
                <button type="button" onClick={() => removeKeyword(kw)}>
                  <X className="w-3 h-3" />
                </button>
              </span>
            ))}
          </div>
        )}
      </div>

      {/* Platforms */}
      <div>
        <label className="block text-sm font-medium text-gray-300 mb-1.5">
          Platforms <span className="text-red-400">*</span>
        </label>
        <div className="flex flex-wrap gap-2">
          {PLATFORMS.map((p) => (
            <button
              key={p.value}
              type="button"
              onClick={() => togglePlatform(p.value)}
              className={`px-3 py-2 rounded-lg text-xs font-medium border transition-colors ${
                platforms.includes(p.value)
                  ? "bg-indigo-500/10 text-indigo-400 border-indigo-500/20"
                  : "bg-gray-800 text-gray-500 border-gray-700 hover:border-gray-600"
              }`}
            >
              {p.label}
            </button>
          ))}
        </div>
        {errors.platforms && (
          <p className="mt-1 text-xs text-red-400">{errors.platforms}</p>
        )}
      </div>

      {/* Duration */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-300 mb-1.5">
            Min Duration (s)
          </label>
          <input
            type="number"
            value={durationMin}
            onChange={(e) => setDurationMin(Number(e.target.value))}
            min={60}
            className="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-300 mb-1.5">
            Max Duration (s)
          </label>
          <input
            type="number"
            value={durationMax}
            onChange={(e) => setDurationMax(Number(e.target.value))}
            min={60}
            className="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>
      {errors.duration && (
        <p className="text-xs text-red-400">{errors.duration}</p>
      )}

      {/* Max Results */}
      <div>
        <label className="block text-sm font-medium text-gray-300 mb-1.5">
          Max Results
        </label>
        <div className="flex items-center gap-3">
          <input
            type="range"
            min={1}
            max={20}
            value={maxResults}
            onChange={(e) => setMaxResults(Number(e.target.value))}
            className="flex-1 accent-indigo-500 h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer"
          />
          <span className="text-sm text-gray-300 font-mono min-w-[2rem] text-right">
            {maxResults}
          </span>
        </div>
        <p className="mt-1 text-xs text-gray-500">
          Number of YouTube results to scrape
        </p>
      </div>

      {/* AI Rerank toggle */}
      <div className="flex items-center justify-between">
        <label className="text-sm font-medium text-gray-300">AI Re-ranking</label>
        <button
          type="button"
          onClick={() => setAiRerank(!aiRerank)}
          className={`relative w-11 h-6 rounded-full transition-colors ${
            aiRerank ? "bg-indigo-600" : "bg-gray-700"
          }`}
        >
          <span
            className={`absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform ${
              aiRerank ? "translate-x-5" : ""
            }`}
          />
        </button>
      </div>

      {/* Budget */}
      <div>
        <label className="block text-sm font-medium text-gray-300 mb-1.5">
          Budget (USD, optional)
        </label>
        <input
          type="number"
          value={budget}
          onChange={(e) => setBudget(Number(e.target.value))}
          min={0}
          className="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        {errors.budget && (
          <p className="mt-1 text-xs text-red-400">{errors.budget}</p>
        )}
      </div>

      {/* Submit */}
      <button
        type="submit"
        disabled={state.isLoading}
        className="w-full py-3 bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-700 disabled:text-gray-500 rounded-lg text-sm font-semibold text-white transition-colors flex items-center justify-center gap-2"
      >
        {state.isLoading ? (
          <>
            <Loader2 className="w-4 h-4 animate-spin" />
            Discovering...
          </>
        ) : (
          <>
            <Search className="w-4 h-4" />
            Discover Clip Potential
          </>
        )}
      </button>
    </form>
  );
}
