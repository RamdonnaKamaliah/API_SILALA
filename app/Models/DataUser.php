<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataUser extends Model


{

    protected $table = 'users';
    
    protected $fillable = [
    'name',
    'phone',
    'email',
    'password',
    'membership_type',
    'gender',
    'google_id',
    'google_token',
    'google_refresh_token',
    'password_setup',
];


    protected $hidden = [
        'password',
        'remember_token',
    ];

        protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Scope untuk user karyawan
     */
    public function scopeKaryawan($query)
    {
        return $query->where('membership_type', 'karyawan');
    }

    /**
     * Scope untuk user magang/pkl
     */
    public function scopeMagang($query)
    {
        return $query->where('membership_type', 'magang');
    }

    /**
     * Accessor untuk label membership type
     */
    public function getMembershipTypeLabelAttribute()
    {
        $labels = [
            'karyawan' => 'Karyawan',
            'magang' => 'Magang/PKL',
        ];

        return $labels[$this->membership_type] ?? 'Tidak Diketahui';
    }
    
}