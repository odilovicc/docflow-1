@php
use App\Models\Document;

$myDocuments = Document::where('author_id', auth()->id())->count();
$departmentDocuments = Document::where('department_id', auth()->user()->department_id)->count();
$pendingDocuments = Document::where('department_id', auth()->user()->department_id)->where('status', 'in_progress')->count();
$recentDocuments = Document::with(['author', 'category'])->where('department_id', auth()->user()->department_id)->latest()->take(5)->get();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Панель управления - DocsFlow
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Приветствие -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">
                        Добро пожаловать, {{ auth()->user()->name }}!
                    </h3>
                    <p class="text-gray-600">
                        {{ auth()->user()->position }} • {{ auth()->user()->department?->name }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Роли: @foreach(auth()->user()->roles as $role){{ $role->name }}@if(!$loop->last), @endif @endforeach
                    </p>
                </div>
            </div>

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $myDocuments }}</div>
                            <div class="ml-3 text-sm text-gray-600">
                                Мои документы
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-green-600">{{ $departmentDocuments }}</div>
                            <div class="ml-3 text-sm text-gray-600">
                                Документов отдела
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $pendingDocuments }}</div>
                            <div class="ml-3 text-sm text-gray-600">
                                В процессе
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Быстрые действия</h4>
                    <div class="flex flex-wrap gap-3">
                        @can('document.create')
                            <a href="{{ route('documents.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Создать документ
                            </a>
                        @endcan
                        
                        <a href="{{ route('documents.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Все документы
                        </a>
                        
                        @can('department.manage')
                            <a href="{{ route('departments.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Управление отделами
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Последние документы -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Последние документы отдела</h4>
                    @if($recentDocuments->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentDocuments as $document)
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <a href="{{ route('documents.show', $document) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                                {{ $document->title }}
                                            </a>
                                            <div class="text-sm text-gray-600">
                                                {{ $document->category->name }} • Автор: {{ $document->author->name }}
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_color }}">
                                            {{ $document->status_label }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('documents.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                Посмотреть все документы →
                            </a>
                        </div>
                    @else
                        <p class="text-gray-500 italic">Пока нет документов в вашем отделе</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
