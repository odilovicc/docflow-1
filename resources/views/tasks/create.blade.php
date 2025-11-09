@extends('layouts.app')

@section('title', 'Создание новой задачи')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Создание новой задачи</h1>
                    <a href="{{ route('tasks.index') }}" 
                       class="text-gray-600 hover:text-gray-900">
                        ← Назад к списку задач
                    </a>
                </div>

                <!-- Create Form -->
                <form method="POST" action="{{ route('tasks.store') }}" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Название задачи *
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title') }}" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                            placeholder="Введите название задачи"
                        >
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Описание задачи
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                            placeholder="Введите описание задачи"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Document and Assignee -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Document -->
                        <div>
                            <label for="document_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Связанный документ *
                            </label>
                            <select 
                                id="document_id" 
                                name="document_id" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('document_id') border-red-500 @enderror"
                            >
                                <option value="">Выберите документ</option>
                                @foreach($documents as $document)
                                    <option value="{{ $document->id }}" 
                                            {{ old('document_id', request('document_id')) == $document->id ? 'selected' : '' }}>
                                        {{ $document->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('document_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assignee -->
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                                Исполнитель *
                            </label>
                            <select 
                                id="assigned_to" 
                                name="assigned_to" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('assigned_to') border-red-500 @enderror"
                            >
                                <option value="">Выберите исполнителя</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->department->name ?? 'Без отдела' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Priority and Due Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                Приоритет *
                            </label>
                            <select 
                                id="priority" 
                                name="priority" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('priority') border-red-500 @enderror"
                            >
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                    Низкий
                                </option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                    Средний
                                </option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                    Высокий
                                </option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                    Срочный
                                </option>
                            </select>
                            @error('priority')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Срок выполнения
                            </label>
                            <input 
                                type="date" 
                                id="due_date" 
                                name="due_date" 
                                value="{{ old('due_date') }}" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('due_date') border-red-500 @enderror"
                                min="{{ date('Y-m-d') }}"
                            >
                            @error('due_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">
                            Инструкции для выполнения
                        </label>
                        <textarea 
                            id="instructions" 
                            name="instructions" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('instructions') border-red-500 @enderror"
                            placeholder="Дополнительные инструкции или требования к выполнению задачи"
                        >{{ old('instructions') }}</textarea>
                        @error('instructions')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status (hidden, defaults to pending) -->
                    <input type="hidden" name="status" value="pending">

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a 
                            href="{{ route('tasks.index') }}" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                        >
                            Отмена
                        </a>
                        
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                        >
                            Создать задачу
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-populate document if coming from document page
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const documentId = urlParams.get('document_id');
    
    if (documentId) {
        const documentSelect = document.getElementById('document_id');
        documentSelect.value = documentId;
        documentSelect.dispatchEvent(new Event('change'));
    }
});

// Priority color preview
document.getElementById('priority').addEventListener('change', function() {
    const priority = this.value;
    const colors = {
        'low': '#10B981',
        'medium': '#F59E0B', 
        'high': '#EF4444',
        'urgent': '#DC2626'
    };
    
    this.style.borderColor = colors[priority] || '#D1D5DB';
});

// Due date validation
document.getElementById('due_date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        alert('Срок выполнения не может быть в прошлом');
        this.value = '';
    }
});
</script>
@endsection