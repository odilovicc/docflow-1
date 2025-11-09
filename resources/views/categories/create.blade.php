@extends('layouts.app')

@section('title', 'Создание новой категории')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Создание новой категории</h1>
                    <a href="{{ route('categories.index') }}" 
                       class="text-gray-600 hover:text-gray-900">
                        ← Назад к списку категорий
                    </a>
                </div>

                <!-- Create Form -->
                <form method="POST" action="{{ route('categories.store') }}" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Название категории *
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                            placeholder="Введите название категории"
                        >
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Описание категории
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                            placeholder="Введите описание категории (необязательно)"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-500 mt-1">
                            Описание поможет пользователям понять, какие документы должны относиться к этой категории.
                        </p>
                    </div>

                    <!-- Color (optional) -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                            Цвет категории
                        </label>
                        <div class="flex items-center space-x-3">
                            <input 
                                type="color" 
                                id="color" 
                                name="color" 
                                value="{{ old('color', '#3B82F6') }}" 
                                class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer"
                            >
                            <span class="text-sm text-gray-500">
                                Выберите цвет для визуального выделения категории в интерфейсе
                            </span>
                        </div>
                        @error('color')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="is_active" 
                            name="is_active" 
                            value="1" 
                            {{ old('is_active', '1') ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Активная категория
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 -mt-2">
                        Неактивные категории не будут доступны при создании новых документов, но существующие документы сохранят свою категорию.
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a 
                            href="{{ route('categories.index') }}" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                        >
                            Отмена
                        </a>
                        
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                        >
                            Создать категорию
                        </button>
                    </div>
                </form>

                <!-- Helpful Tips -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-900 mb-2">Советы по созданию категорий:</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Используйте понятные и описательные названия</li>
                        <li>• Создавайте категории по функциональным областям (HR, Финансы, IT и т.д.)</li>
                        <li>• Избегайте слишком специфичных категорий, которые будут содержать мало документов</li>
                        <li>• Цвета помогают быстро идентифицировать категории в списках</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Preview color selection
document.getElementById('color').addEventListener('change', function() {
    const color = this.value;
    const previewElements = document.querySelectorAll('.color-preview');
    
    previewElements.forEach(element => {
        element.style.backgroundColor = color;
    });
});

// Auto-generate slug from name (if needed for SEO URLs)
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    // Could be used for generating URL-friendly slugs if needed
});

// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    
    let counter = document.getElementById('description-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'description-counter';
        counter.className = 'text-sm text-gray-500 mt-1';
        this.parentNode.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} символов`;
    
    if (currentLength > maxLength) {
        counter.className = 'text-sm text-red-500 mt-1';
        this.classList.add('border-red-500');
    } else {
        counter.className = 'text-sm text-gray-500 mt-1';
        this.classList.remove('border-red-500');
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Название категории обязательно для заполнения');
        document.getElementById('name').focus();
        return;
    }
    
    if (name.length < 2) {
        e.preventDefault();
        alert('Название категории должно содержать минимум 2 символа');
        document.getElementById('name').focus();
        return;
    }
});
</script>
@endsection