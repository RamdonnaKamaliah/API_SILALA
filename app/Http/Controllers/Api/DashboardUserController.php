<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BukuFavorit;
use App\Models\DataPeminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardUserController extends Controller
{
    public function index (){
        $user = Auth::user();

        //jumlah buku yg di pinjam 
        $pinjam = DataPeminjaman::where('user_id', $user->id)
            ->where('status', 'dipinjam')->count();

        //jumlah buku favorite 
        $favorite = BukuFavorit::where('user_id', $user->id)
            ->count();
            
        //jumlah buku telat dikembalikan 
        $late = DataPeminjaman::where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->where('tanggal_kembali', '<', Carbon::now())
            ->count();

        return response()->json([
            'message' => 'Berhasil Masuk ke Dashboard',
            'dipinjam' => $pinjam,
            'telat' => $late
        ]);
    }
}