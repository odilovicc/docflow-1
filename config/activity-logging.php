<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Logging Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for activity logging.
    | You can adjust settings for logging behavior, retention policies,
    | and excluded actions.
    |
    */

    /**
     * Включение/отключение логирования активности
     */
    'enabled' => env('ACTIVITY_LOGGING_ENABLED', true),

    /**
     * Настройки сохранения логов (в днях)
     */
    'retention' => [
        'default' => env('ACTIVITY_LOG_RETENTION_DAYS', 90),
        'critical' => env('ACTIVITY_LOG_CRITICAL_RETENTION_DAYS', 365),
        'auth' => env('ACTIVITY_LOG_AUTH_RETENTION_DAYS', 180),
        'admin' => env('ACTIVITY_LOG_ADMIN_RETENTION_DAYS', 365),
    ],

    /**
     * Настройки веб-логирования
     */
    'web_logging' => [
        'enabled' => env('WEB_ACTIVITY_LOGGING_ENABLED', true),
        
        /**
         * Исключенные маршруты (не логируются)
         */
        'excluded_routes' => [
            'api/health',
            'api/status',
            '_ignition/*',
            'telescope/*',
            'horizon/*',
        ],

        /**
         * Исключенные методы HTTP
         */
        'excluded_methods' => [
            'OPTIONS',
        ],

        /**
         * Исключенные пути
         */
        'excluded_paths' => [
            '/css/*',
            '/js/*',
            '/images/*',
            '/fonts/*',
            '/favicon.ico',
        ],

        /**
         * Максимальный размер данных запроса для логирования (в байтах)
         */
        'max_request_size' => 1024 * 10, // 10KB

        /**
         * Поля запроса, которые нужно скрыть в логах
         */
        'hidden_fields' => [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_token',
            '_token',
            'csrf_token',
        ],
    ],

    /**
     * Настройки логирования моделей
     */
    'model_logging' => [
        'enabled' => env('MODEL_ACTIVITY_LOGGING_ENABLED', true),

        /**
         * События моделей для логирования
         */
        'events' => [
            'created',
            'updated', 
            'deleted',
            'restored',
            'force_deleted',
        ],

        /**
         * Модели, исключенные из логирования
         */
        'excluded_models' => [
            // Исключаем сами логи активности
            'Spatie\Activitylog\Models\Activity',
        ],

        /**
         * Атрибуты моделей, которые нужно скрыть
         */
        'hidden_attributes' => [
            'password',
            'remember_token',
            'api_token',
        ],
    ],

    /**
     * Настройки производительности
     */
    'performance' => [
        /**
         * Использовать очереди для записи логов
         */
        'use_queue' => env('ACTIVITY_LOG_USE_QUEUE', false),

        /**
         * Название очереди для логов
         */
        'queue_name' => env('ACTIVITY_LOG_QUEUE_NAME', 'logs'),

        /**
         * Размер батча для очистки логов
         */
        'cleanup_batch_size' => env('ACTIVITY_LOG_CLEANUP_BATCH_SIZE', 1000),
    ],

    /**
     * Настройки уведомлений об активности
     */
    'notifications' => [
        /**
         * Критические действия, требующие уведомления
         */
        'critical_actions' => [
            'user_deleted',
            'role_changed', 
            'permission_granted',
            'document_deleted',
            'workflow_bypassed',
        ],

        /**
         * Email для уведомлений о критической активности
         */
        'admin_email' => env('ACTIVITY_LOG_ADMIN_EMAIL'),
    ],
];