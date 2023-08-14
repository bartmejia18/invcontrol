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
                'status' => 0
            ]);

            $newUser = User::with('rol:id,rol')->find($user->id);

            $token  = JWTAuth::fromUser($newUser);

            return response()->json([
                'result' => 200,
                'message' => "Se ha guardado correctamente el registro",
                'records' => [
                    'user' => $newUser,
                    'token' => $token
                ]
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
        $user = User::where('user', $request->input('user'))->first();
        if ($user->status == 0) {
            if (!$token=auth()->attempt($validator->validated())){
                return response()->json(['error'=>'No se pudo iniciar sesión, valide el usuario y contraseña', 401]);
            }
            return $this->createNewToken($token);
        } else {
            return response()->json(['error'=>'El usuario no existe', 401]);
        }
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
