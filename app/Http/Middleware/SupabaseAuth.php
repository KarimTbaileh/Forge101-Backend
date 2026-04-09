<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;

class SupabaseAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: No token provided'], 401);
        }

        try {
            $jwksUrl = 'https://cutebdgpidwqtwzlndqb.supabase.co/auth/v1/.well-known/jwks.json';
            $response = Http::timeout(10 )->get($jwksUrl);

            if (!$response->successful()) {
                return response()->json(['message' => 'Failed to fetch Supabase JWKS keys'], 500);
            }

            $jwksData = $response->json();
            $keys = JWK::parseKeySet($jwksData);

            $decoded = JWT::decode($token, $keys);

            $supabaseUserArray = json_decode(json_encode($decoded), true);

            $request->merge([
                'supabase_user' => $supabaseUserArray,
                'user_id' => $supabaseUserArray['sub']
            ]);


            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized: Invalid token',
                'debug'   => $e->getMessage()
            ], 401);
        }
    }
}
