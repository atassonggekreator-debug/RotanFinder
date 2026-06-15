import axios, { AxiosError } from "axios";
import type {
  SearchQuery,
  Candidate,
  ShortlistAction,
  ExportFormat,
  UserPreferences,
} from "./types";
import { API_BASE_URL } from "./constants";

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 120000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Interceptors
api.interceptors.response.use(
  (res) => res,
  (error: AxiosError) => {
    if (error.response) {
      const status = error.response.status;
      if (status === 422) {
        const detail =
          (error.response.data as Record<string, unknown>)?.message ||
          "Validation failed";
        return Promise.reject(new Error(String(detail)));
      }
      if (status === 429) {
        return Promise.reject(new Error("Rate limited. Please wait."));
      }
      if (status >= 500) {
        return Promise.reject(new Error("Server error. Try again later."));
      }
    }
    if (error.code === "ECONNABORTED") {
      return Promise.reject(new Error("Request timed out."));
    }
    if (!error.response) {
      return Promise.reject(new Error("Connection failed."));
    }
    return Promise.reject(error);
  }
);

export async function submitDiscovery(
  query: SearchQuery
): Promise<{ search_id: string; candidates: Candidate[] }> {
  const { data } = await api.post("/discover", query);
  return data;
}

export async function getResults(
  searchId: string
): Promise<{ candidates: Candidate[] }> {
  const { data } = await api.get(`/discover/${searchId}`);
  return data;
}

export async function toggleShortlist(
  candidateId: number,
  action: ShortlistAction
): Promise<void> {
  await api.post("/shortlist", { candidate_id: candidateId, action });
}

export async function getShortlist(): Promise<Candidate[]> {
  const { data } = await api.get("/shortlist");
  return data.candidates ?? data;
}

export async function exportData(
  format: ExportFormat,
  ids: number[]
): Promise<Blob> {
  const { data } = await api.post("/export", { format, ids }, {
    responseType: "blob",
  });
  return data;
}

export async function getPreferences(): Promise<UserPreferences> {
  const { data } = await api.get("/preferences");
  return data;
}

export async function updatePreferences(
  prefs: Partial<UserPreferences>
): Promise<UserPreferences> {
  const { data } = await api.put("/preferences", prefs);
  return data;
}
