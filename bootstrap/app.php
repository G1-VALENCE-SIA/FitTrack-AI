<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Bearer Token API only (Sanctum Token)
    })

    ->withExceptions(function (Exceptions $exceptions) {

        // Route not found
        $exceptions->render(function (NotFoundHttpException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'Route not found.',
                'message' => 'The endpoint you are trying to reach does not exist.',
            ], 404);
        });

        // Method not allowed
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'Method not allowed.',
                'message' => 'Use the correct HTTP method for this endpoint.',
            ], 405);
        });

        // Model not found
        $exceptions->render(function (ModelNotFoundException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            $model = strtolower(class_basename($e->getModel()));

            return response()->json([
                'error'   => 'Resource not found.',
                'message' => "No {$model} found with the given ID.",
            ], 404);
        });

        // Validation errors (includes duplicate email)
        $exceptions->render(function (ValidationException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'Validation failed.',
                'message' => $e->errors(),
            ], 422);
        });

        // Unauthenticated (missing token / invalid token)
        $exceptions->render(function (AuthenticationException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'Unauthenticated.',
                'message' => 'Please login and provide a valid Bearer token.',
            ], 401);
        });

        // Unauthorized (no permission)
        $exceptions->render(function (AuthorizationException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'Unauthorized.',
                'message' => $e->getMessage(),
            ], 403);
        });

        // Database errors
        $exceptions->render(function (QueryException $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            $errorCode = $e->errorInfo[1] ?? null;

            // Duplicate entry
            if ($errorCode == 1062) {
                return response()->json([
                    'error'   => 'Duplicate entry.',
                    'message' => 'A record with this value already exists.',
                ], 409);
            }

            // Foreign key constraint fail
            if ($errorCode == 1451 || $errorCode == 1452) {
                return response()->json([
                    'error'   => 'Database constraint error.',
                    'message' => 'This record is linked to other data and cannot be modified.',
                ], 409);
            }

            return response()->json([
                'error'   => 'Database error.',
                'message' => 'Something went wrong with the database. Please try again.',
            ], 500);
        });

        // Generic HTTP errors
        $exceptions->render(function (HttpExceptionInterface $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'HTTP error.',
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        // Catch-all unexpected errors (always LAST)
        $exceptions->render(function (\Throwable $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error'   => 'Unexpected error.',
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong. Please try again later.',
            ], 500);
        });
    })

    ->create();