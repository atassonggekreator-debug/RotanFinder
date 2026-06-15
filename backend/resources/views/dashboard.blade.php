<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rotan Finder Dashboard</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased">
    <!-- Navbar -->
    <nav class="border-b border-gray-800 bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🔄</span>
                    <span class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Rotan Finder</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-gray-400">Queue Worker</span>
                    </span>
                    <span class="text-gray-600">|</span>
                    <span class="text-gray-400">{{ $totalVideos }} videos</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <!-- Total Videos -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Videos</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $totalVideos }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-500/10 rounded-lg flex items-center justify-center text-2xl">🎬</div>
                </div>
            </div>
            <!-- HIGH Potential -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">HIGH Potential</p>
                        <p class="text-3xl font-bold text-emerald-400 mt-1">{{ $highCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-lg flex items-center justify-center text-2xl">🔥</div>
                </div>
            </div>
            <!-- MEDIUM Potential -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">MEDIUM Potential</p>
                        <p class="text-3xl font-bold text-amber-400 mt-1">{{ $mediumCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex items-center justify-center text-2xl">⭐</div>
                </div>
            </div>
        </div>

        <!-- Search & Table Section -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden"
             x-data="dashboard()"
             x-init="init()">
            <!-- Search Bar -->
            <div class="p-4 border-b border-gray-800">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           x-model="search" 
                           placeholder="Search by title..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <!-- Debug search value -->
                <p class="mt-2 text-xs text-gray-600" x-text="'🔍 Searching: ' + (search || '(empty)')"></p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" @click="sort('title')">Title <span x-show="sortField === 'title'" x-text="sortDir === 'asc' ? '▲' : '▼'" class="text-indigo-400"></span></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" @click="sort('duration')">Duration <span x-show="sortField === 'duration'" x-text="sortDir === 'asc' ? '▲' : '▼'" class="text-indigo-400"></span></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" @click="sort('view_count')">Views <span x-show="sortField === 'view_count'" x-text="sortDir === 'asc' ? '▲' : '▼'" class="text-indigo-400"></span></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" @click="sort('score')">Score <span x-show="sortField === 'score'" x-text="sortDir === 'asc' ? '▲' : '▼'" class="text-indigo-400"></span></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rec</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="v in filteredVideos" :key="v.id">
                            <tr class="border-b border-gray-800/50 hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-gray-500" x-text="v.id"></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <img x-show="v.thumbnail" :src="v.thumbnail" alt="" class="w-10 h-7 rounded object-cover flex-shrink-0" loading="lazy">
                                        <span class="text-gray-200 truncate max-w-xs" :title="v.title" x-text="v.title.length > 45 ? v.title.substring(0, 45) + '...' : v.title"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 bg-gray-800 rounded text-xs text-gray-400" x-text="v.extractor"></span>
                                </td>
                                <td class="px-4 py-3 text-gray-400" x-text="v.duration_formatted"></td>
                                <td class="px-4 py-3 text-gray-400" x-text="v.views_formatted"></td>
                                <td class="px-4 py-3">
                                    <span class="font-mono font-medium" x-show="v.score !== null" x-text="v.score.toFixed(1)"></span>
                                    <span class="text-gray-600" x-show="v.score === null">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                                          :class="{
                                              'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': v.rec === 'HIGH',
                                              'bg-amber-500/10 text-amber-400 border-amber-500/20': v.rec === 'MEDIUM',
                                              'bg-gray-800 text-gray-500 border-gray-700': v.rec === 'LOW' || v.rec === 'N/A'
                                          }"
                                          x-text="v.rec"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <a :href="v.url" target="_blank" rel="noopener noreferrer" 
                                       class="inline-flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        Watch
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredVideos.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                <div class="text-4xl mb-3">🎥</div>
                                <p class="text-lg font-medium" x-text="search ? 'No videos match "' + search + '"' : 'No videos yet'"></p>
                                <p class="text-sm mt-1" x-text="search ? 'Try a different search term' : 'Start scraping to see content here'"></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function dashboard() {
            return {
                search: '',
                sortField: '{{ $sortField }}',
                sortDir: '{{ $sortDir }}',
                videos: @json($videosData),

                init() {
                    // Apply dark mode
                    document.documentElement.classList.add('dark');
                },

                get filteredVideos() {
                    let list = this.videos;
                    // Client-side search
                    if (this.search) {
                        const q = this.search.toLowerCase();
                        list = list.filter(v => v.title.toLowerCase().includes(q));
                    }
                    // Client-side sort
                    list.sort((a, b) => {
                        let valA, valB;
                        if (this.sortField === 'title') {
                            valA = a.title.toLowerCase();
                            valB = b.title.toLowerCase();
                            return this.sortDir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                        } else if (this.sortField === 'duration') {
                            valA = a.duration || 0;
                            valB = b.duration || 0;
                        } else if (this.sortField === 'view_count') {
                            valA = a.view_count || 0;
                            valB = b.view_count || 0;
                        } else {
                            valA = a.score !== null ? a.score : -1;
                            valB = b.score !== null ? b.score : -1;
                        }
                        return this.sortDir === 'asc' ? valA - valB : valB - valA;
                    });
                    return list;
                },

                sort(field) {
                    if (this.sortField === field) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortField = field;
                        this.sortDir = 'desc';
                    }
                }
            };
        }
    </script>
</body>
</html>
