<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'abilities:users:read'])->get('/users', function(Request $request){
    return User::all();
}); */


Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);


Route::middleware("auth:sanctum")->group(function (){
    // Only authenticated users can access this
    
    Route::apiResource("clients", ClientController::class);
    Route::apiResource("withdrawals", WithdrawalController::class);
    // Route::post("/logout", AuthController::class); 
});