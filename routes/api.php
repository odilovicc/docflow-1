<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Публичные API маршруты
Route::prefix('v1')->group(function () {
    // Health check для API
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'version' => config('app.version', '1.0.0'),
                'timestamp' => now()->toISOString(),
                'environment' => app()->environment(),
                'database' => 'connected', // Можно добавить проверку БД
            ]
        ]);
    });
    
    // Базовая информация о системе
    Route::get('/info', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'name' => config('app.name', 'DocsFlow'),
                'version' => config('app.version', '1.0.0'),
                'api_version' => 'v1',
                'features' => [
                    'documents' => true,
                    'tasks' => true,
                    'workflows' => true,
                    'search' => true,
                    'notifications' => true,
                ],
                'endpoints' => [
                    'health' => '/api/v1/health',
                    'info' => '/api/v1/info',
                ]
            ]
        ]);
    });
});

// API маршруты будут добавлены при необходимости внешних интеграций
// Для базового функционала используется web интерфейс

/*
// Пример защищенных API маршрутов (требует Laravel Sanctum)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Основные API endpoints для внешних интеграций
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Документы API
    Route::get('/documents', function () {
        return response()->json([
            'success' => true,
            'message' => 'API endpoints будут добавлены при необходимости'
        ]);
    });
});
*/

// Fallback route для неизвестных API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'ENDPOINT_NOT_FOUND',
            'message' => 'The requested API endpoint was not found.',
        ],
        'meta' => [
            'timestamp' => now()->toISOString(),
        ]
    ], 404);
});