<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BukuFavoritController;
use App\Http\Controllers\Api\DaftarBukuController;
use App\Http\Controllers\Api\DasboardAdminController;
use App\Http\Controllers\Api\DashboardUserController;
use App\Http\Controllers\Api\DataArsipContoller;
use App\Http\Controllers\Api\DataBukuController;
use App\Http\Controllers\Api\DataDendaController;
use App\Http\Controllers\Api\DataPeminjamanController;
use App\Http\Controllers\Api\DataUserController;
use App\Http\Controllers\Api\DetailBukuController;
use App\Http\Controllers\Api\EditProfilController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\LandingController;
use App\Http\Controllers\Api\MediaBukuController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RiwayatBacaController;
use App\Http\Controllers\Api\RiwayatBukuController;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Server\Resource;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Register
Route::post('register', [AuthController::class, 'register']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

//Login
Route::post('login', [AuthController::class,'login']); //manual login
Route::post('/google/login', [AuthController::class,'googleLogin']); //Google login


Route::get('kategori', [KategoriController::class, 'index']);
Route::get('arsip', [DataArsipContoller::class, 'index']);
Route::get('DataDenda', [DataDendaController::class, 'index']);
Route::get('DataPeminjaman', [DataPeminjamanController::class, 'index']);
Route::get('DataUser', [DataUserController::class, 'index']);
Route::get('MediaBuku', [MediaBukuController::class, 'index']);
Route::get('DasboardAdmin', [DasboardAdminController::class, 'index']);
Route::get('DaftarBuku', [DaftarBukuController::class, 'index']);
Route::get('landingPage', [LandingController::class, 'index']);
Route::get('dataBuku', [DataBukuController::class, 'index']);
Route::post('buku', [DataBukuController::class, 'store']);


// route yang butuh token
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function(Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/favorite/toggle', [BukuFavoritController::class, 'toggle']);
    Route::get('/favorite', [BukuFavoritController::class, 'apiIndex']);
    Route::post('rating', [RatingController::class, 'store']);
    Route::get('/rating/{bukuId}', [RatingController::class, 'getUserRating']);
    Route::delete('/rating/{bukuId}', [RatingController::class, 'destroy']);
    Route::get('/riwayat', [RiwayatBacaController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile/update', [EditProfilController::class, 'update']);
    Route::get('/dashboard', [DashboardUserController::class, 'index']);
    Route::get('/detail-buku/{id}', [DetailBukuController::class, 'detail']);
    Route::prefix('riwayat-buku')->group(function () {
        Route::get('/', [RiwayatBukuController::class, 'index']);
        Route::get('/check-active', [RiwayatBukuController::class, 'checkActiveBorrow']);
        Route::get('/check-book/{bookId}', [RiwayatBukuController::class, 'checkBookBorrowStatus']);
        Route::post('/pinjam', [RiwayatBukuController::class, 'store']);
        Route::put('/kembalikan/{id}', [RiwayatBukuController::class, 'kembalikanBuku']);
        Route::post('/kembalikan-foto', [RiwayatBukuController::class, 'kembalikanBukuWithPhoto']);
        
    });
});