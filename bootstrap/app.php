<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'must_change_password' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            'api_docs_admin' => \App\Http\Middleware\EnsureIsApiDocsAdmin::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ApplyRememberMeSessionLifetime::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.',
                    'data' => null,
                ], 401);
            }
        });
    })->create();
