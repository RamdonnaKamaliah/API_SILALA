<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BukuFavorit;
use App\Models\Favorit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BukuFavoritController extends Controller
{
    public function toggle(Request $request){
        $user = Auth::user();

        if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated'
        ], 401);
    }
        $bukuId = $request->buku_id;
        
        
        $favorite = BukuFavorit::where('user_id', $user->id)
                            ->where('buku_id', $bukuId)
                            ->first();

        if ($favorite){
            $favorite->delete();
            return response()->json([
                'message' => 'Berhasil menghapus dari favorit',
                'buku_id' => $bukuId
            ]);
        }else{
            BukuFavorit::create([
                'user_id' => $user->id,
                'buku_id' => $bukuId
            ]);
            return response()->json([
                'message' => 'Berhasil menambahkan ke favorit',
                'favorited' => true
            ]);
        }
    }

    public function apiIndex(){
        $favorites = BukuFavorit::where('user_id', Auth::id())->with('buku')->get();

        return response()->json([
            'succes' => true,
            'data' => $favorites
        ]);
    }
}