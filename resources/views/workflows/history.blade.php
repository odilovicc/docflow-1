@extends('layouts.app')

@section('title', 'История выполнения workflow: ' . $document->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('documents.show', $document) }}" 
               class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">История workflow</h1>
                <p class="mt-2 text-gray-600">{{ $document->title }}</p>
            </div>
        </div>
    </div>

    <!-- Информация о документе и workflow -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Информация о документе</h2>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Документ</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('documents.show', $document) }}" 
                           class="text-indigo-600 hover:text-indigo-900">
                            {{ $document->title }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Автор</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $document->author->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Текущий статус</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($document->status === 'completed') bg-green-100 text-green-800
                            @elseif($document->status === 'approved') bg-blue-100 text-blue-800
                            @elseif($document->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($document->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $document->status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Workflow</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($document->workflow)
                            <a href="{{ route('admin.workflows.show', $document->workflow) }}" 
                               class="text-indigo-600 hover:text-indigo-900">
                                {{ $document->workflow->name }}
                            </a>
                        @else
                            <span class="text-gray-400">Не назначен</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- История событий -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">История событий</h2>
            <p class="text-sm text-gray-500">Хронология всех действий, выполненных в рамках рабочего процесса</p>
        </div>
        
        @php
            // Получаем все активности для этого документа, связанные с workflow
            $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', 'App\Models\Document')
                ->where('subject_id', $document->id)
                ->whereIn('description', ['Workflow started', 'Workflow transition', 'Comment added during transition', 'Document created', 'Document updated'])
                ->with('causer')
                ->orderBy('created_at', 'desc')
                ->get();
        @endphp

        @if($activities->count() > 0)
            <div class="p-6">
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($activities as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                @if($activity->description === 'Workflow started') bg-green-500
                                                @elseif($activity->description === 'Workflow transition') bg-blue-500
                                                @elseif($activity->description === 'Comment added during transition') bg-yellow-500
                                                @elseif($activity->description === 'Document created') bg-indigo-500
                                                @elseif($activity->description === 'Document updated') bg-purple-500
                                                @else bg-gray-500 @endif">
                                                @if($activity->description === 'Workflow started')
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @elseif($activity->description === 'Workflow transition')
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                    </svg>
                                                @elseif($activity->description === 'Comment added during transition')
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                @elseif($activity->description === 'Document created')
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @elseif($activity->description === 'Document updated')
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    @if($activity->description === 'Workflow started')
                                                        <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Система' }}</span> запустил рабочий процесс
                                                        @if($activity->properties && isset($activity->properties['workflow_name']))
                                                            "<span class="font-medium">{{ $activity->properties['workflow_name'] }}</span>"
                                                        @endif
                                                    @elseif($activity->description === 'Workflow transition')
                                                        <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Система' }}</span> выполнил переход
                                                        @if($activity->properties && isset($activity->properties['transition_name']))
                                                            "<span class="font-medium">{{ $activity->properties['transition_name'] }}</span>"
                                                        @endif
                                                        @if($activity->properties && isset($activity->properties['from_state']) && isset($activity->properties['to_state']))
                                                            из состояния <span class="font-medium">{{ $activity->properties['from_state'] }}</span> 
                                                            в <span class="font-medium">{{ $activity->properties['to_state'] }}</span>
                                                        @endif
                                                    @elseif($activity->description === 'Comment added during transition')
                                                        <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Система' }}</span> добавил комментарий
                                                    @elseif($activity->description === 'Document created')
                                                        <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Система' }}</span> создал документ
                                                    @elseif($activity->description === 'Document updated')
                                                        <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Система' }}</span> обновил документ
                                                    @else
                                                        <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Система' }}</span> {{ $activity->description }}
                                                    @endif
                                                </p>
                                                
                                                @if($activity->properties && isset($activity->properties['comment']))
                                                    <div class="mt-2 text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                                                        <strong>Комментарий:</strong> {{ $activity->properties['comment'] }}
                                                    </div>
                                                @endif
                                                
                                                @if($activity->properties && count($activity->properties) > 0)
                                                    <div class="mt-2 text-xs text-gray-400">
                                                        @if(isset($activity->properties['workflow_id']))
                                                            Workflow ID: {{ $activity->properties['workflow_id'] }}
                                                        @endif
                                                        @if(isset($activity->properties['transition_name']) && $activity->description === 'Workflow transition')
                                                            • Переход: {{ $activity->properties['transition_name'] }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time datetime="{{ $activity->created_at->toISOString() }}">
                                                    {{ $activity->created_at->format('d.m.Y H:i') }}
                                                </time>
                                                <div class="text-xs text-gray-400">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">История событий пуста</h3>
                <p class="mt-2 text-gray-500">Для этого документа пока нет записей о выполнении рабочих процессов.</p>
                <div class="mt-6">
                    <a href="{{ route('documents.show', $document) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                        Вернуться к документу
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Текущее состояние workflow -->
    @if($document->workflow)
        <div class="mt-6 bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Схема рабочего процесса</h2>
                <p class="text-sm text-gray-500">Возможные переходы в текущем workflow</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($document->workflow->transitions as $transition)
                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-4 
                            {{ in_array($document->status, $transition['from']) ? 'bg-blue-50 border-blue-200' : 'bg-gray-50' }}">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    @foreach($transition['from'] as $fromState)
                                        <span class="px-2 py-1 text-xs rounded 
                                            {{ $fromState === $document->status ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                                            {{ $fromState }}
                                        </span>
                                    @endforeach
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                                <span class="px-2 py-1 text-xs rounded bg-green-200 text-green-700">
                                    {{ $transition['to'] }}
                                </span>
                            </div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $transition['name'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection