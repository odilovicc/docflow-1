@extends('layouts.app')

@section('title', 'Редактирование рабочего процесса: ' . $workflow->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.workflows.show', $workflow) }}" 
               class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Редактирование: {{ $workflow->name }}</h1>
                <p class="mt-2 text-gray-600">Изменение настроек рабочего процесса</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <h3 class="text-red-800 font-medium">Обнаружены ошибки:</h3>
                    <ul class="mt-2 text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.workflows.update', $workflow) }}" method="POST" id="workflow-form">
        @csrf
        @method('PUT')
        
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Основная информация -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Основная информация</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Название процесса <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $workflow->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Например: Согласование договоров"
                               required>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Категория документов
                        </label>
                        <select name="category_id" 
                                id="category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Все категории</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $workflow->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Краткое описание назначения рабочего процесса">{{ old('description', $workflow->description) }}</textarea>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $workflow->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Процесс активен</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Состояния -->
        <div class="mt-6 bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Состояния процесса</h2>
                <p class="text-sm text-gray-500">Определите все возможные состояния документа в процессе</p>
            </div>
            
            <div class="p-6">
                <div id="states-container">
                    <div class="space-y-3">
                        @php
                            $states = old('states', $workflow->states);
                            $initialState = old('initial_state', $workflow->initial_state);
                        @endphp
                        @foreach($states as $index => $state)
                            <div class="state-item flex items-center space-x-3">
                                <input type="text" 
                                       name="states[]" 
                                       value="{{ $state }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Название состояния"
                                       required>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="initial_state_index" 
                                           value="{{ $index }}"
                                           {{ $initialState === $state ? 'checked' : '' }}
                                           class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Начальное</span>
                                </label>
                                @if($index > 1)
                                    <button type="button" 
                                            onclick="removeState(this)"
                                            class="text-red-600 hover:text-red-800 p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <button type="button" 
                            onclick="addState()"
                            class="mt-3 inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Добавить состояние
                    </button>
                </div>
                
                <!-- Скрытое поле для начального состояния -->
                <input type="hidden" name="initial_state" id="initial_state" value="{{ old('initial_state', $workflow->initial_state) }}">
            </div>
        </div>

        <!-- Переходы -->
        <div class="mt-6 bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Переходы между состояниями</h2>
                <p class="text-sm text-gray-500">Определите возможные переходы из одного состояния в другое</p>
            </div>
            
            <div class="p-6">
                <div id="transitions-container">
                    <div class="space-y-4">
                        @php
                            $transitions = old('transitions', $workflow->transitions);
                        @endphp
                        @foreach($transitions as $index => $transition)
                            <div class="transition-item border border-gray-200 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Название перехода</label>
                                        <input type="text" 
                                               name="transitions[{{ $index }}][name]" 
                                               value="{{ $transition['name'] }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="submit, approve, reject..."
                                               required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Из состояний</label>
                                        <input type="text" 
                                               name="transitions[{{ $index }}][from_states]" 
                                               value="{{ implode(', ', $transition['from']) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="draft, in_progress..."
                                               required>
                                        <p class="text-xs text-gray-500 mt-1">Через запятую</p>
                                    </div>
                                    
                                    <div class="flex items-end">
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">В состояние</label>
                                            <input type="text" 
                                                   name="transitions[{{ $index }}][to]" 
                                                   value="{{ $transition['to'] }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="in_progress, approved..."
                                                   required>
                                        </div>
                                        
                                        @if($index > 0)
                                            <button type="button" 
                                                    onclick="removeTransition(this)"
                                                    class="ml-3 text-red-600 hover:text-red-800 p-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <button type="button" 
                            onclick="addTransition()"
                            class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Добавить переход
                    </button>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.workflows.show', $workflow) }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Отмена
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                Сохранить изменения
            </button>
        </div>
    </form>
</div>

<script>
let stateIndex = {{ count($states) }};
let transitionIndex = {{ count($transitions) }};

function addState() {
    const container = document.getElementById('states-container').querySelector('.space-y-3');
    const stateItem = document.createElement('div');
    stateItem.className = 'state-item flex items-center space-x-3';
    stateItem.innerHTML = `
        <input type="text" 
               name="states[]" 
               value=""
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
               placeholder="Название состояния"
               required>
        <label class="flex items-center">
            <input type="radio" 
                   name="initial_state_index" 
                   value="${stateIndex}"
                   class="text-indigo-600 focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-600">Начальное</span>
        </label>
        <button type="button" 
                onclick="removeState(this)"
                class="text-red-600 hover:text-red-800 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </button>
    `;
    container.appendChild(stateItem);
    stateIndex++;
}

function removeState(button) {
    button.closest('.state-item').remove();
}

function addTransition() {
    const container = document.getElementById('transitions-container').querySelector('.space-y-4');
    const transitionItem = document.createElement('div');
    transitionItem.className = 'transition-item border border-gray-200 rounded-lg p-4';
    transitionItem.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Название перехода</label>
                <input type="text" 
                       name="transitions[${transitionIndex}][name]" 
                       value=""
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="submit, approve, reject..."
                       required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Из состояний</label>
                <input type="text" 
                       name="transitions[${transitionIndex}][from_states]" 
                       value=""
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="draft, in_progress..."
                       required>
                <p class="text-xs text-gray-500 mt-1">Через запятую</p>
            </div>
            
            <div class="flex items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">В состояние</label>
                    <input type="text" 
                           name="transitions[${transitionIndex}][to]" 
                           value=""
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="in_progress, approved..."
                           required>
                </div>
                
                <button type="button" 
                        onclick="removeTransition(this)"
                        class="ml-3 text-red-600 hover:text-red-800 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    container.appendChild(transitionItem);
    transitionIndex++;
}

function removeTransition(button) {
    button.closest('.transition-item').remove();
}

// Обновление скрытого поля initial_state при выборе радио-кнопки
document.addEventListener('change', function(e) {
    if (e.target.name === 'initial_state_index') {
        const stateInputs = document.querySelectorAll('input[name="states[]"]');
        const selectedIndex = parseInt(e.target.value);
        const initialStateValue = stateInputs[selectedIndex].value;
        document.getElementById('initial_state').value = initialStateValue;
    }
});

// Обработка формы перед отправкой
document.getElementById('workflow-form').addEventListener('submit', function(e) {
    // Преобразуем transitions в правильный формат
    const transitionItems = document.querySelectorAll('.transition-item');
    transitionItems.forEach((item, index) => {
        const fromStatesInput = item.querySelector(`input[name*="[from_states]"]`);
        if (fromStatesInput) {
            const fromStatesValue = fromStatesInput.value.split(',').map(s => s.trim()).filter(s => s);
            
            // Создаем скрытые поля для массива from
            fromStatesValue.forEach((state, stateIndex) => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `transitions[${index}][from][${stateIndex}]`;
                hiddenInput.value = state;
                item.appendChild(hiddenInput);
            });
            
            // Удаляем оригинальное поле from_states
            fromStatesInput.remove();
        }
    });
});
</script>
@endsection