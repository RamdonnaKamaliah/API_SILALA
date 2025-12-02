<?php

use App\Http\Controllers\Api\DataArsipContoller;
use App\Http\Controllers\Api\DataBukuContoller;
use App\Http\Controllers\Api\DataDendaController;
use App\Http\Controllers\Api\DataPeminjamanController;
use App\Http\Controllers\Api\DataUserController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\MediaBukuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Server\Resource;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('kategori', [KategoriController::class, 'index']);
Route::get('databuku', [DataBukuContoller::class, 'index']);
Route::get('arsip', [DataArsipContoller::class, 'index']);
Route::get('DataDenda', [DataDendaController::class, 'index']);
Route::get('DataPeminjaman', [DataPeminjamanController::class, 'index']);
Route::get('DataUser', [DataUserController::class, 'index']);
Route::get('MediaBuku', [MediaBukuController::class, 'index']);