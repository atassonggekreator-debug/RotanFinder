"use client";

import Link from "next/link";
import { useAppContext } from "@/context/AppContext";
import { APP_NAME } from "@/lib/constants";
import { useState, useEffect } from "react";

export default function Navbar() {
  const { state, dispatch } = useAppContext();
  const [isClient, setIsClient] = useState(false);

  useEffect(() => { setIsClient(true); }, []);

  const tabs = [
    { key: "discovery" as const, label: "Discovery", href: "/" },
    { key: "shortlist" as const, label: "Shortlist", href: "/shortlist" },
    { key: "settings" as const, label: "Settings", href: "/settings" },
  ];

  return (
    <nav className="border-b border-gray-800 bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2">
            <span className="text-2xl">🔄</span>
            <span className="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
              RotanFinder
            </span>
          </Link>

          {/* Navigation */}
          <div className="flex items-center gap-1">
            {tabs.map((tab) => (
              <Link
                key={tab.key}
                href={tab.href}
                onClick={() =>
                  dispatch({ type: "SET_ACTIVE_TAB", payload: tab.key })
                }
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                  state.activeTab === tab.key
                    ? "bg-gray-800 text-white"
                    : "text-gray-400 hover:text-gray-200 hover:bg-gray-800/50"
                }`}
              >
                {tab.label}
                {isClient && tab.key === "shortlist" && state.shortlist.length > 0 && (
                  <span className="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-indigo-500 text-white">
                    {state.shortlist.length}
                  </span>
                )}
              </Link>
            ))}
          </div>
        </div>
      </div>
    </nav>
  );
}
