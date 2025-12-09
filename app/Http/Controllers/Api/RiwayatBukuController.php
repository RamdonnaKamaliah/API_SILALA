<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPeminjaman;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RiwayatBukuController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = DataPeminjaman::where('user_id', $userId)
            ->with('buku')
            ->orderByDesc('created_at');

        if ($request->has('status')) {
            switch ($request->status) {
                case 'dikembalikan':
                    $query->where('status', 'dikembalikan');
                    break;
                case 'dipinjam':
                    $query->where('status', 'dipinjam');
                    break;
                case 'belum_dikembalikan':
                    $query->where('status', 'dipinjam')
                        ->where('tanggal_kembali', '<', now());
                    break;
            }
        }

        $riwayat = $query->get();

         // Jika tidak ada data terlambat, kembalikan array kosong dengan message yang jelas
            if ($request->status === 'belum_dikembalikan' && $riwayat->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada buku yang terlambat dikembalikan',
                    'data' => []
                ], 200);
            }
        
        return response()->json([
            'success' => true,
            'message' => 'Data riwayat peminjaman',
            'data' => $riwayat
        ]);
    }

     // Method untuk mengecek apakah user sedang meminjam buku
    public function checkActiveBorrow()
    {
        try {
            $userId = Auth::id();
            
            $activeBorrows = DataPeminjaman::where('user_id', $userId)
                ->where('status', 'dipinjam')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'hasActiveBorrow' => $activeBorrows > 0,
                    'activeCount' => $activeBorrows
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status peminjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //cek peminjaman buku tertentu 
    
     public function checkBookBorrowStatus($bookId)
    {
        try {
            $userId = Auth::id();
            
            $activeBorrow = DataPeminjaman::where('user_id', $userId)
                ->where('buku_id', $bookId)
                ->where('status', 'dipinjam')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'isBorrowed' => !is_null($activeBorrow),
                    'borrowData' => $activeBorrow
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $data = DataPeminjaman::create([
            'user_id' => Auth::id(),
            'buku_id' => $request->buku_id,
            'tanggal_pinjam' => now(),
            'tanggal_kembali' => $request->tanggal_kembali,
            'status' => 'dipinjam',
            'keterangan' => 'Sedang dipinjam',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dipinjam',
            'data' => $data
        ]);
    }

     /**
     * Kembalikan buku tanpa foto
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function kembalikanBuku($id)
    {
        try {
            $peminjaman = DataPeminjaman::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$peminjaman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peminjaman tidak ditemukan'
                ], 404);
            }

            // Update keterangan jika terlambat
            $tanggalKembali = Carbon::parse($peminjaman->tanggal_kembali);
            $sekarang = Carbon::now();
            
            // Reset waktu ke 00:00:00 untuk perhitungan hari murni
            $tanggalKembali->startOfDay();
            $sekarang->startOfDay();
            
            if ($sekarang->gt($tanggalKembali)) {
                $hariTelat = abs($sekarang->diffInDays($tanggalKembali));
                $peminjaman->keterangan = 'Terlambat ' . $hariTelat . ' hari - Sudah dikembalikan';
            } else {
                $peminjaman->keterangan = 'Tepat waktu - Sudah dikembalikan';
            }

            // Ubah status menjadi menunggu konfirmasi admin
            $peminjaman->status = 'menunggu_konfirmasi';
            $peminjaman->save();

            return response()->json([
                'success' => true,
                'message' => 'Buku dikembalikan. Menunggu konfirmasi admin.',
                'data' => $peminjaman
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengembalikan buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kembalikan buku dengan foto bukti
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kembalikanBukuWithPhoto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'buku_id' => 'required|exists:data_peminjams,id',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120' // Maksimal 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cari data peminjaman
            $peminjaman = DataPeminjaman::where('id', $request->buku_id)
                ->where('user_id', Auth::id())
                ->where('status', 'dipinjam')
                ->first();

            if (!$peminjaman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peminjaman tidak ditemukan atau buku sudah dikembalikan'
                ], 404);
            }

            // Simpan foto ke storage
            if ($request->hasFile('foto')) {
                // Generate nama file yang unik
                $fileName = 'pengembalian_' . time() . '_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
                
                // Simpan file ke storage
                $path = $request->file('foto')->storeAs('pengembalian', $fileName, 'public');
                
                // Update data peminjaman
                $peminjaman->foto_bukti_pengembalian = $path;
            }

            // Hitung keterlambatan
            $tanggalKembali = Carbon::parse($peminjaman->tanggal_kembali);
            $sekarang = Carbon::now();
            
            // Reset waktu ke 00:00:00 untuk perhitungan hari murni
            $tanggalKembali->startOfDay();
            $sekarang->startOfDay();
            
            // Update keterangan berdasarkan keterlambatan
            if ($sekarang->gt($tanggalKembali)) {
                $hariTelat = abs($sekarang->diffInDays($tanggalKembali));
                $peminjaman->keterangan = 'Terlambat ' . $hariTelat . ' hari - Menunggu konfirmasi admin';
            } else {
                $peminjaman->keterangan = 'Tepat waktu - Menunggu konfirmasi admin';
            }

            // Update data pengembalian
            $peminjaman->status = 'menunggu_konfirmasi';
            $peminjaman->metode_pengembalian = 'mandiri';
            $peminjaman->waktu_pengembalian_aktual = $sekarang;
            $peminjaman->save();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dikembalikan dengan foto. Menunggu konfirmasi admin.',
                'data' => [
                    'peminjaman_id' => $peminjaman->id,
                    'buku' => $peminjaman->buku->judul_buku,
                    'waktu_pengembalian' => $sekarang->format('d F Y H:i:s'),
                    'status' => 'menunggu_konfirmasi',
                    'foto_bukti' => $peminjaman->foto_bukti_pengembalian,
                    'foto_url' => Storage::url($peminjaman->foto_bukti_pengembalian)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengembalikan buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}