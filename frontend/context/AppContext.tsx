"use client";

import React, {
  createContext,
  useContext,
  useReducer,
  useEffect,
  useCallback,
  type ReactNode,
} from "react";
import { fetchShortlist, addToShortlist, removeFromShortlist } from "@/lib/api";
import type { AppState, AppAction } from "@/lib/types";

const STORAGE_KEY = "rotanfinder_state";

const initialState: AppState = {
  isLoading: false,
  results: [],
  error: null,
  shortlist: [],
  activeTab: "discovery",
  selectedCandidate: null,
};

function appReducer(state: AppState, action: AppAction): AppState {
  switch (action.type) {
    case "SET_LOADING":
      return { ...state, isLoading: action.payload, error: null };
    case "SET_RESULTS":
      return { ...state, results: action.payload, isLoading: false };
    case "SET_ERROR":
      return { ...state, error: action.payload, isLoading: false };
    case "TOGGLE_SHORTLIST": {
      const id = action.payload;
      const exists = state.shortlist.includes(id);
      const shortlist = exists
        ? state.shortlist.filter((sid) => sid !== id)
        : [...state.shortlist, id];
      return { ...state, shortlist };
    }
    case "HYDRATE_SHORTLIST":
      return { ...state, shortlist: action.payload };
    case "SET_ACTIVE_TAB":
      return { ...state, activeTab: action.payload };
    case "SELECT_CANDIDATE":
      return { ...state, selectedCandidate: action.payload };
    case "CLEAR_ERROR":
      return { ...state, error: null };
    default:
      return state;
  }
}

// Context value type
interface AppContextValue {
  state: AppState;
  dispatch: React.Dispatch<AppAction>;
  isShortlisted: (id: number) => boolean;
  toggleShortlist: (id: number) => void;
  clearError: () => void;
}

const AppContext = createContext<AppContextValue | undefined>(undefined);

export function AppProvider({ children }: { children: ReactNode }) {
  const [state, dispatch] = useReducer(appReducer, initialState, (init) => {
    // Hydrate full state from localStorage (shortlist + results)
    if (typeof window !== "undefined") {
      try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
          const parsed = JSON.parse(stored);
          return {
            ...init,
            shortlist: Array.isArray(parsed.shortlist) ? parsed.shortlist : [],
            results: Array.isArray(parsed.results) ? parsed.results : [],
          };
        }
      } catch {
        // ignore
      }
    }
    return init;
  });

  // Sync state to localStorage on change
  useEffect(() => {
    try {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          shortlist: state.shortlist,
          results: state.results,
        })
      );
    } catch {
      // ignore
    }
  }, [state.shortlist, state.results]);

  // Sync shortlist from DB on mount (authoritative source)
  // Keeps localStorage as fast cache, DB as source of truth
  useEffect(() => {
    fetchShortlist()
      .then((candidates) => {
        const ids = candidates.map((c) => c.id);
        dispatch({ type: "HYDRATE_SHORTLIST", payload: ids });
      })
      .catch(() => {
        // API unavailable — localStorage cache is fine
      });
  }, []);

  const isShortlisted = useCallback(
    (id: number) => state.shortlist.includes(id),
    [state.shortlist]
  );

  const toggleShortlist = useCallback(
    async (id: number) => {
      const isAdd = !state.shortlist.includes(id);
      try {
        if (isAdd) {
          await addToShortlist(id);
        } else {
          await removeFromShortlist(id);
        }
        dispatch({ type: "TOGGLE_SHORTLIST", payload: id });
      } catch {
        dispatch({ type: "SET_ERROR", payload: "Failed to sync shortlist with server" });
      }
    },
    [state.shortlist]
  );

  const clearError = useCallback(
    () => dispatch({ type: "CLEAR_ERROR" }),
    []
  );

  return (
    <AppContext.Provider
      value={{ state, dispatch, isShortlisted, toggleShortlist, clearError }}
    >
      {children}
    </AppContext.Provider>
  );
}

export function useAppContext(): AppContextValue {
  const ctx = useContext(AppContext);
  if (!ctx) {
    throw new Error("useAppContext must be used within an AppProvider");
  }
  return ctx;
}
