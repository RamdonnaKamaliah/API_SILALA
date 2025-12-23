<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\DataBukuImport;
use App\Models\DataBuku;
use App\Models\GambarBuku;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;   

class DataBukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DataBuku::orderBy('asc')->get();
        return response()->json([
            'status' => true,
            'message' => 'Data Ditemukan',
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $rules = [
         'foto_buku' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'foto_id'   => 'nullable|exists:gambar_bukus,id',
        'judul_buku' => 'required',
        'penulis' => 'required',
        'penerbit' => 'required',
        'tahun_terbit' => 'required',
        'bahasa' => 'required',
        'kategori_id' => 'required|array|min:1',
        'jumlah_halaman' => 'required',
        'edisi' => 'required',
        'deskripsi' => 'required',
        'stok' => 'required',
        'file_buku' => 'required|mimes:pdf|max:10240',
    ];
    
    $validator = Validator::make($request->all(), $rules);
    if($validator->fails()){  // ✅ BENAR: huruf f kecil
    return response()->json([
        'status' => false,
        'message' => 'Gagal membuat data',
        'data' => $validator->errors()  // ✅ BENAR: errors
    ]);
}

    
    $foto_buku_path = null;

    // 1️⃣ Upload manual
    if ($request->hasFile('foto_buku')) {

        $file = $request->file('foto_buku');

        $path = $file->store('uploads/buku', 'public');  
        $foto_buku_path = $path;

        // Simpan ke tabel media
        GambarBuku::create([
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $path,
            'judul_buku' => $request->judul_buku,
        ]);

    }

     if ($request->foto_id) {
        $media = GambarBuku::find($request->foto_id);
        if ($media) {
            $foto_buku_path = $media->path_file;
        }
    }
    
      $buku = DataBuku::create([
        'judul_buku' => $request->judul_buku,
        'penulis' => $request->penulis,
        'penerbit' => $request->penerbit,
        'tahun_terbit' => $request->tahun_terbit,
        'bahasa' => $request->bahasa,
        'jumlah_halaman' => $request->jumlah_halaman,
        'edisi' => $request->edisi,
        'deskripsi' => $request->deskripsi,
        'stok' => $request->stok,

        'file_buku' => $request->file_buku
    ? $request->file_buku->store('uploads/file_buku', 'public')
    : null,

        'foto_buku' => $foto_buku_path,

    ]);

        $buku->kategoris()->attach($request->kategori_id);

        return response()->json([
            'status' => true,
            'message' => 'Data Berhasil dibuat'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = DataBuku::find($id);
        if($data){
            return response()->json([
                'status'=> true,
                'message' => 'Data ditemukan',
                'data' => $data
            ]);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ]);
        }
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
        $buku = DataBuku::find($id);
        if(empty($buku)){
            return response()->json([
                'status' => false,
                'message' => 'data tidak ditemukan'
            ]);
        }

        $buku->delete();
        return response()->json([
            'status' => true,
            'message' => 'data berhasil di hapus'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

       Excel::import(new DataBukuImport, $request->file('file'));

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dibuat'
        ]);
    }
}