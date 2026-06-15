"use client";

import { useAppContext } from "@/context/AppContext";

export default function SettingsPage() {
  const { state, dispatch } = useAppContext();

  return (
    <div className="flex flex-1 items-center justify-center p-8">
      <div className="max-w-md w-full space-y-6">
        <h1 className="text-2xl font-bold text-white">Settings</h1>
        <p className="text-gray-400">Preferences coming soon.</p>
      </div>
    </div>
  );
}
