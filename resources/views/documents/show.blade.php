@php
use App\Services\WorkflowService;
$workflowService = app(WorkflowService::class);
$availableTransitions = $document->workflow ? $workflowService->availableTransitions(auth()->user(), $document) : [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $document->title }}
            </h2>
            <div class="flex space-x-2">
                @can('document.edit')
                    <a href="{{ route('documents.edit', $document) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Изменить
                    </a>
                @endcan
                <a href="{{ route('documents.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    К списку документов
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Основная информация -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-4">Информация о документе</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Название:</label>
                                    <p class="text-gray-900">{{ $document->title }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Статус:</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_color }}">
                                        {{ $document->status_label }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Категория:</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                          style="background-color: {{ $document->category->color }}">
                                        {{ $document->category->name }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Отдел:</label>
                                    <p class="text-gray-900">{{ $document->department->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Автор:</label>
                                    <p class="text-gray-900">{{ $document->author->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Дата создания:</label>
                                    <p class="text-gray-900">{{ $document->created_at->format('d.m.Y H:i') }}</p>
                                </div>

                                @if($document->due_date)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Срок выполнения:</label>
                                        <p class="text-gray-900">{{ $document->due_date->format('d.m.Y') }}</p>
                                    </div>
                                @endif

                                @if($document->workflow)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Workflow:</label>
                                        <p class="text-gray-900">{{ $document->workflow->name }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($document->description)
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-600">Описание:</label>
                                    <p class="text-gray-900 mt-1">{{ $document->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Файлы документа -->
                    @if($document->getMedia('documents')->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Файлы документа</h3>
                                <div class="space-y-2">
                                    @foreach($document->getMedia('documents') as $media)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-sm">{{ $media->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">({{ $media->human_readable_size }})</span>
                                            </div>
                                            <a href="{{ $media->getUrl() }}" target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                Скачать
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Панель управления workflow -->
                <div class="space-y-6">
                    <!-- Workflow действия -->
                    @if(!$document->workflow)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Workflow</h3>
                                <p class="text-gray-600 mb-4">Workflow не запущен для этого документа</p>
                                @can('document.edit')
                                    <form action="{{ route('workflow.start', $document) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                            Запустить Workflow
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Действия Workflow</h3>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-600">Текущий статус:</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_color }}">
                                        {{ $document->status_label }}
                                    </span>
                                </div>

                                @if(count($availableTransitions) > 0)
                                    <form action="{{ route('workflow.transition', $document) }}" method="POST">
                                        @csrf
                                        
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Выберите действие:
                                            </label>
                                            <select name="transition" required 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                                <option value="">Выберите действие</option>
                                                @foreach($availableTransitions as $transition)
                                                    <option value="{{ $transition['name'] }}">
                                                        {{ $transition['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Комментарий (опционально):
                                            </label>
                                            <textarea name="comment" rows="3" 
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                                      placeholder="Добавьте комментарий к действию..."></textarea>
                                        </div>

                                        <button type="submit" 
                                                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Выполнить
                                        </button>
                                    </form>
                                @else
                                    <p class="text-gray-600">У вас нет доступных действий для этого документа</p>
                                @endif

                                <div class="mt-4 pt-4 border-t">
                                    <a href="{{ route('workflow.history', $document) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        Посмотреть историю →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>