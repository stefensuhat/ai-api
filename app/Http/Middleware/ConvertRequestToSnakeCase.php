<?php

namespace App\Http\Middleware;

use App\Helpers\KeyCaseConverter;
use Closure;
use Illuminate\Http\Request;

class ConvertRequestToSnakeCase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $request->replace(
            resolve(KeyCaseConverter::class)->convert(
                KeyCaseConverter::CASE_SNAKE,
                $request->all()
            )
        );

        return $next($request);
    }
}
