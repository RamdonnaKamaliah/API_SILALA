<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuFavorit extends Model
{
    protected $table = 'favorits';

    protected $fillable = ['user_id', 'buku_id'];

    public function buku(){
        return $this->belongsTo(databuku::class, 'buku_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}