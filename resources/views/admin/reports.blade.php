@extends('layouts.app')

@section('title', 'Отчёты системы')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Отчёты системы</h1>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Назад к панели
                    </a>
                </div>
            </div>
        </div>

        <!-- Period Filter -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" class="flex items-center space-x-4">
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Период</label>
                    <select name="period" id="period" onchange="this.form.submit()" class="block border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Неделя</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Месяц</option>
                        <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Квартал</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Год</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Document Statistics -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Статистика документов</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $documentStats['created'] }}</div>
                    <div class="text-sm text-gray-500">Создано документов</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $documentStats['completed'] }}</div>
                    <div class="text-sm text-gray-500">Завершено</div>
                </div>
                <div class="text-center">
                    @php
                        $completionRate = $documentStats['created'] > 0 
                            ? round(($documentStats['completed'] / $documentStats['created']) * 100, 1) 
                            : 0;
                    @endphp
                    <div class="text-3xl font-bold text-purple-600">{{ $completionRate }}%</div>
                    <div class="text-sm text-gray-500">Процент завершения</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $documentStats['by_category']->count() }}</div>
                    <div class="text-sm text-gray-500">Активных категорий</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Documents by Category -->
                <div>
                    <h3 class="text-md font-medium text-gray-900 mb-3">По категориям</h3>
                    @if($documentStats['by_category']->count() > 0)
                    <div class="space-y-2">
                        @foreach($documentStats['by_category'] as $stat)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="text-sm text-gray-700">{{ $stat->name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $stat->total }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">Нет данных</p>
                    @endif
                </div>

                <!-- Documents by Status -->
                <div>
                    <h3 class="text-md font-medium text-gray-900 mb-3">По статусам</h3>
                    @if($documentStats['by_status']->count() > 0)
                    <div class="space-y-2">
                        @foreach($documentStats['by_status'] as $stat)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="text-sm text-gray-700">
                                @switch($stat->status)
                                    @case('draft') Черновик @break
                                    @case('in_progress') В работе @break
                                    @case('approved') Одобрен @break
                                    @case('rejected') Отклонён @break
                                    @case('completed') Завершён @break
                                    @default {{ $stat->status }}
                                @endswitch
                            </span>
                            <span class="text-sm font-medium text-gray-900">{{ $stat->total }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">Нет данных</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Task Statistics -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Статистика задач</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $taskReports['created'] }}</div>
                    <div class="text-sm text-gray-500">Создано задач</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $taskReports['completed'] }}</div>
                    <div class="text-sm text-gray-500">Выполнено</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600">{{ $taskReports['overdue'] }}</div>
                    <div class="text-sm text-gray-500">Просрочено</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ $taskReports['avg_completion_time'] ? round($taskReports['avg_completion_time'], 1) : 0 }}ч
                    </div>
                    <div class="text-sm text-gray-500">Среднее время</div>
                </div>
            </div>
        </div>

        <!-- Department Performance -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Эффективность отделов</h2>
            
            @if($departmentPerformance->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Отдел</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Всего задач</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Выполнено</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Просрочено</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Эффективность</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($departmentPerformance as $dept)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $dept->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">{{ $dept->total_tasks }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-green-600 font-medium">{{ $dept->completed_tasks }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-red-600 font-medium">{{ $dept->overdue_tasks }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $efficiency = $dept->total_tasks > 0 
                                        ? round(($dept->completed_tasks / $dept->total_tasks) * 100, 1) 
                                        : 0;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($efficiency >= 80) bg-green-100 text-green-800
                                    @elseif($efficiency >= 60) bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $efficiency }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет данных</h3>
                <p class="mt-1 text-sm text-gray-500">Данных для анализа эффективности пока нет.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection