@extends('layouts.app')

@section('title', 'Категории документов')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Категории документов</h1>
                    <div class="flex space-x-3">
                        @can('create', App\Models\Category::class)
                            <a href="{{ route('categories.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Создать категорию
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <form method="GET" action="{{ route('categories.index') }}" class="flex gap-2">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Поиск по названию или описанию..." 
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
                            >
                                Найти
                            </button>
                            @if(request('search'))
                                <a 
                                    href="{{ route('categories.index') }}" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
                                >
                                    Сбросить
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if($categories->count() > 0)
                    <!-- Categories Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($categories as $category)
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                                <!-- Category Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            {{ $category->name }}
                                        </h3>
                                        @if($category->description)
                                            <p class="text-gray-600 text-sm line-clamp-2">
                                                {{ $category->description }}
                                            </p>
                                        @endif
                                    </div>
                                    
                                    <!-- Actions Dropdown -->
                                    <div class="relative ml-4" x-data="{ open: false }">
                                        <button 
                                            @click="open = !open" 
                                            class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full p-1"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                        
                                        <div 
                                            x-show="open" 
                                            @click.away="open = false"
                                            x-transition
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                                        >
                                            <div class="py-1">
                                                <a 
                                                    href="{{ route('categories.show', $category) }}" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                >
                                                    Просмотр
                                                </a>
                                                @can('update', $category)
                                                    <a 
                                                        href="{{ route('categories.edit', $category) }}" 
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Редактировать
                                                    </a>
                                                @endcan
                                                @can('delete', $category)
                                                    <form 
                                                        method="POST" 
                                                        action="{{ route('categories.destroy', $category) }}" 
                                                        onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию? Все документы в этой категории будут перемещены в категорию по умолчанию.')"
                                                        class="inline"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button 
                                                            type="submit" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                                        >
                                                            Удалить
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistics -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Документов:</span>
                                        <span class="font-medium text-gray-900">{{ $category->documents_count ?? 0 }}</span>
                                    </div>
                                    
                                    @if($category->created_at)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Создана:</span>
                                            <span class="text-gray-900">{{ $category->created_at->format('d.m.Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Quick Actions -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex space-x-2">
                                        <a 
                                            href="{{ route('documents.index', ['category' => $category->id]) }}" 
                                            class="flex-1 text-center px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition duration-200"
                                        >
                                            Просмотр документов
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($categories->hasPages())
                        <div class="mt-8">
                            {{ $categories->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            @if(request('search'))
                                Категории не найдены
                            @else
                                Нет категорий
                            @endif
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request('search'))
                                Попробуйте изменить поисковый запрос.
                            @else
                                Создайте первую категорию для организации документов.
                            @endif
                        </p>
                        @if(!request('search'))
                            @can('create', App\Models\Category::class)
                                <div class="mt-6">
                                    <a 
                                        href="{{ route('categories.create') }}" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Создать категорию
                                    </a>
                                </div>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection