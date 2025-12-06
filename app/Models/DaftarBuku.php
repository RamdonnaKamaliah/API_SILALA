<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DaftarBuku extends Model
{
    protected $table = 'data_bukus';

    protected $fillable = [
        'foto_buku',
        'foto_id',
        'judul_buku',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'bahasa',
        'jumlah_halaman',
        'edisi',
        'deskripsi',
        'stok',
        'file_buku',
        'status',
        'kategori_ids',
    ];

    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'buku_kategori', 'data_buku_id', 'data_kategori_id');
    }
    public function foto()
{
    return $this->belongsTo(GambarBuku::class, 'foto_id');
}

    public function getFotoUrlAttribute()
    {
        $fotoUrl = $this->foto_buku; // ambil dari kolom model
        $localPath = null;

        if ($fotoUrl) {
            if (str_contains($fotoUrl, 'drive.google.com')) {
                if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $fotoUrl, $matches)) {
                    $fileId = $matches[1];
                } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $fotoUrl, $matches)) {
                    $fileId = $matches[1];
                } else {
                    $fileId = null;
                }

                if ($fileId) {
                    $directUrl = "https://drive.google.com/uc?export=view&id={$fileId}";

                    try {
                        $response = Http::get($directUrl);

                        if ($response->ok()) {
                            $fileName = time() . '_' . uniqid() . '.jpg';
                            $path = 'photos/' . $fileName;
                            Storage::disk('public')->put($path, $response->body());
                            $localPath = 'storage/' . $path;
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal download foto: ' . $e->getMessage());
                    }
                }
            }
        }

        return $localPath ?? null;
    }

    public function getFileUrlAttribute()
{
    $fileUrl = $this->file_buku;
    $localPath = null;

    if ($fileUrl && str_contains($fileUrl, 'drive.google.com')) {

        // Ambil file ID
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $fileUrl, $matches)) {
            $fileId = $matches[1];
        } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $fileUrl, $matches)) {
            $fileId = $matches[1];
        } else {
            return null;
        }

        // Link download PDF
        $directUrl = "https://drive.google.com/uc?export=download&id={$fileId}";

        try {
            $response = Http::withOptions(['stream' => true])->get($directUrl);

            if ($response->ok()) {

                // Pastikan folder ada
                Storage::disk('public')->makeDirectory('uploads/file_buku');

                $fileName = time().'_'.uniqid().'.pdf';
                $path = 'uploads/file_buku/'.$fileName;

                // Ambil stream body (aman untuk PDF)
                $content = $response->getBody()->getContents();

                Storage::disk('public')->put($path, $content);

                $localPath = 'storage/'.$path;
            }

        } catch (\Exception $e) {
            Log::error('Gagal download file PDF: ' . $e->getMessage());
        }

    }

    return $localPath ?? null;
}

public function gambar()
{
    return $this->hasMany(GambarBuku::class, 'data_buku_id');
}
}