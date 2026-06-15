"use client";

import DiscoveryForm from "@/components/DiscoveryForm";
import ResultsList from "@/components/ResultsList";
import { useAppContext } from "@/context/AppContext";

export default function Home() {
  const { state } = useAppContext();

  return (
    <div className="flex-1 flex flex-col">
      {/* Hero / Form Section */}
      <section className="border-b border-gray-800 bg-gray-900/50">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-white">
              Discovery
            </h1>
            <p className="text-sm text-gray-400 mt-1">
              Find viral-worthy clip opportunities from long-form content
            </p>
          </div>
          <DiscoveryForm />
        </div>
      </section>

      {/* Results Section */}
      <section className="flex-1">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <ResultsList />
        </div>
      </section>
    </div>
  );
}
