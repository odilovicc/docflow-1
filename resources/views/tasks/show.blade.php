@extends('layouts.app')

@section('title', 'Задача: ' . $task->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $task->title }}</h1>
                    <div class="mt-2 flex items-center space-x-4">
                        {!! $task->status_badge !!}
                        {!! $task->priority_badge !!}
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Назад к списку
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Task Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Описание</h2>
                    @if($task->description)
                    <p class="text-gray-700 whitespace-pre-line">{{ $task->description }}</p>
                    @else
                    <p class="text-gray-500 italic">Описание не указано</p>
                    @endif
                </div>

                <!-- Related Document -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Связанный документ</h2>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    <a href="{{ route('documents.show', $task->document) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $task->document->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $task->document->description ?? 'Описание не указано' }}</p>
                                
                                @if($task->workflowStep)
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Этап: {{ $task->workflowStep->name }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                @if($task->assigned_to === auth()->id())
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Действия</h2>
                    
                    @if($task->isPending())
                    <!-- Start Task -->
                    <form method="POST" action="{{ route('tasks.start', $task) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m2-10v18a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2h8l4 4z"></path>
                            </svg>
                            Начать выполнение
                        </button>
                    </form>
                    @endif

                    @if($task->isInProgress())
                    <!-- Complete Task -->
                    <form method="POST" action="{{ route('tasks.complete', $task) }}" class="mb-4">
                        @csrf
                        <div class="mb-4">
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Комментарий к выполнению</label>
                            <textarea name="comment" id="comment" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Опишите что было сделано..."></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Завершить задачу
                        </button>
                    </form>
                    @endif
                </div>
                @endif

                @if($task->comment)
                <!-- Comments -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Комментарий</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 whitespace-pre-line">{{ $task->comment }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Task Info Sidebar -->
            <div class="space-y-6">
                <!-- Task Info -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Информация о задаче</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Назначена</dt>
                            <dd class="text-sm text-gray-900">{{ $task->assignedToUser->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Создана</dt>
                            <dd class="text-sm text-gray-900">{{ $task->assignedByUser->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата создания</dt>
                            <dd class="text-sm text-gray-900">{{ $task->created_at->format('d.m.Y H:i') }}</dd>
                        </div>

                        @if($task->due_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Срок выполнения</dt>
                            <dd class="text-sm {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                {{ $task->due_date->format('d.m.Y H:i') }}
                                @if($task->isOverdue())
                                <span class="block text-xs text-red-500">Просрочена</span>
                                @endif
                            </dd>
                        </div>
                        @endif

                        @if($task->started_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Начата</dt>
                            <dd class="text-sm text-gray-900">{{ $task->started_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        @endif

                        @if($task->finished_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Завершена</dt>
                            <dd class="text-sm text-gray-900">{{ $task->finished_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Cancel Task -->
                @can('delete', $task)
                @if(!$task->isCompleted() && $task->status !== 'cancelled')
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Управление задачей</h2>
                    
                    <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Отменить задачу
                    </button>
                </div>
                @endif
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
@can('delete', $task)
<div id="cancel-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Отменить задачу</h3>
            
            <form method="POST" action="{{ route('tasks.cancel', $task) }}" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Причина отмены</label>
                    <textarea name="reason" id="reason" rows="3" required 
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" 
                              placeholder="Укажите причину отмены задачи..."></textarea>
                </div>
                
                <div class="flex items-center justify-center space-x-4">
                    <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400">
                        Отмена
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700">
                        Отменить задачу
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection