<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Маршруты и их время кеширования (в секундах)
     */
    private array $cacheableRoutes = [
        'documents.index' => 300,    // 5 минут
        'admin.dashboard' => 180,    // 3 минуты
        'admin.reports' => 600,      // 10 минут
        'departments.index' => 1800, // 30 минут
        'categories.index' => 3600,  // 1 час
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $duration = null): Response
    {
        // Кешируем только GET запросы
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Не кешируем для неаутентифицированных пользователей
        if (!auth()->check()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        
        // Определяем время кеширования
        $ttl = $duration ? (int) $duration : ($this->cacheableRoutes[$routeName] ?? null);
        
        if (!$ttl) {
            return $next($request);
        }

        // Генерируем ключ кеша
        $cacheKey = $this->generateCacheKey($request);

        // Пытаемся получить из кеша
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers'])
                ->header('X-Cache', 'HIT')
                ->header('X-Cache-Key', $cacheKey);
        }

        // Получаем ответ
        $response = $next($request);

        // Кешируем только успешные ответы
        if ($response->getStatusCode() === 200) {
            $cacheData = [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $this->getHeaders($response),
            ];

            Cache::put($cacheKey, $cacheData, $ttl);

            $response->header('X-Cache', 'MISS');
            $response->header('X-Cache-Key', $cacheKey);
            $response->header('X-Cache-TTL', $ttl);
        }

        return $response;
    }

    /**
     * Генерация ключа кеша
     */
    private function generateCacheKey(Request $request): string
    {
        $userId = auth()->id();
        $route = $request->route()?->getName() ?? 'unknown';
        $uri = $request->getRequestUri();
        $queryString = $request->getQueryString();

        $parts = [
            'response',
            $route,
            $userId,
            md5($uri . '?' . $queryString)
        ];

        return implode('.', array_filter($parts));
    }

    /**
     * Получение кешируемых заголовков
     */
    private function getHeaders(Response $response): array
    {
        $headers = [];
        
        $cacheableHeaders = [
            'Content-Type',
            'Content-Language',
            'Last-Modified',
            'ETag',
        ];

        foreach ($cacheableHeaders as $header) {
            if ($response->headers->has($header)) {
                $headers[$header] = $response->headers->get($header);
            }
        }

        return $headers;
    }
}
