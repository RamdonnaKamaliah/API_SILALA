<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'data_kategoris';
    protected $fillable = ['nama_kategori'];

    public function bukus() {
        return $this->belongsToMany(
            DataBuku::class,
            'buku_kategori',
            'data_buku_id',
            'data_kategori_id'
        );
    } 
}