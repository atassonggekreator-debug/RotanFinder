"use client";

import { useEffect, useRef } from "react";
import { toast } from "sonner";
import { useAppContext } from "@/context/AppContext";

export default function ErrorToast() {
  const { state, clearError } = useAppContext();
  const prevError = useRef<string | null>(null);

  useEffect(() => {
    if (state.error && state.error !== prevError.current) {
      prevError.current = state.error;
      toast.error(state.error, {
        duration: 5000,
        onDismiss: () => clearError(),
        action: {
          label: "Dismiss",
          onClick: () => clearError(),
        },
      });
    }
  }, [state.error, clearError]);

  return null;
}
