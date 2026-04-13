<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
          $middleware->alias([
           'firebasejwt' => \App\Http\Middleware\FirebaseJwtMiddleware::class,
           'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
            ], 500);
        });

    $exceptions->render(function (ModelNotFoundException $e) {
            $model = class_basename($e->getModel());
            return response()->json([
                'success' => false,
                'message' => "{$model} not found",
            ], 404);
        });

    $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found'
            ], 404);
        });


    })->create();

    