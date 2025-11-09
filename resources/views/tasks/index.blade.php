@extends('layouts.app')

@section('title', 'Задачи')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Задачи</h1>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('tasks.my') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Мои задачи
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-48">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                    <select name="status" id="status" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Все статусы</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Ожидает</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В процессе</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Завершена</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Отменена</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="overdue" id="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} 
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="overdue" class="ml-2 text-sm text-gray-700">Только просроченные</label>
                </div>

                @if(auth()->user()->hasRole(['Admin', 'DepartmentHead']))
                <div class="flex items-center">
                    <input type="checkbox" name="all_tasks" id="all_tasks" value="1" {{ request('all_tasks') ? 'checked' : '' }} 
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="all_tasks" class="ml-2 text-sm text-gray-700">Все задачи департамента</label>
                </div>
                @endif

                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Применить фильтры
                </button>

                <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Сбросить
                </a>
            </form>
        </div>

        <!-- Tasks List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($tasks->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Задача</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Документ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Назначена</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Срок</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Приоритет</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                    @if($task->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($task->description, 100) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('documents.show', $task->document) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    {{ $task->document->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $task->assignedToUser->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($task->due_date)
                                <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                    {{ $task->due_date->format('d.m.Y H:i') }}
                                </span>
                                @else
                                <span class="text-gray-400">Не указан</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {!! $task->status_badge !!}
                            </td>
                            <td class="px-6 py-4">
                                {!! $task->priority_badge !!}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Подробнее</a>
                                
                                @if($task->isPending() && $task->assigned_to === auth()->id())
                                <form method="POST" action="{{ route('tasks.start', $task) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900">Начать</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $tasks->appends(request()->query())->links() }}
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет задач</h3>
                <p class="mt-1 text-sm text-gray-500">Задачи с выбранными критериями не найдены.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection