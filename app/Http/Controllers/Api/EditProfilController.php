<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EditProfilController extends Controller

{
    public function index() {
        $user = Auth::user();

        return response()->json([
            'title' => 'EDIT PROFIL',
            'user' => $user
        ], 200);
    }

    public function update(Request $request){
          $user = User::find(Auth::id());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'membership_type' => 'required|in:Karyawan,Magang',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $user->name =$validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];

        if(isset($validated['gender'])){
            $user->gender = $validated['gender'] === 'Laki-laki' ? 'L' : 'P';
        }

         $user->membership_type = $validated['membership_type'];

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil && Storage::exists('public/' . $user->foto_profil)) {
                Storage::delete('public/' . $user->foto_profil);
            }
            $path = $request->file('foto_profil')->store('foto_profil', 'public');
            $user->foto_profil = $path;
        }

         if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Password lama salah'
                ], 400);
            }

            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }
        }

        $user->save();
        
        return response()->json([
            'message' => 'profile berhasil diperbarui!',
            'user' => $user
        ]);
        
    }
}