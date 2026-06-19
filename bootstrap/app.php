<?php

declare(strict_types=1);

use App\Support\Api\ApiErrorResponseFactory;
use App\Support\Api\ApiProblemException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            ApiProblemException::class,
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            return app(ApiErrorResponseFactory::class)->make(
                request: $request,
                code: 'VALIDATION_FAILED',
                status: 422,
                details: ['fields' => $exception->errors()],
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return app(ApiErrorResponseFactory::class)->make(
                request: $request,
                code: 'UNAUTHORIZED',
                status: 401,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return app(ApiErrorResponseFactory::class)->make(
                request: $request,
                code: 'FORBIDDEN',
                status: 403,
            );
        });

        $exceptions->render(function (ApiProblemException $exception, Request $request) {
            return app(ApiErrorResponseFactory::class)->make(
                request: $request,
                code: $exception->errorCode,
                status: $exception->statusCode,
                details: $exception->details,
            );
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            return app(ApiErrorResponseFactory::class)->make(
                request: $request,
                code: 'NOT_FOUND',
                status: 404,
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if ($exception instanceof ApiProblemException) {
                return app(ApiErrorResponseFactory::class)->make(
                    request: $request,
                    code: $exception->errorCode,
                    status: $exception->statusCode,
                    details: $exception->details,
                );
            }

            return app(ApiErrorResponseFactory::class)->make(
                request: $request,
                code: 'INTERNAL_ERROR',
                status: 500,
            );
        });
    })->create();
