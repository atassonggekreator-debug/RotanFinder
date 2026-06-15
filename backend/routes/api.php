<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\ShortlistController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\PreferencesController;

Route::get('/discover', [DiscoveryController::class, 'index']);
Route::post('/discover', [DiscoveryController::class, 'store']);
Route::get('/discover/{id}', [DiscoveryController::class, 'show']);

Route::get('/shortlist', [ShortlistController::class, 'index']);
Route::post('/shortlist', [ShortlistController::class, 'store']);
Route::delete('/shortlist/{videoId}', [ShortlistController::class, 'destroy']);
Route::post('/shortlist/toggle', [ShortlistController::class, 'toggle']);

Route::post('/export', [ExportController::class, 'export']);

Route::get('/preferences', [PreferencesController::class, 'index']);
Route::put('/preferences', [PreferencesController::class, 'update']);
