<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatBaca extends Model
{
    protected $table = 'riwayat_baca';
    protected $fillable = ['user_id', 'buku_id', 'terakhir_dibaca'];

    public function buku()
    {
        return $this->belongsTo(DataBuku::class, 'buku_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $casts = [
    'terakhir_dibaca' => 'datetime',
];

}