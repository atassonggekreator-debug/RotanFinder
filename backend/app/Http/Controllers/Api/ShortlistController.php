<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ShortlistController extends Controller
{
    /**
     * GET /api/shortlist — get shortlisted videos.
     */
    public function index(): JsonResponse
    {
        $shortlist = Session::get('shortlist', []);

        return response()->json([
            'candidates' => $shortlist,
        ]);
    }

    /**
     * POST /api/shortlist — toggle shortlist (add/remove).
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'candidate_id' => 'required|integer',
            'action'       => 'required|string|in:add,remove',
        ]);

        $candidateId = $validated['candidate_id'];
        $action = $validated['action'];
        $shortlist = Session::get('shortlist', []);

        if ($action === 'add') {
            if (!in_array($candidateId, $shortlist, true)) {
                $shortlist[] = $candidateId;
            }
            $message = "Video {$candidateId} added to shortlist.";
        } else {
            $shortlist = array_values(
                array_filter($shortlist, fn ($id) => (int) $id !== $candidateId)
            );
            $message = "Video {$candidateId} removed from shortlist.";
        }

        Session::put('shortlist', $shortlist);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $shortlist,
        ]);
    }
}
