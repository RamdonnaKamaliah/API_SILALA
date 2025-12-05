<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DaftarBukuController;
use App\Http\Controllers\Api\DasboardAdminController;
use App\Http\Controllers\Api\DataArsipContoller;
use App\Http\Controllers\Api\DataBukuContoller;
use App\Http\Controllers\Api\DataDendaController;
use App\Http\Controllers\Api\DataPeminjamanController;
use App\Http\Controllers\Api\DataUserController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\MediaBukuController;
use App\Http\Controllers\Api\RiwayatBacaController;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Server\Resource;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//route melihat data user berdasarkan request
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function(Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

//Register
Route::post('register', [AuthController::class, 'register']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

//Login
Route::post('login', [AuthController::class,'login']); //manual login
Route::post('/google/login', [AuthController::class,'googleLogin']); //Google login


Route::get('kategori', [KategoriController::class, 'index']);
Route::get('databuku', [DataBukuContoller::class, 'index']);
Route::get('arsip', [DataArsipContoller::class, 'index']);
Route::get('DataDenda', [DataDendaController::class, 'index']);
Route::get('DataPeminjaman', [DataPeminjamanController::class, 'index']);
Route::get('DataUser', [DataUserController::class, 'index']);
Route::get('MediaBuku', [MediaBukuController::class, 'index']);
Route::get('DasboardAdmin', [DasboardAdminController::class, 'index']);
Route::get('DaftarBuku', [DaftarBukuController::class, 'index']);