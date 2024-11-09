<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->guard('api')->check()) {
            return response()->json([
                'status' => 401,
                'successful' => false,
                'message' => 'Vui lòng đăng nhập !',
                'data' => null
            ]);
        }
        return $next($request);
    }
}
