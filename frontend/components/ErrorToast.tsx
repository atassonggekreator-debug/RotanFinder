"use client";

import { useEffect } from "react";
import { toast } from "sonner";
import { useAppContext } from "@/context/AppContext";

export default function ErrorToast() {
  const { state, dispatch } = useAppContext();

  useEffect(() => {
    if (state.error) {
      toast.error(state.error, {
        duration: 5000,
        onAutoClose: () => dispatch({ type: "CLEAR_ERROR" }),
        onDismiss: () => dispatch({ type: "CLEAR_ERROR" }),
      });
    }
  }, [state.error, dispatch]);

  return null;
}
