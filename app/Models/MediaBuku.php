<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaBuku extends Model
{

     protected $table = 'gambar_bukus';
     protected $fillable = [
        'nama_file',
        'path_file',
        'judul_buku'
    ];
    public function buku()
{
    return $this->belongsTo(DataBuku::class, 'data_buku_id');
}

}