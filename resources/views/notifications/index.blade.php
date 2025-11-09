@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Уведомления</h1>
                <div class="mt-4 sm:mt-0">
                    <button onclick="markAllAsRead()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Отметить все как прочитанные
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                <div class="p-6 hover:bg-gray-50 {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50' }}" 
                     id="notification-{{ $notification->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                @if($notification->data['type'] === 'task_assigned')
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                </div>
                                @else
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Уведомление' }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                                    
                                    @if(isset($notification->data['document_title']))
                                    <p class="text-xs text-gray-500 mt-1">Документ: {{ $notification->data['document_title'] }}</p>
                                    @endif
                                    
                                    @if(isset($notification->data['due_date']) && $notification->data['due_date'])
                                    <p class="text-xs text-gray-500 mt-1">
                                        Срок: {{ \Carbon\Carbon::parse($notification->data['due_date'])->format('d.m.Y H:i') }}
                                    </p>
                                    @endif
                                    
                                    <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2 ml-4">
                            @if(isset($notification->data['url']) && $notification->data['url'] !== '#')
                            <a href="{{ $notification->data['url'] }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Перейти
                            </a>
                            @endif
                            
                            @if(!$notification->read_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Новое
                            </span>
                            @endif
                            
                            <button onclick="deleteNotification('{{ $notification->id }}')" 
                                    class="text-gray-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7l-5 5h5V7zm6 0h5v5h-5V7zm-6 10V7h5v10H9z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет уведомлений</h3>
                <p class="mt-1 text-sm text-gray-500">У вас пока нет уведомлений.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function markAllAsRead() {
    fetch('{{ route('notifications.mark-all-read') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(id) {
    if (confirm('Удалить это уведомление?')) {
        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`notification-${id}`).remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection