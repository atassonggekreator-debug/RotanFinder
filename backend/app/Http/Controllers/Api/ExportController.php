<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * POST /api/export — export data as blob (JSON or CSV).
     */
    public function export(Request $request): JsonResponse|StreamedResponse
    {
        $validated = $request->validate([
            'format' => 'required|string|in:json,csv',
            'ids'    => 'nullable|array',
            'ids.*'  => 'integer|exists:videos,id',
        ]);

        $format = $validated['format'];
        $ids = $validated['ids'] ?? null;

        $query = Video::orderByDesc('clip_potential_score');
        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }
        $videos = $query->get();

        $data = $videos->map(function (Video $v): array {
            return [
                'id'            => $v->id,
                'title'         => $v->title ?? '',
                'url'           => $v->url ?? '#',
                'platform'      => $v->extractor ?? 'youtube',
                'creator'       => $v->uploader ?? 'Unknown',
                'duration'      => $v->duration ?? 0,
                'view_count'    => $v->view_count ?? 0,
                'like_count'    => $v->like_count ?? 0,
                'comment_count' => $v->comment_count ?? 0,
                'score'         => $v->clip_potential_score,
                'rec'           => $v->recommendation ?? 'N/A',
                'thumbnail'     => $v->thumbnail ?? '',
            ];
        })->values();

        if ($format === 'json') {
            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        }

        // CSV export as a downloadable streamed response
        $filename = 'rotan-finder-export-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'ID', 'Title', 'URL', 'Platform', 'Creator',
                'Duration', 'View Count', 'Like Count', 'Comment Count',
                'Score', 'Recommendation', 'Thumbnail',
            ]);

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
