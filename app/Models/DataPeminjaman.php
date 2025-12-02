<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPeminjaman extends Model
{
     protected $table = 'data_peminjams';
    protected $fillable = [
        'user_id',
        'buku_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'denda',
        'status',
        'keterangan'
    ];

    protected $dates = [
        'tanggal_pinjam',
        'tanggal_kembali'
    ];

}