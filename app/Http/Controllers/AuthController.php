<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  public function login(Request $request){
      
      $validateData =$request->validate([
          'email' => 'required|email',
          'password' => 'required'
        ]);
        $user = User::where('email', $validateData['email'])->first();
        if(!$user || !Hash::check($validateData['password'], $user->password)){
            return response()->json([
                'message' => 'Invalid Username or Password'
            ], 404);
        } 
    $token=$user->createToken('auth_token')->accessToken;
    return response()->json([
      'message' => 'Login Successful',
      'access_token' => $token,
      'user' => $user
    ], 200);

  }
}
