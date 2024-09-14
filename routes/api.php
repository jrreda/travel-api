<?php

use App\Http\Controllers\API\V1\TourController;
use App\Http\Controllers\API\V1\TravelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function() {
    Route::get('/travels', [TravelController::class, 'index'])->name('public-travels');
    Route::get('/travels/{travel:slug}/tours', [TourController::class, 'index'])->name('public-tours');
});
