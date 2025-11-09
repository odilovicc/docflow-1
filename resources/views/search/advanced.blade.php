@extends('layouts.app')

@section('title', 'Расширенный поиск')
@section('description', 'Расширенные возможности поиска по документам и задачам в системе DocsFlow')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Расширенный поиск</h1>
                    <p class="text-gray-600">Используйте дополнительные фильтры для более точного поиска</p>
                </div>

                <!-- Advanced Search Form -->
                <form method="POST" action="{{ route('search.advanced') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Basic Search -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Основные параметры</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Search Query -->
                            <div>
                                <label for="query" class="block text-sm font-medium text-gray-700 mb-1">
                                    Поисковый запрос
                                </label>
                                <input 
                                    type="text" 
                                    id="query" 
                                    name="query" 
                                    value="{{ old('query') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Введите ключевые слова..."
                                >
                            </div>
                            
                            <!-- Search Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Тип поиска
                                </label>
                                <select 
                                    id="type" 
                                    name="type" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="all" {{ old('type') == 'all' ? 'selected' : '' }}>Все типы</option>
                                    <option value="documents" {{ old('type') == 'documents' ? 'selected' : '' }}>Только документы</option>
                                    <option value="tasks" {{ old('type') == 'tasks' ? 'selected' : '' }}>Только задачи</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Document Filters -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Фильтры по документам</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Department -->
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Отдел
                                </label>
                                <select 
                                    id="department_id" 
                                    name="department_id" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Все отделы</option>
                                    <!-- Здесь будут загружены отделы из контроллера -->
                                </select>
                            </div>
                            
                            <!-- Category -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Категория
                                </label>
                                <select 
                                    id="category_id" 
                                    name="category_id" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Все категории</option>
                                    <!-- Здесь будут загружены категории из контроллера -->
                                </select>
                            </div>
                            
                            <!-- Document Status -->
                            <div>
                                <label for="document_status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Статус документа
                                </label>
                                <select 
                                    id="document_status" 
                                    name="document_status" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Любой статус</option>
                                    <option value="draft" {{ old('document_status') == 'draft' ? 'selected' : '' }}>Черновик</option>
                                    <option value="pending" {{ old('document_status') == 'pending' ? 'selected' : '' }}>На рассмотрении</option>
                                    <option value="approved" {{ old('document_status') == 'approved' ? 'selected' : '' }}>Утвержден</option>
                                    <option value="rejected" {{ old('document_status') == 'rejected' ? 'selected' : '' }}>Отклонен</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Task Filters -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Фильтры по задачам</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Assignee -->
                            <div>
                                <label for="assignee_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Исполнитель
                                </label>
                                <select 
                                    id="assignee_id" 
                                    name="assignee_id" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Любой исполнитель</option>
                                    <!-- Здесь будут загружены пользователи из контроллера -->
                                </select>
                            </div>
                            
                            <!-- Task Status -->
                            <div>
                                <label for="task_status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Статус задачи
                                </label>
                                <select 
                                    id="task_status" 
                                    name="task_status" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Любой статус</option>
                                    <option value="pending" {{ old('task_status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                                    <option value="in_progress" {{ old('task_status') == 'in_progress' ? 'selected' : '' }}>В работе</option>
                                    <option value="completed" {{ old('task_status') == 'completed' ? 'selected' : '' }}>Выполнена</option>
                                    <option value="cancelled" {{ old('task_status') == 'cancelled' ? 'selected' : '' }}>Отменена</option>
                                </select>
                            </div>
                            
                            <!-- Priority -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                    Приоритет
                                </label>
                                <select 
                                    id="priority" 
                                    name="priority" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Любой приоритет</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Низкий</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Средний</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Высокий</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Срочный</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Период</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- From Date -->
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                                    Дата от
                                </label>
                                <input 
                                    type="date" 
                                    id="date_from" 
                                    name="date_from" 
                                    value="{{ old('date_from') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                            
                            <!-- To Date -->
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                                    Дата до
                                </label>
                                <input 
                                    type="date" 
                                    id="date_to" 
                                    name="date_to" 
                                    value="{{ old('date_to') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Search Options -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Параметры поиска</h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="search_content" 
                                    value="1" 
                                    {{ old('search_content') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Искать в содержимом документов</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="search_attachments" 
                                    value="1" 
                                    {{ old('search_attachments') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Искать в названиях вложений</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="exact_phrase" 
                                    value="1" 
                                    {{ old('exact_phrase') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Точное совпадение фразы</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6">
                        <a 
                            href="{{ route('search.index') }}" 
                            class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium"
                        >
                            ← Обычный поиск
                        </a>
                        
                        <div class="flex space-x-4">
                            <button 
                                type="button" 
                                onclick="resetForm()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                            >
                                Очистить
                            </button>
                            
                            <button 
                                type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                            >
                                Найти
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Search Results (if any) -->
                @if(isset($results) && $results)
                    <div class="mt-12 border-t border-gray-200 pt-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">
                            Результаты поиска
                        </h2>
                        
                        <!-- Results content would be similar to search.index -->
                        <div class="text-center py-8">
                            <p class="text-gray-500">Результаты будут отображены здесь</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function resetForm() {
    document.querySelector('form').reset();
}

// Auto-update date range
document.addEventListener('DOMContentLoaded', function() {
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    dateFrom.addEventListener('change', function() {
        if (dateTo.value && this.value > dateTo.value) {
            dateTo.value = this.value;
        }
    });
    
    dateTo.addEventListener('change', function() {
        if (dateFrom.value && this.value < dateFrom.value) {
            dateFrom.value = this.value;
        }
    });
});
</script>
@endsection