<?php

use App\Http\Controllers\API\V1\Admin\TourController as AdminTourController;
use App\Http\Controllers\API\V1\Admin\TravelController as AdminTravelController;
use App\Http\Controllers\API\V1\Auth\LoginController;
use App\Http\Controllers\API\V1\TourController;
use App\Http\Controllers\API\V1\TravelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function() {
    Route::post('login', LoginController::class);

    Route::get('/travels', [TravelController::class, 'index'])->name('public-travels');
    Route::get('/travels/{travel:slug}/tours', [TourController::class, 'index'])->name('public-tours');

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function() {
        Route::post('travels', [AdminTravelController::class, 'store']);
        Route::post('travels/{travel}/tours', [AdminTourController::class, 'store']); // travel_id
    });
});
