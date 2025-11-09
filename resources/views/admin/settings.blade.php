@extends('layouts.app')

@section('title', 'Настройки системы')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Настройки системы</h1>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Назад к панели
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Categories -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Категории документов</h2>
                    <button onclick="showCreateCategoryModal()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Добавить
                    </button>
                </div>
                
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    @if($categories->count() > 0)
                    @foreach($categories as $category)
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                @if($category->description)
                                <div class="text-sm text-gray-500">{{ $category->description }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">{{ $category->documents_count ?? 0 }} документов</span>
                            <button onclick="editCategory({{ $category->id }})" class="text-indigo-600 hover:text-indigo-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="p-6 text-center text-gray-500">
                        Нет категорий
                    </div>
                    @endif
                </div>
            </div>

            <!-- Workflows -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Рабочие процессы</h2>
                    <button onclick="showCreateWorkflowModal()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Добавить
                    </button>
                </div>
                
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    @if($workflows->count() > 0)
                    @foreach($workflows as $workflow)
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $workflow->name }}</div>
                                    @if(!$workflow->is_active)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Неактивен
                                    </span>
                                    @endif
                                </div>
                                @if($workflow->description)
                                <div class="text-sm text-gray-500 mt-1">{{ $workflow->description }}</div>
                                @endif
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $workflow->steps->count() }} этапов
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="viewWorkflow({{ $workflow->id }})" class="text-gray-600 hover:text-gray-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button onclick="editWorkflow({{ $workflow->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="p-6 text-center text-gray-500">
                        Нет рабочих процессов
                    </div>
                    @endif
                </div>
            </div>

            <!-- Roles & Permissions -->
            <div class="bg-white shadow rounded-lg lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Роли и права доступа</h2>
                </div>
                
                <div class="p-6">
                    @if($roles->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($roles as $role)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-900">{{ $role->name }}</h3>
                                <span class="text-xs text-gray-500">{{ $role->permissions->count() }} прав</span>
                            </div>
                            
                            @if($role->permissions->count() > 0)
                            <div class="space-y-1">
                                @foreach($role->permissions->take(5) as $permission)
                                <div class="text-xs text-gray-600">{{ $permission->name }}</div>
                                @endforeach
                                @if($role->permissions->count() > 5)
                                <div class="text-xs text-gray-400">и ещё {{ $role->permissions->count() - 5 }}...</div>
                                @endif
                            </div>
                            @else
                            <div class="text-xs text-gray-400">Нет прав</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        Нет ролей
                    </div>
                    @endif
                </div>
            </div>

            <!-- System Configuration -->
            <div class="bg-white shadow rounded-lg lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Конфигурация системы</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email Settings -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Настройки email</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">SMTP сервер:</span>
                                    <span class="text-gray-900">{{ config('mail.mailers.smtp.host', 'Не настроен') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Порт:</span>
                                    <span class="text-gray-900">{{ config('mail.mailers.smtp.port', 'Не настроен') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Отправитель:</span>
                                    <span class="text-gray-900">{{ config('mail.from.address', 'Не настроен') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Queue Settings -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Настройки очередей</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Драйвер:</span>
                                    <span class="text-gray-900">{{ config('queue.default', 'sync') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Соединение:</span>
                                    <span class="text-gray-900">{{ config('queue.connections.'.config('queue.default').'.connection', 'Не настроено') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- File Storage -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Хранилище файлов</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Диск по умолчанию:</span>
                                    <span class="text-gray-900">{{ config('filesystems.default', 'local') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Публичный диск:</span>
                                    <span class="text-gray-900">{{ config('filesystems.disks.public.driver', 'local') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cache Settings -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Настройки кэша</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Драйвер:</span>
                                    <span class="text-gray-900">{{ config('cache.default', 'file') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">TTL по умолчанию:</span>
                                    <span class="text-gray-900">{{ config('cache.stores.'.config('cache.default').'.ttl', '3600') }} сек</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Техническое обслуживание</h3>
                                <p class="text-sm text-gray-500">Выполнение команд обслуживания системы</p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="clearCache()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Очистить кэш
                                </button>
                                <button onclick="optimizeSystem()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Оптимизация
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showCreateCategoryModal() {
    alert('Функция создания категории будет реализована в следующих версиях');
}

function editCategory(id) {
    alert('Функция редактирования категории будет реализована в следующих версиях');
}

function showCreateWorkflowModal() {
    alert('Функция создания workflow будет реализована в следующих версиях');
}

function viewWorkflow(id) {
    alert('Функция просмотра workflow будет реализована в следующих версиях');
}

function editWorkflow(id) {
    alert('Функция редактирования workflow будет реализована в следующих версиях');
}

function clearCache() {
    if (confirm('Очистить кэш системы?')) {
        alert('Функция очистки кэша будет реализована в следующих версиях');
    }
}

function optimizeSystem() {
    if (confirm('Выполнить оптимизацию системы?')) {
        alert('Функция оптимизации будет реализована в следующих версиях');
    }
}
</script>
@endsection