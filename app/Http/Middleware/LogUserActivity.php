<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Activitylog\Facades\LogBatch;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Логируем только аутентифицированных пользователей
        if (Auth::check() && $this->shouldLog($request)) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Определяем нужно ли логировать запрос
     */
    private function shouldLog(Request $request): bool
    {
        // Не логируем определённые маршруты
        $skipRoutes = [
            'notifications.dropdown',
            'notifications.unread-count',
            '_debugbar',
            'livewire',
            'horizon',
        ];

        $routeName = $request->route()?->getName();
        
        if ($routeName) {
            foreach ($skipRoutes as $skip) {
                if (str_contains($routeName, $skip)) {
                    return false;
                }
            }
        }

        // Не логируем GET запросы к статическим ресурсам
        if ($request->isMethod('GET')) {
            $path = $request->path();
            $staticPaths = ['assets/', 'storage/', 'images/', 'css/', 'js/'];
            
            foreach ($staticPaths as $staticPath) {
                if (str_contains($path, $staticPath)) {
                    return false;
                }
            }
        }

        // Логируем только важные HTTP методы
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) 
               || ($request->isMethod('GET') && $this->isImportantGetRequest($request));
    }

    /**
     * Проверяем является ли GET запрос важным для логирования
     */
    private function isImportantGetRequest(Request $request): bool
    {
        $importantRoutes = [
            'documents.show',
            'tasks.show', 
            'admin.',
            'reports.',
        ];

        $routeName = $request->route()?->getName();
        
        if ($routeName) {
            foreach ($importantRoutes as $route) {
                if (str_contains($routeName, $route)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Логируем активность пользователя
     */
    private function logActivity(Request $request, Response $response): void
    {
        try {
            $user = Auth::user();
            $route = $request->route();
            $routeName = $route?->getName();
            $method = $request->method();
            $path = $request->path();
            $ip = $request->ip();
            $userAgent = $request->userAgent();

            // Определяем тип действия
            $actionType = $this->determineActionType($method, $routeName, $request);
            $description = $this->generateDescription($actionType, $routeName, $request);

            // Собираем дополнительные свойства
            $properties = [
                'method' => $method,
                'path' => $path,
                'route_name' => $routeName,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'status_code' => $response->getStatusCode(),
                'action_type' => $actionType,
            ];

            // Добавляем параметры для важных действий
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $properties['request_data'] = $this->sanitizeRequestData($request->all());
            }

            // Добавляем параметры маршрута
            if ($route) {
                $properties['route_parameters'] = $route->parameters();
            }

            // Логируем активность
            activity('user_activity')
                ->causedBy($user)
                ->withProperties($properties)
                ->log($description);

        } catch (\Exception $e) {
            // Тихо игнорируем ошибки логирования чтобы не нарушить работу приложения
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Определяем тип действия
     */
    private function determineActionType(string $method, ?string $routeName, Request $request): string
    {
        if (!$routeName) {
            return 'unknown';
        }

        // Административные действия
        if (str_contains($routeName, 'admin.')) {
            return 'admin';
        }

        // Действия с документами
        if (str_contains($routeName, 'documents.')) {
            return match($method) {
                'POST' => 'document_create',
                'PUT', 'PATCH' => 'document_update', 
                'DELETE' => 'document_delete',
                'GET' => 'document_view',
                default => 'document_action'
            };
        }

        // Действия с задачами
        if (str_contains($routeName, 'tasks.')) {
            return match(true) {
                str_contains($routeName, 'start') => 'task_start',
                str_contains($routeName, 'complete') => 'task_complete',
                str_contains($routeName, 'cancel') => 'task_cancel',
                $method === 'GET' => 'task_view',
                default => 'task_action'
            };
        }

        // Workflow действия
        if (str_contains($routeName, 'workflow.')) {
            return match(true) {
                str_contains($routeName, 'transition') => 'workflow_transition',
                str_contains($routeName, 'start') => 'workflow_start',
                default => 'workflow_action'
            };
        }

        // Аутентификация
        if (str_contains($routeName, 'login')) {
            return 'auth_login';
        }
        if (str_contains($routeName, 'logout')) {
            return 'auth_logout';
        }

        // Управление пользователями и отделами
        if (str_contains($routeName, 'departments.')) {
            return 'department_management';
        }

        return 'general';
    }

    /**
     * Генерируем описание действия
     */
    private function generateDescription(string $actionType, ?string $routeName, Request $request): string
    {
        return match($actionType) {
            'document_create' => 'Создал новый документ',
            'document_update' => 'Обновил документ',
            'document_delete' => 'Удалил документ', 
            'document_view' => 'Просмотрел документ',
            'task_start' => 'Начал выполнение задачи',
            'task_complete' => 'Завершил задачу',
            'task_cancel' => 'Отменил задачу',
            'task_view' => 'Просмотрел задачу',
            'workflow_transition' => 'Выполнил переход workflow',
            'workflow_start' => 'Запустил workflow',
            'auth_login' => 'Вошёл в систему',
            'auth_logout' => 'Вышел из системы',
            'admin' => 'Выполнил административное действие',
            'department_management' => 'Управление отделами',
            default => 'Выполнил действие: ' . ($routeName ?? $request->path())
        };
    }

    /**
     * Очищаем данные запроса от конфиденциальной информации
     */
    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 
            'password_confirmation', 
            '_token',
            '_method',
            'current_password',
            'new_password'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[СКРЫТО]';
            }
        }

        // Ограничиваем размер данных
        $serialized = json_encode($data);
        if (strlen($serialized) > 2000) {
            return ['_note' => 'Данные слишком большие для сохранения'];
        }

        return $data;
    }
}
