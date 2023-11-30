<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AuthApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      // $apiKey = $request->header('X-API-Key');
      $apiKey = $request->query('api_key');

     if (!$apiKey || !User::where('api_key', $apiKey)->exists()) {
         return response()->json(['error' => 'Unauthorized. Invalid API key.'], 401);
     }

        return $next($request);
    }
}
