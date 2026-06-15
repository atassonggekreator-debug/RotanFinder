"use client";

export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <html>
      <body className="min-h-screen flex items-center justify-center bg-gray-950 dark">
        <div className="max-w-md text-center space-y-4 p-8">
          <div className="text-6xl">💥</div>
          <h1 className="text-2xl font-bold text-white">Critical Error</h1>
          <p className="text-gray-400 text-sm">
            Something went very wrong. Please try reloading the app.
          </p>
          <button
            onClick={reset}
            className="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition-colors"
          >
            Reload
          </button>
        </div>
      </body>
    </html>
  );
}
