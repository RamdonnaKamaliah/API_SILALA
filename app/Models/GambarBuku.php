<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GambarBuku extends Model
{
     protected $fillable = [
        'nama_file',
        'path_file',
    ];
    public function buku()
{
    return $this->belongsTo(DataBuku::class, 'data_buku_id');
}

}