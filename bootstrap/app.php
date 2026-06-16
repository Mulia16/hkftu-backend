<?php

use App\Support\ApiError;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Http\Middleware\SetPermissionsTeam;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', SetPermissionsTeam::class);

        Authenticate::redirectUsing(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiError::respond(
                code: 'VALIDATION_ERROR',
                message: 'The given data was invalid.',
                status: 422,
                fieldErrors: $e->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiError::respond(
                code: 'UNAUTHENTICATED',
                message: 'Authentication required.',
                status: 401,
            );
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiError::respond(
                code: 'FORBIDDEN',
                message: $e->getMessage() ?: 'You are not authorized to perform this action.',
                status: 403,
            );
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiError::respond(
                code: 'NOT_FOUND',
                message: 'The requested resource was not found.',
                status: 404,
            );
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiError::respond(
                code: 'HTTP_ERROR',
                message: $e->getMessage() ?: 'An error occurred.',
                status: $e->getStatusCode(),
            );
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiError::respond(
                code: 'INTERNAL_ERROR',
                message: app()->hasDebugModeEnabled() ? $e->getMessage() : 'Unexpected server error.',
                status: 500,
            );
        });
    })->create();
