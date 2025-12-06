<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\DataBuku;
use App\Models\DataPeminjaman;
use App\Models\RiwayatBaca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    // Simpan rating
    public function store(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:data_bukus,id',
            'rating' => 'required|integer|between:1,5'
        ]);

        // Cek apakah user sudah membaca/meminjam buku ini
        $hasRead = RiwayatBaca::where('user_id', Auth::id())
            ->where('buku_id', $request->buku_id)
            ->exists();

        $hasBorrowed = DataPeminjaman::where('user_id', Auth::id())
            ->where('buku_id', $request->buku_id)
            ->where('status', 'dipinjam')
            ->exists();

        if (!$hasRead && !$hasBorrowed) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membaca atau meminjam buku ini terlebih dahulu sebelum memberi rating.'
            ], 403);
        }

        // Simpan atau update rating
        $rating = Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'buku_id' => $request->buku_id
            ],
            [
                'rating' => $request->rating
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil disimpan!',
            'rating' => $rating
        ]);
    }

    // Ambil rating user untuk buku tertentu
    public function getUserRating($bukuId)
    {
        $rating = Rating::where('user_id', Auth::id())
            ->where('buku_id', $bukuId)
            ->first();

        return response()->json([
            'rating' => $rating
        ]);
    }

    // Hapus rating
    public function destroy($bukuId)
    {
        $rating = Rating::where('user_id', Auth::id())
            ->where('buku_id', $bukuId)
            ->first();

        if ($rating) {
            $rating->delete();
            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil dihapus!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Rating tidak ditemukan.'
        ], 404);
    }
}