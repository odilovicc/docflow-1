<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $department->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('departments.edit', $department) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Изменить
                </a>
                <a href="{{ route('departments.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    К списку отделов
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Информация об отделе -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Информация об отделе</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-600">Название:</label>
                                <p class="text-gray-900">{{ $department->name }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-600">Описание:</label>
                                <p class="text-gray-900">{{ $department->description ?: 'Не указано' }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-600">Руководитель:</label>
                                <p class="text-gray-900">
                                    @if($department->head)
                                        <strong>{{ $department->head->name }}</strong><br>
                                        <span class="text-sm text-gray-600">{{ $department->head->position }}</span><br>
                                        <span class="text-sm text-gray-600">{{ $department->head->email }}</span><br>
                                        <span class="text-sm text-gray-600">{{ $department->head->phone }}</span>
                                    @else
                                        <span class="text-gray-500">Не назначен</span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Дата создания:</label>
                                <p class="text-gray-900">{{ $department->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>

                        <!-- Сотрудники отдела -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">
                                Сотрудники отдела ({{ $department->users->count() }})
                            </h3>
                            
                            @if($department->users->count() > 0)
                                <div class="space-y-3">
                                    @foreach($department->users as $user)
                                        <div class="border-l-4 border-blue-500 pl-3 py-2 bg-white rounded">
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-600">{{ $user->position ?: 'Должность не указана' }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            @if($user->phone)
                                                <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">В отделе пока нет сотрудников</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>