@extends('layouts.app')

@section('title', $department->name)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $department->name }}</h1>
                    <p class="text-gray-600 mt-2">Информация об отделе</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('departments.edit', $department) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Изменить
                    </a>
                    <a href="{{ route('departments.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        К списку отделов
                    </a>
                </div>
            </div>
        </div>
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