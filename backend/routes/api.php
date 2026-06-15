<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\ShortlistController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\PreferencesController;

// Public GET — generous limit
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/discover', [DiscoveryController::class, 'index']);
    Route::get('/discover/{id}', [DiscoveryController::class, 'show']);
    Route::get('/shortlist', [ShortlistController::class, 'index']);
    Route::get('/preferences', [PreferencesController::class, 'index']);
});

// Write operations — moderate limit
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/shortlist', [ShortlistController::class, 'store']);
    Route::delete('/shortlist/{videoId}', [ShortlistController::class, 'destroy']);
    Route::post('/shortlist/toggle', [ShortlistController::class, 'toggle']);
    Route::post('/export', [ExportController::class, 'export']);
    Route::put('/preferences', [PreferencesController::class, 'update']);
});

// Heavy operations (yt-dlp scrape) — strict limit
Route::post('/discover', [DiscoveryController::class, 'store'])
    ->middleware('throttle:5,1');
