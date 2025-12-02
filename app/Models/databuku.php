<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class databuku extends Model
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
}