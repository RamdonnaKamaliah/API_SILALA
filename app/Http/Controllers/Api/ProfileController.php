<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller

{
    public function index(){
           $user = Auth::user();
   
   $genderDisplay = '';
   if($user->gender == 'P'){
      $genderDisplay = 'Perempuan' ;
   }elseif ($user->gender == 'L'){
    $genderDisplay = 'Laki-Laki';
   }else {
    $genderDisplay = $user->gender ?? 'jenis kelamin belum di isi';
   }

   return Response()->json([
    'title' => 'PROFIL',
    'user' => $user,
    'genderDisplay' => $genderDisplay
   ]);
    }
 
   
}