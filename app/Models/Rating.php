<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $ratings;
    
    protected $fillable = [
        'user_id',
        'buku_id',
        'rating'];

    protected $casts = [
        'rating' => 'integer'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function buku(){
        return $this->belongsTo(databuku::class);
    }
}