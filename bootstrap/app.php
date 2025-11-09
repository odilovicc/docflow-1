<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware алиасы для разных типов авторизации
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        
        // Web middleware stack
        $middleware->web(append: [
            \App\Http\Middleware\LogUserActivity::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        
        // API middleware stack (Sanctum будет добавлен при необходимости)
        // $middleware->api(prepend: [
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // ]);
        
        // Rate limiting для API
        $middleware->throttleApi();
        
        // CORS для API (если нужно)
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Глобальные middleware
        $middleware->append([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
        ]);
        
        // Приоритеты middleware
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Логирование исключений в production
        $exceptions->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
        
        // Кастомные страницы ошибок для production
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RESOURCE_NOT_FOUND',
                        'message' => 'The requested resource was not found.',
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ]
                ], 404);
            }
        });
        
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AUTHENTICATION_REQUIRED',
                        'message' => 'Authentication is required to access this resource.',
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ]
                ], 401);
            }
        });
        
        $exceptions->render(function (Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AUTHORIZATION_FAILED',
                        'message' => 'You do not have permission to access this resource.',
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ]
                ], 403);
            }
        });
        
        $exceptions->render(function (Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'The given data was invalid.',
                        'details' => $e->errors(),
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ]
                ], 422);
            }
        });
        
        // Rate limit exceptions
        $exceptions->render(function (Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Too many requests. Please try again later.',
                        'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ]
                ], 429);
            }
        });
        
        // Maintenance mode exception
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 503 && $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'MAINTENANCE_MODE',
                        'message' => 'The application is currently in maintenance mode.',
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ]
                ], 503);
            }
        });
    })->create();
