<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/getUnavailableOptions', [ReservationController::class, 'getUnavailableOptions']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications-unread', [NotificationController::class, 'indexUnreadNotifications']);
    Route::patch('/mark-notification/{id}', [NotificationController::class, 'markNotification']);
    Route::patch('/mark-notifications', [NotificationController::class, 'markAllNotifications']);
    Route::get('/notifications/delete', [NotificationController::class, 'destroyAll']);
    Route::delete('/notification/{id}', [NotificationController::class, 'destroy']);
});
