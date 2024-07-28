<?php

use App\Http\Middleware\ConvertRequestToSnakeCase;
use App\Http\Middleware\ConvertResponseToCamelCase;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            ConvertRequestToSnakeCase::class,
            ConvertResponseToCamelCase::class,
        ]);
    })
    ->withRouting(
        using: function () {
            Route::middleware(['api'])
                ->group(base_path('routes/api.php'));
        },
        health: '/up',
    )
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        });

        return $exceptions;
    })->create();
