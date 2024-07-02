<?php

namespace App\Http\Middleware;

use App\Services\Supabase;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SupabaseMiddleware
{
    protected Supabase $supabase;

    public function __construct()
    {
        $this->supabase = new Supabase();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     *
     * @throws ConnectionException
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (! $request->header('Authorization')) {
                return response()->json(['error' => true], 401);
            }

            logger('Authenticating with Supabase ', [$request->header('Authorization')]);

            $headers = $this->supabase
                ->setHeader('Authorization', $request->header('Authorization'))
                ->getHeaders();
            $getUrl = $this->supabase->getAuthUriBase('user');

            $response = Http::withHeaders($headers)->get($getUrl);

            if ($response->failed()) {
                return response()->json(['error' => $response->json()], 401);
            }

            $request->merge([
                'u' => $response->json(),
            ]);

            return $next($request);
        } catch (ConnectionException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

    }
}
