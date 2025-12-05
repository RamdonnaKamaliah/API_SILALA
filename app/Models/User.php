<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'membership_type',
        'gender',
        'google_id',
        'google_token',
        'google_refresh_token',
        'password_setup',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeKaryawan($query){
        return $query->where('membersip_type', 'karyawan');
    }

    public function scopeMagang($query){
        return $query->where('membership_type', 'magang');
    }

    public function getMemberTypeAttribute(){
        $labels = [
          'karyawan' => 'karyawan',
          'magang' => 'Magang/PKL'  
        ];
        
        return $labels[$this->membership_type] ?? 'Tidak diketahui';
    }
    
}