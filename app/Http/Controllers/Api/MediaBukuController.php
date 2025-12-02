<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaBuku;
use Illuminate\Http\Request;

class MediaBukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $media = MediaBuku::with('buku')->get();
        return response()->json([
           'status'=> true,
           'message'=>'data berhasil di temukan',
           'data'=> $media
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