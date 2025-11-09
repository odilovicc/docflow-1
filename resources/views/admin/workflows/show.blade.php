@extends('layouts.app')

@section('title', 'Просмотр рабочего процесса: ' . $workflow->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.workflows.index') }}" 
                   class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                    <div class="flex items-center mt-2 space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $workflow->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $workflow->is_active ? 'Активен' : 'Неактивен' }}
                        </span>
                        @if($workflow->category)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $workflow->category->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500">Все категории</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <form action="{{ route('admin.workflows.toggle-active', $workflow) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        {{ $workflow->is_active ? 'Деактивировать' : 'Активировать' }}
                    </button>
                </form>
                <a href="{{ route('admin.workflows.edit', $workflow) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Редактировать
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="ml-3 text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Основная информация -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Детали процесса -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Информация о процессе</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Название</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workflow->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Категория</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($workflow->category)
                                    {{ $workflow->category->name }}
                                @else
                                    <span class="text-gray-400">Все категории</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Начальное состояние</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $workflow->initial_state }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Количество состояний</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ count($workflow->states) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата создания</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workflow->created_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Последнее изменение</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workflow->updated_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        @if($workflow->description)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Описание</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $workflow->description }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Состояния -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Состояния процесса</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-3">
                        @foreach($workflow->states as $state)
                            <div class="flex items-center px-3 py-2 rounded-lg border 
                                {{ $state === $workflow->initial_state 
                                    ? 'bg-green-50 border-green-200 text-green-800' 
                                    : 'bg-gray-50 border-gray-200 text-gray-700' }}">
                                @if($state === $workflow->initial_state)
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                <span class="font-medium">{{ $state }}</span>
                                @if(isset($documentStats[$state]))
                                    <span class="ml-2 px-2 py-0.5 text-xs bg-white rounded-full">
                                        {{ $documentStats[$state] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Переходы -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Переходы между состояниями</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($workflow->transitions as $transition)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">{{ $transition['name'] }}</h3>
                                        <div class="mt-1 flex items-center space-x-2 text-sm text-gray-500">
                                            <span>Из:</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($transition['from'] as $fromState)
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">
                                                        {{ $fromState }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            <span>→</span>
                                            <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">
                                                {{ $transition['to'] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="space-y-6">
            <!-- Статистика -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Статистика использования</h2>
                </div>
                <div class="p-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-indigo-600">
                            {{ $workflow->documents->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Документов использует этот процесс</div>
                    </div>
                    
                    @if($documentStats)
                        <div class="mt-6 space-y-3">
                            @foreach($documentStats as $status => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">{{ $status }}:</span>
                                    <span class="font-medium">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Последние документы -->
            @if($workflow->documents->count() > 0)
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Последние документы</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($workflow->documents->take(5) as $document)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <a href="{{ route('documents.show', $document) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                {{ $document->title }}
                                            </a>
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $document->category->name }} • {{ $document->author->name }}
                                        </p>
                                    </div>
                                    <div class="ml-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            @if($document->status === 'completed') bg-green-100 text-green-800
                                            @elseif($document->status === 'in_progress') bg-yellow-100 text-yellow-800
                                            @elseif($document->status === 'approved') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $document->status }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($workflow->documents->count() > 5)
                            <div class="mt-4 text-center">
                                <a href="{{ route('documents.index', ['workflow' => $workflow->id]) }}" 
                                   class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Показать все документы ({{ $workflow->documents->count() }})
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Действия -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Действия</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.workflows.edit', $workflow) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Редактировать процесс
                    </a>
                    
                    <form action="{{ route('admin.workflows.toggle-active', $workflow) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            @if($workflow->is_active)
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Деактивировать
                            @else
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Активировать
                            @endif
                        </button>
                    </form>

                    @if($workflow->documents->count() == 0)
                        <form action="{{ route('admin.workflows.destroy', $workflow) }}" method="POST" 
                              onsubmit="return confirm('Вы уверены, что хотите удалить этот рабочий процесс? Это действие нельзя отменить.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Удалить процесс
                            </button>
                        </form>
                    @else
                        <div class="text-center">
                            <p class="text-sm text-gray-500">
                                Невозможно удалить процесс, который используется в документах
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection