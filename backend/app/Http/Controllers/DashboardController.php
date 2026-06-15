<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $sortField = 'clip_potential_score';
        $sortDir = 'desc';

        $allVideos = Video::orderByDesc($sortField)
                          ->orderByDesc('upload_date')
                          ->get();

        $videosData = $allVideos->map(function ($v) {
            return [
                'id'       => $v->id,
                'title'    => $v->title ?? '',
                'url'      => $v->url ?? '#',
                'extractor'=> $v->extractor ? ucfirst($v->extractor) : 'youtube',
                'duration' => $v->duration ?? 0,
                'duration_formatted' => $v->duration ? gmdate('H:i:s', $v->duration) : '-',
                'view_count' => $v->view_count ?? 0,
                'views_formatted' => $v->view_count ? number_format($v->view_count) : '-',
                'score'    => $v->clip_potential_score,
                'rec'      => $v->recommendation ?? 'N/A',
                'thumbnail' => $v->thumbnail ?? '',
            ];
        })->values()->toArray();

        $totalVideos = count($videosData);
        $highCount = $allVideos->filter(fn($v) => ($v->clip_potential_score ?? 0) >= 7.0)->count();
        $mediumCount = $allVideos->filter(fn($v) => ($v->clip_potential_score ?? 0) >= 4.0 && ($v->clip_potential_score ?? 0) < 7.0)->count();

        return view('dashboard', compact(
            'videosData', 'totalVideos', 'highCount', 'mediumCount',
            'sortField', 'sortDir'
        ));
    }
}
