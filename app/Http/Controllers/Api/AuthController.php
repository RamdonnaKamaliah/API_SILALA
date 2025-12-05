<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    // Login via Google OAuth
    public function googleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->token);

        if (!$payload) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'],
                'google_id' => $payload['sub'],
                'password' => bcrypt(str()->random(16))
            ]
        );

        $token = $user->createToken('google-token')->plainTextToken;

        return response()->json([
            "message" => "Login Google berhasil",
            "user" => $user,
            "token" => $token
        ]);
    }

    //register manual
    public function register (Request $request) {
         $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'membership_type' => ['required', 'in:karyawan,magang'],
            'gender' => ['required', 'in:L,P'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create ([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'membership_type' => $request->membership_type,
            'gender'=> $request->gender,
            'password' => Hash::make($request->password),
            'password_setup' => true,
        ]);

        
         $token = $user->createToken('API Token')->plainTextToken;

         return response()->json([
            'message'=> 'Register Berhasil',
            'user' => $user,
            'token' => $token 
         ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}