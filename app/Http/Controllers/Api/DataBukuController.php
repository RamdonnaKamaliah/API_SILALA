<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\databuku;
use App\Models\GambarBuku;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Imports\DataBukuImport;

class DataBukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       try {
        $data = DataBuku::with('kategoris')
        ->where('status', 'aktif')
        ->get();
        return response()->json([
            'status' => true,
            'message' => 'Data buku berhasil diambil!',
            'data' => $data
        ]);
       } catch (\Exception $e) {
           return response()->json([
               'status' => false,
               'message' => 'Gagal mengambil data buku!',
               'error' => $e->getMessage()
           ], 500);
       }
    }

    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
    {
        $validated = $request->validate([
            'foto_buku' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'foto_id'   => 'nullable|exists:gambar_bukus,id',
            'judul_buku' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4|integer',
            'bahasa' => 'required|string|max:100',
            'kategori_id' => 'required|array|min:1',
            'kategori_id.*' => 'exists:data_kategoris,id',
            'jumlah_halaman' => 'required|integer|min:1',
            'edisi' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'stok' => 'required|integer|min:0',
            'file_buku' => 'required|mimes:pdf|max:5120',
        ]);

        try {
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
                ]);
            }

            // 2️⃣ Pilih dari galeri (jika ada foto_id)
            if ($request->foto_id && !$foto_buku_path) {
                $media = GambarBuku::find($request->foto_id);
                if ($media) {
                    $foto_buku_path = $media->path_file;
                }
            }

            // 3️⃣ Upload file buku (PDF)
            $file_buku_path = null;
            if ($request->hasFile('file_buku')) {
                $file_buku_path = $request->file('file_buku')->store('uploads/file_buku', 'public');
            }

            // 4️⃣ Simpan data buku
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
                'foto_buku' => $foto_buku_path,
                'file_buku' => $file_buku_path,
                'kategori_ids' => implode(',', $request->kategori_id),
                'status' => 'aktif'
            ]);

            // 5️⃣ Attach kategori (many-to-many)
            $buku->kategoris()->attach($request->kategori_id);

            return response()->json([
                'status' => true,
                'message' => 'Data buku berhasil disimpan!',
                'data' => $buku->load('kategoris')
            ], 201);

        } catch (\Exception $e) {
            // Hapus file jika ada error
            if (isset($foto_buku_path)) {
                Storage::disk('public')->delete($foto_buku_path);
            }
            if (isset($file_buku_path)) {
                Storage::disk('public')->delete($file_buku_path);
            }

            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan data buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $buku = DataBuku::with('kategoris')->findOrFail($id);
            
            return response()->json([
                'status' => true,
                'message' => 'Data ditemukan',
                'data' => $buku
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'foto_buku' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'judul_buku' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4|integer',
            'bahasa' => 'required|string|max:100',
            'kategori_id' => 'required|array',
            'kategori_id.*' => 'exists:data_kategoris,id',
            'jumlah_halaman' => 'required|integer|min:1',
            'edisi' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'stok' => 'required|integer|min:0',
            'file_buku' => 'nullable|mimes:pdf|max:5120',
        ]);

        try {
            $buku = DataBuku::findOrFail($id);
            $oldFoto = $buku->foto_buku;
            $oldFile = $buku->file_buku;

            // Update foto jika ada file baru
            if ($request->hasFile('foto_buku')) {
                // Hapus foto lama
                if ($oldFoto && Storage::disk('public')->exists($oldFoto)) {
                    Storage::disk('public')->delete($oldFoto);
                }
                
                $foto_path = $request->file('foto_buku')->store('uploads/buku', 'public');
                $buku->foto_buku = $foto_path;
            }

            // Update file buku jika ada file baru
            if ($request->hasFile('file_buku')) {
                // Hapus file lama
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
                
                $file_path = $request->file('file_buku')->store('uploads/file_buku', 'public');
                $buku->file_buku = $file_path;
            }

            // Update field lainnya
            $buku->judul_buku = $request->judul_buku;
            $buku->penulis = $request->penulis;
            $buku->penerbit = $request->penerbit;
            $buku->tahun_terbit = $request->tahun_terbit;
            $buku->bahasa = $request->bahasa;
            $buku->jumlah_halaman = $request->jumlah_halaman;
            $buku->edisi = $request->edisi;
            $buku->deskripsi = $request->deskripsi;
            $buku->stok = $request->stok;
            $buku->kategori_ids = implode(',', $request->kategori_id);
            $buku->save();

            // Update pivot kategori
            $buku->kategoris()->sync($request->kategori_id);

            return response()->json([
                'status' => true,
                'message' => 'Data buku berhasil diperbarui!',
                'data' => $buku->load('kategoris')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $buku = DataBuku::findOrFail($id);
       
        $buku->delete();
        return response()->json([
            'status' => true,
            'message' => 'Data buku berhasil dihapus!',
        ]);
    }

   public function import(Request $request)
{
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new DataBukuImport, $request->file('file'));

            return response()->json([
                'status' => true,
                'message' => 'Data buku berhasil diimpor!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengimpor data',
                'error' => $e->getMessage()
            ], 500);
        }
}

   public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:data_bukus,id'
        ]);

        try {
            DataBuku::whereIn('id', $request->ids)
                ->update(['status' => 'arsip']);

            return response()->json([
                'status' => true,
                'message' => count($request->ids) . ' buku berhasil diarsipkan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengarsipkan buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete( Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:data_bukus,id'
        ]);

        try {
            DataBuku::whereIn('id', $request->ids)->delete();

            return response()->json([
                'status' => true,
                'message' => count($request->ids) . ' buku berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore($id) {
        try {
            $buku = DataBuku::withTrashed()->findOrFail($id);
            $buku->restore();

            $buku->status = 'aktif';
            $buku->save();

            return response()->json([
                'status' => true,
                'message' => 'Buku berhasil dipulihkan',
                'data' => $buku
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Buku Gagal dipulihkan',
                'error' => $e->getMessage()
            ], 500);
        }
    } 
}