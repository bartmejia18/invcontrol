<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'user' => 'required|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'rol_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        } else {
            $user = User::create([
                'name' => $request->name,
                'user' => $request->user,
                'password' => Hash::make($request->password),
                'rol_id' => $request->rol_id,
                'status' => 1
            ]);

            $newUser = User::with('rol:id,name')->find($user->id);

            $token  = JWTAuth::fromUser($newUser);

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
       }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'user' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        } 
        if (!$token=auth()->attempt($validator->validated())){
            return response()->json(['error'=>'Unauthorized', 401]);
        }
        return $this->createNewToken($token);
    }

    public function createNewToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL()*60,
            'user' => auth()->user() 
        ]);
    }
}
