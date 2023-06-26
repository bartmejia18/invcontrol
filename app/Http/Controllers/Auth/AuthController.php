<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
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
            'email' => 'required|max:255|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $user->assignRole($request->input('roles'));

            $user->token  = JWTAuth::fromUser($user);

            return new UserResource($user);
       }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
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
        auth()->user()->token = $token;
        return new UserResource(auth()->user());
    }

    public function profile() {
        return new UserResource(auth()->user());
    }

    public function logout() {
        auth()->logout();
        return response()->json([
            'message' => 'User logged out'
        ]);
    }
}
