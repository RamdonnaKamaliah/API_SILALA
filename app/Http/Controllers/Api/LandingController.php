<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\databuku;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(){
        $dataBuku = databuku::latest()->get();

        return response()->json([
            'title' => 'Data Buku Landing Page',
            'data' => $dataBuku
        ], 200);
    }
}