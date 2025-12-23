<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BukuFavorit;
use App\Models\databuku;
use App\Models\DataPeminjaman;
use App\Models\RiwayatBaca;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DetailBukuController extends Controller
{
    public function detail($id)
    {
        $buku = databuku::find($id);

        if (!$buku) {
            return response()->json([
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        $userId = Auth::id(); // hanya bisa terbaca jika pakai token
        $userBorrow = null;
        $hasRead = false;
        $isFavorited = false;
        $userRating = null;
        $averageRating = 0;
        $totalRatings = 0;
        $canRate = false;

        if ($userId) {
            $userBorrow = DataPeminjaman::where('user_id', $userId)
                ->where('buku_id', $buku->id)
                ->where('status', 'dipinjam')
                ->first();

            $hasRead = RiwayatBaca::where('user_id', $userId)
                ->where('buku_id', $buku->id)
                ->exists();

            $isFavorited = BukuFavorit::where('user_id', $userId)
                ->where('buku_id', $buku->id)
                ->exists();

            if (Schema::hasTable('ratings')) {
                $userRating = Rating::where('user_id', $userId)
                    ->where('buku_id', $buku->id)
                    ->first();

                $averageRating = Rating::where('buku_id', $buku->id)->avg('rating') ?? 0;
                $totalRatings = Rating::where('buku_id', $buku->id)->count();

                $canRate = ($hasRead || $userBorrow);
            }
        }

        return response()->json([
            'buku' => $buku,
            'stokHabis' => $buku->stok <= 0,
            'userBorrow' => $userBorrow,
            'isFavorited' => $isFavorited,
            'hasRead' => $hasRead,
            'userRating' => $userRating,
            'averageRating' => round($averageRating, 1),
            'totalRatings' => $totalRatings,
            'canRate' => $canRate,
        ], 200);
    }
}