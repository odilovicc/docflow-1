@extends('layouts.app')

@section('title', 'Мои задачи')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Мои задачи</h1>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Все задачи
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Ожидающие</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $pendingTasks->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">В работе</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $inProgressTasks->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Просроченные</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $overdueTasks }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Выполнено на неделе</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $completedThisWeek }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pending Tasks -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Ожидающие выполнения</h2>
                </div>
                
                @if($pendingTasks->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($pendingTasks as $task)
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('tasks.show', $task) }}" class="hover:text-indigo-600">
                                        {{ $task->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $task->document->title }}</p>
                                
                                @if($task->due_date)
                                <div class="mt-2 flex items-center text-xs {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $task->due_date->format('d.m.Y H:i') }}
                                    @if($task->isOverdue())
                                    <span class="ml-1 font-medium">(Просрочена)</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                {!! $task->priority_badge !!}
                                <form method="POST" action="{{ route('tasks.start', $task) }}">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Начать
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-8 text-center text-gray-500">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>Нет ожидающих задач</p>
                </div>
                @endif
            </div>

            <!-- In Progress Tasks -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">В процессе выполнения</h2>
                </div>
                
                @if($inProgressTasks->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($inProgressTasks as $task)
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('tasks.show', $task) }}" class="hover:text-indigo-600">
                                        {{ $task->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $task->document->title }}</p>
                                
                                @if($task->started_at)
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Начата: {{ $task->started_at->format('d.m.Y H:i') }}
                                </div>
                                @endif
                                
                                @if($task->due_date)
                                <div class="mt-1 flex items-center text-xs {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Срок: {{ $task->due_date->format('d.m.Y H:i') }}
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                {!! $task->priority_badge !!}
                                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Завершить
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-8 text-center text-gray-500">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <p>Нет задач в работе</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection