@extends('layouts.app')

@section('title', 'Редактирование документа')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Редактирование документа</h1>
                    <a href="{{ route('documents.show', $document) }}" 
                       class="text-gray-600 hover:text-gray-900">
                        ← Назад к документу
                    </a>
                </div>

                <!-- Edit Form -->
                <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Название документа *
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title', $document->title) }}" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                            placeholder="Введите название документа"
                        >
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                            Содержание документа *
                        </label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="10" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('content') border-red-500 @enderror"
                            placeholder="Введите содержание документа"
                        >{{ old('content', $document->content) }}</textarea>
                        @error('content')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category and Department -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Категория *
                            </label>
                            <select 
                                id="category_id" 
                                name="category_id" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('category_id') border-red-500 @enderror"
                            >
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $document->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Отдел *
                            </label>
                            <select 
                                id="department_id" 
                                name="department_id" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('department_id') border-red-500 @enderror"
                            >
                                <option value="">Выберите отдел</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                            {{ old('department_id', $document->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- File Attachments -->
                    @if($document->attachments && count($document->attachments) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Текущие вложения
                            </label>
                            <div class="space-y-2">
                                @foreach($document->attachments as $attachment)
                                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-700">{{ $attachment->name }}</span>
                                            <span class="text-xs text-gray-500 ml-2">({{ number_format($attachment->size / 1024, 1) }} KB)</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ $attachment->url }}" 
                                               class="text-indigo-600 hover:text-indigo-800 text-sm" 
                                               target="_blank">
                                                Скачать
                                            </a>
                                            <button type="button" 
                                                    onclick="removeAttachment({{ $attachment->id }})"
                                                    class="text-red-600 hover:text-red-800 text-sm">
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Add New Attachments -->
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">
                            Добавить новые вложения
                        </label>
                        <input 
                            type="file" 
                            id="attachments" 
                            name="attachments[]" 
                            multiple
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png"
                        >
                        <p class="text-sm text-gray-500 mt-1">
                            Разрешенные форматы: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, JPEG, PNG. Максимальный размер: 10MB
                        </p>
                        @error('attachments')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status (only if document is draft) -->
                    @if($document->status === 'draft')
                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="submit_for_review" 
                                    value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">
                                    Отправить документ на рассмотрение после сохранения
                                </span>
                            </label>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <div>
                            @if($document->status === 'draft')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Черновик
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($document->status === 'approved') bg-green-100 text-green-800
                                    @elseif($document->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($document->status === 'rejected') bg-red-100 text-red-800
                                    @endif">
                                    {{ $document->status_label }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex space-x-4">
                            <a 
                                href="{{ route('documents.show', $document) }}" 
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
function removeAttachment(attachmentId) {
    if (confirm('Вы уверены, что хотите удалить это вложение?')) {
        fetch(`/attachments/${attachmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка при удалении вложения');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка');
        });
    }
}

// File upload validation
document.getElementById('attachments').addEventListener('change', function(e) {
    const files = e.target.files;
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];
    
    for (let file of files) {
        if (file.size > maxSize) {
            alert(`Файл ${file.name} превышает максимальный размер 10MB`);
            e.target.value = '';
            return;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert(`Файл ${file.name} имеет недопустимый тип`);
            e.target.value = '';
            return;
        }
    }
});
</script>
@endsection