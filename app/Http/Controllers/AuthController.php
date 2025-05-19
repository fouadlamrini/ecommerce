<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);

        if (User::count() === 1) {
            $user->assignRole('super_admin');
        } else {
            $user->assignRole('product_manager');
        }

        return response()->json([
            'user' => $user->load('roles'),
            'message' => 'Registered successfully. Now login to get your token.'
        ], 201);
    }
///////////////////////////////////////////////////
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->hasAnyRole(['super_admin', 'product_manager', 'user_manager'])) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Send POST request to /oauth/token
        $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '*',
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Token request failed'], 500);
        }

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $response->json()
        ]);
    }
/////////////////////////////////////////////////////////////////////////////////////
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out'], 200);
    }
}
