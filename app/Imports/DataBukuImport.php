<?php

namespace App\Imports;

use App\Models\DataBuku;
use App\Models\DataKategori;
use App\Models\GambarBuku;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DataBukuImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2; // Mulai baris ke-2
    }

    public function model(array $row)
    {
        // 1. Import FOTO dari Google Drive
        // ===============================
        $fotoUrl = $row[10];
        $localFotoPath = null;

        if ($fotoUrl && str_contains($fotoUrl, 'drive.google.com')) {

            $fileId = null;
            if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $fotoUrl, $m)) $fileId = $m[1];
            if (preg_match('/id=([a-zA-Z0-9_-]+)/', $fotoUrl, $m)) $fileId = $m[1];

            if ($fileId) {
                $direct = "https://drive.google.com/uc?export=download&id={$fileId}";

                try {
                    $response = Http::get($direct);

                    if ($response->ok()) {

                         $lastId = (GambarBuku::max('id') ?? 0) + 1;
                        $fileName = 'gambar-' . $lastId . '.jpg';
                        $path = "uploads/buku/{$fileName}";

                        Storage::disk('public')->put($path, $response->body());
                        $localFotoPath = $path;

                        // SIMPAN ke tabel gambar_bukus
                        GambarBuku::create([
                            // 'data_buku' =>$buku->id,
                            'nama_file' => $fileName,
                            'path_file' => $path,
                            
                        ]);
                    }
                } catch (\Exception $e) {
                    // Abaikan error
                }
            }
        }

        // ===============================
        // 2. Import FILE PDF Google Drive
        // ===============================
        $pdfUrl = $row[11];
        $localPdfPath = null;

        if ($pdfUrl && str_contains($pdfUrl, 'drive.google.com')) {

            // Ambil ID otomatis
            preg_match('/(?:id=|\/d\/)([a-zA-Z0-9_-]+)/', $pdfUrl, $match);
            $fileId = $match[1] ?? null;

            if ($fileId) {

                $direct = "https://drive.google.com/uc?export=download&id={$fileId}";

                try {
                    $response = Http::withOptions(['allow_redirects' => true])
                        ->get($direct);

                    // Simpan langsung tanpa cek content-type
                    if ($response->ok() && $response->body()) {

                        $fileName = time() . '_' . uniqid() . '.pdf';
                        $path = "uploads/file_buku/{$fileName}";

                        Storage::disk('public')->put($path, $response->body());

                        $localPdfPath = $path;
                    }
                } catch (\Exception $e) {
                    $localPdfPath = null;
                }
            }
        }


        // ===============================
        // 3. Simpan Data Buku
        // ===============================
        $buku = DataBuku::create([
            'judul_buku'     => $row[0],
            'penulis'        => $row[1],
            'penerbit'       => $row[2],
            'tahun_terbit'   => (int) $row[3],
            'bahasa'         => $row[4],
            'jumlah_halaman' => (int) $row[6],
            'edisi'          => $row[7],
            'stok'           => (int) $row[8],
            'deskripsi'      => $row[9],
            'foto_buku'      => $localFotoPath,
            'file_buku'      => $localPdfPath,
        ]);


        // ===============================
        // 4. Hubungkan Kategori
        // ===============================
        $kategoriNama = trim($row[5]);
        if ($kategoriNama) {
            $kategori = DataKategori::firstOrCreate(['nama_kategori' => $kategoriNama]);
            $buku->kategoris()->attach($kategori->id);
        }

        return $buku;
    }
}