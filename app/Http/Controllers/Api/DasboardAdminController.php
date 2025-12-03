<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\databuku;
use App\Models\DataPeminjaman;
use Illuminate\Http\Request;

class DasboardAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
           'totalBuku' => databuku::count(),
           'peminjamAktif' => DataPeminjaman::whereNull('tanggal_kembali')->count(),
           'bukuDipinjam' => DataPeminjaman::whereNull('tanggal_pinjam')->count(),
        ], 200);
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