<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        // Remover el prefijo "Bearer " si está presente
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        try {
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));
            
            // Agregar información del usuario al request
            $request->user_id = $decoded->user_id;
            $request->user_email = $decoded->email;
            $request->user_name = $decoded->name;
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token inválido o expirado',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}