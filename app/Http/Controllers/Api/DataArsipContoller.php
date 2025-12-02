<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\databuku;
use Illuminate\Http\Request;

class DataArsipContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $arsip = databuku::where('status', 'arsip')->get();

         if ($arsip->isEmpty()){
            return response()->json([
                'status'=> false,
                'message'=> 'data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status'=> true,
            'message'=> 'data arsip ditemukan',
            'data' => $arsip
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