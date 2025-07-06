<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RoomController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/hotels/hotelsearch',[HotelController::class,'search']);
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/users', [AuthController::class, 'allUsers']);
    Route::put('/user/{id}', [AuthController::class, 'updateUser']);

    //  HOTEL APIS 
    
    Route::patch('hotels/{id}/toggle-status', [HotelController::class, 'toggleStatus']);
    Route::get('/hotels/stats', [HotelController::class, 'getStats']);
    Route::apiResource('hotels', HotelController::class);

    //  ROOM APIS
    Route::get('/rooms/{id}/check-availability', [RoomController::class, 'checkAvailability']);
    Route::get('/hotels/{hotelId}/rooms/check-availability', [RoomController::class, 'checkAvailabilityByHotel']); 
    Route::get('/rooms/statistics/summary', [RoomController::class, 'statistics']);     
    Route::apiResource('rooms', RoomController::class);

    //  BOOKING APIS
    Route::patch('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('/bookings/user/{userId}', [BookingController::class, 'getBookingsByUser']);
    Route::patch('bookings/{id}/status', [BookingController::class, 'updateStatus']);
     Route::apiResource('bookings', BookingController::class);

});
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

