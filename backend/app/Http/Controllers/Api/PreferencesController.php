<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    private function defaults(): array
    {
        return [
            'min_score'       => 4.0,
            'max_duration'    => 600,
            'platforms'       => ['youtube', 'twitch', 'facebook', 'tiktok'],
            'max_results'     => 50,
            'sort_by'         => 'score',
            'sort_direction'  => 'desc',
        ];
    }

    public function index(): JsonResponse
    {
        return response()->json($this->defaults());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'min_score'      => 'nullable|numeric|min:0|max:10',
            'max_duration'   => 'nullable|integer|min:0',
            'platforms'      => 'nullable|array',
            'platforms.*'    => 'string|in:youtube,twitch,facebook,tiktok',
            'max_results'    => 'nullable|integer|min:1|max:200',
            'sort_by'        => 'nullable|string|in:score,duration,view_count,upload_date',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        $prefs = array_merge($this->defaults(), $validated);

        return response()->json($prefs);
    }
}
