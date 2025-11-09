@extends('layouts.app')

@section('title', 'Поиск')
@section('description', 'Поиск по документам и задачам в системе DocsFlow')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Search Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Поиск</h1>
                    <p class="text-gray-600">Найдите нужные документы и задачи</p>
                </div>

                <!-- Search Form -->
                <form method="GET" action="{{ route('search.index') }}" class="mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="q" 
                                    value="{{ request('q') }}" 
                                    placeholder="Введите поисковый запрос..."
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    id="search-input"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search Type Filter -->
                        <select name="type" class="border border-gray-300 rounded-lg px-3 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Все типы</option>
                            <option value="documents" {{ request('type') == 'documents' ? 'selected' : '' }}>Документы</option>
                            <option value="tasks" {{ request('type') == 'tasks' ? 'selected' : '' }}>Задачи</option>
                        </select>
                        
                        <!-- Search Button -->
                        <button 
                            type="submit" 
                            class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                        >
                            Найти
                        </button>
                        
                        <!-- Advanced Search Link -->
                        <a 
                            href="{{ route('search.advanced') }}" 
                            class="text-indigo-600 hover:text-indigo-800 px-3 py-3 text-sm font-medium"
                        >
                            Расширенный поиск
                        </a>
                    </div>
                </form>

                @if(request('q'))
                    <!-- Search Results -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">
                                Результаты поиска для "{{ request('q') }}"
                            </h2>
                            <span class="text-sm text-gray-500">
                                Найдено: <span class="font-medium">{{ $results['total'] ?? 0 }}</span> результатов
                            </span>
                        </div>

                        @if(isset($results['documents']) && count($results['documents']) > 0)
                            <!-- Documents Results -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Документы ({{ count($results['documents']) }})
                                </h3>
                                <div class="space-y-4">
                                    @foreach($results['documents'] as $document)
                                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-200">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900 mb-1">
                                                        <a href="{{ route('documents.show', $document) }}" class="hover:text-indigo-600">
                                                            {{ $document->title }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-gray-600 mb-2">
                                                        {{ Str::limit($document->content, 200) }}
                                                    </p>
                                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                        <span class="flex items-center">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ $document->created_at->format('d.m.Y') }}
                                                        </span>
                                                        @if($document->department)
                                                            <span>{{ $document->department->name }}</span>
                                                        @endif
                                                        @if($document->category)
                                                            <span>{{ $document->category->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center ml-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($document->status == 'approved') bg-green-100 text-green-800
                                                        @elseif($document->status == 'pending') bg-yellow-100 text-yellow-800
                                                        @elseif($document->status == 'rejected') bg-red-100 text-red-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($document->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(isset($results['tasks']) && count($results['tasks']) > 0)
                            <!-- Tasks Results -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    Задачи ({{ count($results['tasks']) }})
                                </h3>
                                <div class="space-y-4">
                                    @foreach($results['tasks'] as $task)
                                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-200">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900 mb-1">
                                                        <a href="{{ route('tasks.show', $task) }}" class="hover:text-indigo-600">
                                                            {{ $task->title }}
                                                        </a>
                                                    </h4>
                                                    @if($task->description)
                                                        <p class="text-sm text-gray-600 mb-2">
                                                            {{ Str::limit($task->description, 200) }}
                                                        </p>
                                                    @endif
                                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                        <span class="flex items-center">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ $task->created_at->format('d.m.Y') }}
                                                        </span>
                                                        @if($task->assignee)
                                                            <span>Исполнитель: {{ $task->assignee->name }}</span>
                                                        @endif
                                                        @if($task->due_date)
                                                            <span>До: {{ $task->due_date->format('d.m.Y') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center ml-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($task->status == 'completed') bg-green-100 text-green-800
                                                        @elseif($task->status == 'in_progress') bg-blue-100 text-blue-800
                                                        @elseif($task->status == 'pending') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($task->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if((!isset($results['documents']) || count($results['documents']) == 0) && 
                            (!isset($results['tasks']) || count($results['tasks']) == 0))
                            <!-- No Results -->
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Ничего не найдено</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Попробуйте изменить поисковый запрос или использовать расширенный поиск.
                                </p>
                                <div class="mt-6">
                                    <a 
                                        href="{{ route('search.advanced') }}" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Расширенный поиск
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Initial State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Начните поиск</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Введите ключевые слова для поиска документов и задач в системе.
                        </p>
                        <div class="mt-6">
                            <a 
                                href="{{ route('search.advanced') }}" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Расширенный поиск
                            </a>
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
// Auto-complete functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    let timeout = null;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value;
        
        if (query.length < 2) {
            return;
        }
        
        timeout = setTimeout(function() {
            // Здесь можно добавить AJAX запрос для автодополнения
            // fetch(`{{ route('search.api.autocomplete') }}?q=${query}`)
            //     .then(response => response.json())
            //     .then(data => {
            //         // Обработка результатов автодополнения
            //     });
        }, 300);
    });
});
</script>
@endsection