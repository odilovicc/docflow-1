@extends('layouts.app')

@section('title', 'Системные логи')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Системные логи</h1>
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

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-48">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="Описание или тип лога..." 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="min-w-48">
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Дата от</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="min-w-48">
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Дата до</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Применить фильтры
                </button>

                <a href="{{ route('admin.logs') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Сбросить
                </a>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Объект</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Свойства</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>{{ $log->created_at->format('d.m.Y H:i:s') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($log->causer)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-600">
                                                {{ strtoupper(substr($log->causer->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->causer->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->causer->email }}</div>
                                    </div>
                                </div>
                                @else
                                <span class="text-gray-400">Система</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $log->description }}</div>
                                @if($log->log_name)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $log->log_name }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($log->subject)
                                <div>
                                    <span class="font-medium">{{ class_basename($log->subject_type) }}</span>
                                    @if($log->subject_id)
                                    <span class="text-gray-400">#{{ $log->subject_id }}</span>
                                    @endif
                                </div>
                                @if(method_exists($log->subject, 'title') && $log->subject->title)
                                <div class="text-xs text-gray-400 truncate max-w-48">{{ $log->subject->title }}</div>
                                @elseif(method_exists($log->subject, 'name') && $log->subject->name)
                                <div class="text-xs text-gray-400 truncate max-w-48">{{ $log->subject->name }}</div>
                                @endif
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($log->properties && $log->properties->count() > 0)
                                <button type="button" onclick="toggleProperties('{{ $log->id }}')" 
                                        class="text-indigo-600 hover:text-indigo-900">
                                    Показать ({{ $log->properties->count() }})
                                </button>
                                <div id="properties-{{ $log->id }}" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs">
                                    <pre class="whitespace-pre-wrap">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->appends(request()->query())->links() }}
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Логи не найдены</h3>
                <p class="mt-1 text-sm text-gray-500">Попробуйте изменить критерии поиска.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleProperties(logId) {
    const element = document.getElementById('properties-' + logId);
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}
</script>
@endsection