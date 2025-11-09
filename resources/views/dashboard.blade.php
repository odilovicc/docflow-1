@extends('layouts.app')

@section('title', 'Панель управления')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Панель управления</h1>
            <p class="text-gray-600 mt-2">
                Добро пожаловать, {{ auth()->user()->name }}! 
                {{ auth()->user()->position ? '• ' . auth()->user()->position : '' }}
                {{ auth()->user()->department ? '• ' . auth()->user()->department->name : '' }}
            </p>
        </div>

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Мои документы -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ $myDocuments }}</div>
                                <div class="text-sm text-gray-600">Мои документы</div>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Документы отдела -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ $departmentDocuments }}</div>
                                <div class="text-sm text-gray-600">Документы отдела</div>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Мои задачи -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-yellow-600">{{ $myTasks }}</div>
                                <div class="text-sm text-gray-600">Мои задачи</div>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Просроченные задачи -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-red-600">{{ $overdueTasks }}</div>
                                <div class="text-sm text-gray-600">Просрочено</div>
                            </div>
                            <div class="p-3 bg-red-100 rounded-full">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Админская статистика -->
            @if($adminStats)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Общая статистика (Администратор)</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600">{{ $adminStats['total_documents'] }}</div>
                        <div class="text-sm text-indigo-700">Всего документов</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $adminStats['total_tasks'] }}</div>
                        <div class="text-sm text-purple-700">Всего задач</div>
                    </div>
                    <div class="bg-pink-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-pink-600">{{ $adminStats['total_departments'] }}</div>
                        <div class="text-sm text-pink-700">Всего отделов</div>
                    </div>
                    <div class="bg-teal-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-teal-600">{{ $adminStats['active_workflows'] }}</div>
                        <div class="text-sm text-teal-700">Активных процессов</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Быстрые действия -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Быстрые действия -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Быстрые действия</h3>
                        <div class="space-y-3">
                            <a href="{{ route('documents.create') }}" 
                               class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                                <div class="p-2 bg-blue-500 text-white rounded-lg mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Создать документ</div>
                                    <div class="text-sm text-gray-600">Добавить новый документ в систему</div>
                                </div>
                            </a>
                            
                            <a href="{{ route('tasks.my') }}" 
                               class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                                <div class="p-2 bg-green-500 text-white rounded-lg mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Мои задачи</div>
                                    <div class="text-sm text-gray-600">Просмотреть назначенные задачи</div>
                                </div>
                            </a>
                            
                            @can('manage', App\Models\Department::class)
                            <a href="{{ route('departments.index') }}" 
                               class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200">
                                <div class="p-2 bg-purple-500 text-white rounded-lg mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Управление отделами</div>
                                    <div class="text-sm text-gray-600">Настройка структуры организации</div>
                                </div>
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>

                <!-- Оповещения о задачах -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Уведомления о задачах</h3>
                        @if($pendingTasks > 0 || $overdueTasks > 0)
                            <div class="space-y-3">
                                @if($pendingTasks > 0)
                                <div class="flex items-center p-3 bg-yellow-50 rounded-lg">
                                    <div class="p-2 bg-yellow-500 text-white rounded-lg mr-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $pendingTasks }} ожидающих задач</div>
                                        <div class="text-sm text-gray-600">Требуют вашего внимания</div>
                                    </div>
                                </div>
                                @endif
                                
                                @if($overdueTasks > 0)
                                <div class="flex items-center p-3 bg-red-50 rounded-lg">
                                    <div class="p-2 bg-red-500 text-white rounded-lg mr-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $overdueTasks }} просроченных задач</div>
                                        <div class="text-sm text-gray-600">Срочно требуют выполнения</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-gray-600 mt-2">Все задачи выполнены!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Недавние документы и задачи -->
                            <div class="ml-3 text-sm text-gray-600">
                                Документов отдела
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $pendingDocuments }}</div>
                            <div class="ml-3 text-sm text-gray-600">
                                В процессе
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Быстрые действия</h4>
                    <div class="flex flex-wrap gap-3">
                        @can('document.create')
                            <a href="{{ route('documents.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Создать документ
                            </a>
                        @endcan
                        
                        <a href="{{ route('documents.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Все документы
                        </a>
                        
                        @can('department.manage')
                            <a href="{{ route('departments.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Управление отделами
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Последние документы -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Последние документы отдела</h4>
                    @if($recentDocuments->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentDocuments as $document)
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <a href="{{ route('documents.show', $document) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                                {{ $document->title }}
                                            </a>
                                            <div class="text-sm text-gray-600">
                                                {{ $document->category->name }} • Автор: {{ $document->author->name }}
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_color }}">
                                            {{ $document->status_label }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Нет последних документов для отображения.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
