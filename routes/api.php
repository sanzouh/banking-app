<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\WithdrawalAuditController;
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

// Only authenticated users can access routes with "auth:sanctum" middleware
// Fenêtre User → CRUD clients et withdrawals
Route::middleware(["auth:sanctum", "role:User"])->group(function (){
    Route::apiResource("clients", ClientController::class);
    Route::apiResource("withdrawals", WithdrawalController::class);
});

// Fenêtre Admin → lecture seule audit
Route::middleware(["auth:sanctum", "role:Admin"])->group(function (){
    // Audit — lecture seule
    Route::get('withdrawals-audit', [WithdrawalAuditController::class, 'index']);
    Route::get('withdrawals-audit/stats', [WithdrawalAuditController::class, 'stats']);
});

// Commun aux deux fenêtres
Route::middleware("auth:sanctum")->post("/logout", [AuthController::class, "logout"]);
