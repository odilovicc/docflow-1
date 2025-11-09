@extends('layouts.app')

@section('title', 'Редактирование задачи')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Редактирование задачи</h1>
                    <a href="{{ route('tasks.show', $task) }}" 
                       class="text-gray-600 hover:text-gray-900">
                        ← Назад к задаче
                    </a>
                </div>

                <!-- Task Status Info -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-600">Статус: </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->status === 'completed') bg-green-100 text-green-800
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($task->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($task->status === 'cancelled') bg-red-100 text-red-800
                                @endif">
                                {{ $task->status_label }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600">
                            Создано: {{ $task->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Название задачи *
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title', $task->title) }}" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                            {{ $task->status === 'completed' ? 'readonly' : '' }}
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
                            {{ $task->status === 'completed' ? 'readonly' : '' }}
                        >{{ old('description', $task->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Document (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Связанный документ
                        </label>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <a href="{{ route('documents.show', $task->document) }}" 
                               class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $task->document->title }}
                            </a>
                        </div>
                    </div>

                    <!-- Assignee and Priority -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                {{ $task->status === 'completed' ? 'disabled' : '' }}
                            >
                                <option value="">Выберите исполнителя</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->department->name ?? 'Без отдела' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

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
                                {{ $task->status === 'completed' ? 'disabled' : '' }}
                            >
                                <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>
                                    Низкий
                                </option>
                                <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>
                                    Средний
                                </option>
                                <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>
                                    Высокий
                                </option>
                                <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>
                                    Срочный
                                </option>
                            </select>
                            @error('priority')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status and Due Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Статус *
                            </label>
                            <select 
                                id="status" 
                                name="status" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror"
                            >
                                <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>
                                    Ожидает выполнения
                                </option>
                                <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>
                                    В работе
                                </option>
                                <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>
                                    Выполнена
                                </option>
                                @can('cancel', $task)
                                <option value="cancelled" {{ old('status', $task->status) == 'cancelled' ? 'selected' : '' }}>
                                    Отменена
                                </option>
                                @endcan
                            </select>
                            @error('status')
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
                                value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('due_date') border-red-500 @enderror"
                                {{ $task->status === 'completed' ? 'readonly' : '' }}
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
                            {{ $task->status === 'completed' ? 'readonly' : '' }}
                        >{{ old('instructions', $task->instructions) }}</textarea>
                        @error('instructions')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Completion Notes (only if status is completed) -->
                    <div id="completion-notes" style="{{ old('status', $task->status) === 'completed' ? '' : 'display: none;' }}">
                        <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Примечания к выполнению
                        </label>
                        <textarea 
                            id="completion_notes" 
                            name="completion_notes" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('completion_notes') border-red-500 @enderror"
                            placeholder="Добавьте примечания о выполненной работе"
                        >{{ old('completion_notes', $task->completion_notes) }}</textarea>
                        @error('completion_notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            @if($task->updated_at != $task->created_at)
                                Последнее изменение: {{ $task->updated_at->format('d.m.Y H:i') }}
                            @endif
                        </div>
                        
                        <div class="flex space-x-4">
                            <a 
                                href="{{ route('tasks.show', $task) }}" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                            >
                                Отмена
                            </a>
                            
                            <button 
                                type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                            >
                                Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Show/hide completion notes based on status
document.getElementById('status').addEventListener('change', function() {
    const completionNotes = document.getElementById('completion-notes');
    const isCompleted = this.value === 'completed';
    
    completionNotes.style.display = isCompleted ? 'block' : 'none';
    
    if (isCompleted) {
        document.getElementById('completion_notes').focus();
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

// Status color preview
document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const colors = {
        'pending': '#F59E0B',
        'in_progress': '#3B82F6',
        'completed': '#10B981',
        'cancelled': '#EF4444'
    };
    
    this.style.borderColor = colors[status] || '#D1D5DB';
});

// Confirm status change to completed
document.getElementById('status').addEventListener('change', function() {
    if (this.value === 'completed' && '{{ $task->status }}' !== 'completed') {
        if (!confirm('Вы уверены, что хотите отметить задачу как выполненную? Это действие ограничит дальнейшее редактирование.')) {
            this.value = '{{ old("status", $task->status) }}';
            document.getElementById('completion-notes').style.display = 'none';
        }
    }
});

// Due date validation
document.getElementById('due_date').addEventListener('change', function() {
    if (this.value && '{{ $task->status }}' !== 'completed') {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            if (!confirm('Выбранная дата в прошлом. Продолжить?')) {
                this.value = '{{ old("due_date", $task->due_date?->format("Y-m-d")) }}';
            }
        }
    }
});
</script>
@endsection