<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::findByEmail($request->email);

        if (!$user || !password_verify($request->password, $user['password'])) {
            return response()->json([
                'error' => 'Credenciales inválidas'
            ], 401);
        }

        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'iat' => time(),
            'exp' => time() + (config('jwt.ttl') * 60),
        ];

        $jwt = JWT::encode($payload, config('jwt.secret'), config('jwt.algo'));

        return response()->json([
            'token' => $jwt,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // En una implementación real, podrías manejar una lista negra de tokens
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function me(Request $request)
    {
        $user = User::findById($request->user_id);
        
        if (!$user) {
            return response()->json([
                'error' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ]
        ]);
    }
}