<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="DocsFlow - Система управления документооборотом">
        <meta name="keywords" content="документооборот, workflow, задачи, уведомления">
        <meta name="author" content="{{ config('app.name', 'DocsFlow') }}">
        
        <!-- Security Headers -->
        <meta http-equiv="X-Content-Type-Options" content="nosniff">
        <meta http-equiv="X-Frame-Options" content="DENY">
        <meta http-equiv="X-XSS-Protection" content="1; mode=block">
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <title>{{ config('app.name', 'DocsFlow') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Additional Styles -->
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <strong class="font-bold">Успешно!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <strong class="font-bold">Ошибка!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <strong class="font-bold">Внимание!</strong>
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <strong class="font-bold">Информация:</strong>
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        
        <!-- Additional Scripts -->
        @stack('scripts')
        
        <!-- Auto-hide flash messages -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-hide flash messages after 5 seconds
                const alerts = document.querySelectorAll('[role="alert"]');
                alerts.forEach(function(alert) {
                    setTimeout(function() {
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 300);
                    }, 5000);
                    
                    // Add close button functionality
                    const closeBtn = document.createElement('button');
                    closeBtn.innerHTML = '&times;';
                    closeBtn.className = 'absolute top-0 bottom-0 right-0 px-4 py-3';
                    closeBtn.onclick = function() {
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 300);
                    };
                    alert.appendChild(closeBtn);
                });
            });
        </script>
    </body>
</html>