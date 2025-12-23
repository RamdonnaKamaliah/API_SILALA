<?php

namespace App\Imports;

use App\Models\DataBuku;
use App\Models\Kategori;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DataBukuImport implements ToModel, WithStartRow
{
    private $successCount = 0;
    private $failedCount = 0;
    private $errors = [];

    public function startRow(): int
    {
        return 2; // Mulai baris ke-2
    }

    public function model(array $row)
    {
        try {
            Log::info('Memproses baris import', ['row' => $row]);

            // 1. Import FOTO dari Google Drive
            $fotoUrl = $row[10] ?? null;
            $localFotoPath = null;

            if ($fotoUrl && str_contains($fotoUrl, 'drive.google.com')) {
                $fileId = null;
                if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $fotoUrl, $m)) {
                    $fileId = $m[1];
                }
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $fotoUrl, $m)) {
                    $fileId = $m[1];
                }

                if ($fileId) {
                    $direct = "https://drive.google.com/uc?export=download&id={$fileId}";

                    try {
                        $response = Http::timeout(10)->get($direct);

                        if ($response->ok()) {
                            $fileName = time() . '_' . uniqid() . '.jpg';
                            $path = "uploads/buku/{$fileName}";

                            Storage::disk('public')->put($path, $response->body());
                            $localFotoPath = $path;
                            Log::info('Foto berhasil didownload', ['path' => $localFotoPath]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Gagal download foto', ['error' => $e->getMessage()]);
                        // Lanjutkan tanpa foto
                    }
                }
            }

            // 2. Import FILE PDF Google Drive
            $pdfUrl = $row[11] ?? null;
            $localPdfPath = null;

            if ($pdfUrl && str_contains($pdfUrl, 'drive.google.com')) {
                preg_match('/(?:id=|\/d\/)([a-zA-Z0-9_-]+)/', $pdfUrl, $match);
                $fileId = $match[1] ?? null;

                if ($fileId) {
                    $direct = "https://drive.google.com/uc?export=download&id={$fileId}";

                    try {
                        $response = Http::timeout(10)->withOptions(['allow_redirects' => true])->get($direct);

                        if ($response->ok() && $response->body()) {
                            $fileName = time() . '_' . uniqid() . '.pdf';
                            $path = "uploads/file_buku/{$fileName}";

                            Storage::disk('public')->put($path, $response->body());
                            $localPdfPath = $path;
                            Log::info('PDF berhasil didownload', ['path' => $localPdfPath]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Gagal download PDF', ['error' => $e->getMessage()]);
                        // Lanjutkan tanpa PDF
                    }
                }
            }

            // 3. Simpan Data Buku
            $buku = DataBuku::create([
                'judul_buku'     => $row[0] ?? '',
                'penulis'        => $row[1] ?? '',
                'penerbit'       => $row[2] ?? '',
                'tahun_terbit'   => (int) ($row[3] ?? date('Y')),
                'bahasa'         => $row[4] ?? 'Indonesia',
                'jumlah_halaman' => (int) ($row[6] ?? 0),
                'edisi'          => $row[7] ?? '1',
                'stok'           => (int) ($row[8] ?? 0),
                'deskripsi'      => $row[9] ?? '',
                'foto_buku'      => $localFotoPath,
                'file_buku'      => $localPdfPath,
                'status'         => 'aktif',
            ]);

            Log::info('Buku berhasil dibuat', ['id' => $buku->id, 'judul' => $buku->judul_buku]);

            // 4. Hubungkan Kategori
            $kategoriCell = trim($row[5] ?? '');

            if ($kategoriCell !== '') {
                $kategoriNames = array_map('trim', explode(',', $kategoriCell));
                $kategoriIds = [];

                foreach ($kategoriNames as $namaKategori) {
                    if ($namaKategori === '') continue;

                    $kategori = Kategori::firstOrCreate([
                        'nama_kategori' => $namaKategori
                    ]);

                    $kategoriIds[] = $kategori->id;
                }

                // simpan ke pivot table
                $buku->kategoris()->sync($kategoriIds);
                Log::info('Kategori berhasil disync', ['kategori_ids' => $kategoriIds]);
            }

            $this->successCount++;
            Log::info('Import baris berhasil', ['success_count' => $this->successCount]);
            
            return $buku;

        } catch (\Exception $e) {
            $this->failedCount++;
            $errorMsg = 'Import buku error: ' . $e->getMessage();
            
            Log::error($errorMsg, [
                'row' => $row,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->errors[] = [
                'row' => $row,
                'error' => $e->getMessage()
            ];

            // PENTING: Jangan return null, lempar exception agar Laravel Excel tahu ada error
            throw $e;
        }
    }

    public function getImportSummary()
    {
        return [
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'total' => $this->successCount + $this->failedCount,
            'errors' => $this->errors
        ];
    }
}