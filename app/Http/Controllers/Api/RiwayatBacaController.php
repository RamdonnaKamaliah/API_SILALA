<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\RiwayatBaca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatBacaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $riwayat = RiwayatBaca::with(['buku'])
            ->where('user_id', $user->id)
            ->orderByDesc('terakhir_dibaca')
            ->get();

        // Tambahkan average rating & total rating ke setiap buku
        $riwayat->each(function ($item) {
            $item->buku->average_rating = Rating::where('buku_id', $item->buku_id)
                ->avg('rating') ?? 0;
            $item->buku->total_ratings = Rating::where('buku_id', $item->buku_id)
                ->count();
        });

        return response()->json([
            'succes' => true,
            'riwayat' => $riwayat
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}