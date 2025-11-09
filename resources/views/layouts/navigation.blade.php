<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    <!-- Документы -->
                    <x-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.*')">
                        {{ __('Документы') }}
                    </x-nav-link>
                    
                    <!-- Задачи -->
                    <x-nav-link :href="route('tasks.my')" :active="request()->routeIs('tasks.my')">
                        {{ __('Мои задачи') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.index')">
                        {{ __('Все задачи') }}
                    </x-nav-link>
                    
                    <!-- Поиск -->
                    <x-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">
                        {{ __('Поиск') }}
                    </x-nav-link>
                    
                    <!-- Отделы (только для админов) -->
                    @can('department.manage')
                        <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                            {{ __('Отделы') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Notifications -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="relative" id="notifications-dropdown">
                    <button onclick="toggleNotifications()" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7l-5 5h5V7zm6 0h5v5h-5V7zm-6 10V7h5v10H9z"></path>
                        </svg>
                        <span id="notification-badge" class="hidden absolute top-0 right-0 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <div id="notifications-panel" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-20">
                        <div class="py-2">
                            <div class="px-4 py-2 text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b">
                                Уведомления
                            </div>
                            <div id="notifications-list" class="max-h-64 overflow-y-auto">
                                <!-- Notifications will be loaded here -->
                            </div>
                            <div class="px-4 py-2 border-t bg-gray-50">
                                <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Все уведомления</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Профиль') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('notifications.index')">
                            {{ __('Уведомления') }}
                        </x-dropdown-link>
                        
                        <!-- Расширенный поиск -->
                        <x-dropdown-link :href="route('search.advanced')">
                            {{ __('Расширенный поиск') }}
                        </x-dropdown-link>
                        
                        <!-- Административные функции -->
                        @hasrole('Admin')
                        <div class="border-t border-gray-100"></div>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('АДМИНИСТРИРОВАНИЕ') }}
                        </div>
                        
                        <x-dropdown-link :href="route('admin.dashboard')">
                            {{ __('Панель администратора') }}
                        </x-dropdown-link>
                        
                        <x-dropdown-link :href="route('admin.users')">
                            {{ __('Пользователи') }}
                        </x-dropdown-link>
                        
                        <x-dropdown-link :href="route('admin.reports')">
                            {{ __('Отчеты') }}
                        </x-dropdown-link>
                        
                        <x-dropdown-link :href="route('admin.settings')">
                            {{ __('Настройки системы') }}
                        </x-dropdown-link>
                        
                        <x-dropdown-link :href="route('admin.logs')">
                            {{ __('Логи системы') }}
                        </x-dropdown-link>
                        
                        <x-dropdown-link :href="route('search.manage')">
                            {{ __('Управление поиском') }}
                        </x-dropdown-link>
                        @endhasrole

                        <div class="border-t border-gray-100"></div>
                        
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Выйти') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <!-- Документы -->
            <x-responsive-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.*')">
                {{ __('Документы') }}
            </x-responsive-nav-link>
            
            <!-- Задачи -->
            <x-responsive-nav-link :href="route('tasks.my')" :active="request()->routeIs('tasks.my')">
                {{ __('Мои задачи') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.index')">
                {{ __('Все задачи') }}
            </x-responsive-nav-link>
            
            <!-- Поиск -->
            <x-responsive-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">
                {{ __('Поиск') }}
            </x-responsive-nav-link>
            
            <!-- Уведомления -->
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                {{ __('Уведомления') }}
            </x-responsive-nav-link>
            
            <!-- Отделы (только для админов) -->
            @can('department.manage')
                <x-responsive-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                    {{ __('Отделы') }}
                </x-responsive-nav-link>
            @endcan
            
            <!-- Административные функции -->
            @hasrole('Admin')
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4 text-xs text-gray-400 uppercase">
                        {{ __('Администрирование') }}
                    </div>
                </div>
                
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Панель администратора') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">
                    {{ __('Пользователи') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports')">
                    {{ __('Отчеты') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">
                    {{ __('Настройки') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('admin.logs')" :active="request()->routeIs('admin.logs')">
                    {{ __('Логи') }}
                </x-responsive-nav-link>
            @endhasrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Профиль') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('search.advanced')">
                    {{ __('Расширенный поиск') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Выйти') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
let notificationsOpen = false;

function toggleNotifications() {
    const panel = document.getElementById('notifications-panel');
    notificationsOpen = !notificationsOpen;
    
    if (notificationsOpen) {
        panel.classList.remove('hidden');
        loadNotifications();
    } else {
        panel.classList.add('hidden');
    }
}

function loadNotifications() {
    fetch('{{ route('notifications.dropdown') }}')
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('notifications-list');
            const badge = document.getElementById('notification-badge');
            
            if (data.unread_count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
            
            if (data.notifications.length === 0) {
                list.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Нет уведомлений</div>';
                return;
            }
            
            list.innerHTML = data.notifications.map(notification => `
                <a href="${notification.url}" class="block px-4 py-3 text-sm ${notification.read_at ? 'text-gray-600' : 'text-gray-900 bg-blue-50'} hover:bg-gray-100">
                    <div class="font-medium">${notification.title}</div>
                    <div class="text-gray-500">${notification.message}</div>
                    <div class="text-xs text-gray-400 mt-1">${notification.created_at}</div>
                </a>
            `).join('');
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Check for new notifications periodically
setInterval(() => {
    fetch('{{ route('notifications.unread-count') }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            if (data.count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}, 30000); // Check every 30 seconds

// Close notifications when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notifications-dropdown');
    const panel = document.getElementById('notifications-panel');
    
    if (!dropdown.contains(event.target) && notificationsOpen) {
        panel.classList.add('hidden');
        notificationsOpen = false;
    }
});

// Load initial notification count
window.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route('notifications.unread-count') }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            if (data.count > 0) {
                badge.classList.remove('hidden');
            }
        })
        .catch(error => console.error('Error loading initial notifications:', error));
});
</script>
