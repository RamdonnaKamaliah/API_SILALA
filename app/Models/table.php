<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class table extends Model
{
    protected $table = "table";
    protected $fillable = ['nama', 'penulis'];
}