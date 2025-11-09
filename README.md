# DocsFlow - Система документооборота

**DocsFlow** — это современная система документооборота, построенная на Laravel 12 с поддержкой многоступенчатого workflow согласования документов, управлением ролями и правами, версионированием файлов и аудитом действий.

## Особенности

- 🔐 **Аутентификация и авторизация** с ролевой моделью доступа
- 🏢 **Управление отделами** и пользователями
- 📄 **Система документооборота** с категориями и версионированием файлов
- ⚙️ **Настраиваемые Workflow** с многоступенчатым согласованием
- 📋 **Система задач** с автоматическим назначением и уведомлениями
- 📊 **Аудит и логирование** всех действий пользователей
- 🎛️ **Административная панель** для управления системой
- 🚀 **Очереди Redis** для асинхронной обработки задач

## Системные требования

- PHP 8.3+
- Composer
- Node.js 22+
- MySQL 8.0+
- Redis 6.0+ (для очередей и кеша)

## Установка

### 1. Клонирование репозитория

```bash
git clone <repository-url> docflow
cd docflow
```

### 2. Установка зависимостей

```bash
# PHP зависимости
composer install

# Node.js зависимости
npm install
```

### 3. Настройка окружения

```bash
# Копировать файл конфигурации
cp .env.example .env

# Генерировать ключ приложения
php artisan key:generate
```

### 4. Настройка базы данных

Отредактируйте файл `.env` и укажите данные для подключения к MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=docflow
DB_USERNAME=root
DB_PASSWORD=your_password
```

Создайте базу данных:

```sql
CREATE DATABASE docflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Настройка Redis

Убедитесь, что Redis запущен и доступен. В файле `.env` проверьте настройки:

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

### 6. Выполнение миграций и сидеров

```bash
# Запуск миграций
php artisan migrate

# Заполнение данными (создание ролей, прав, тестовых пользователей)
php artisan db:seed
```

### 7. Настройка файлового хранилища

```bash
# Создание символической ссылки для storage
php artisan storage:link
```

### 8. Сборка фронтенд ресурсов

```bash
# Для разработки
npm run dev

# Для продакшена
npm run build
```

## Быстрый старт

### Режим разработки

```bash
# Запуск веб-сервера
php artisan serve

# Запуск очередей (в отдельном терминале)
php artisan queue:work

# Запуск Vite для hot reload (в отдельном терминале)
npm run dev
```

Приложение будет доступно по адресу: http://localhost:8000

### Тестовые пользователи

После выполнения сидеров будут созданы следующие тестовые пользователи:

| Email | Пароль | Роль | Отдел |
|-------|--------|------|-------|
| admin@docflow.com | password | Admin | IT |
| head@docflow.com | password | DepartmentHead | Управление |
| accountant@docflow.com | password | Accountant | Бухгалтерия |
| lawyer@docflow.com | password | Lawyer | Юридический |
| employee@docflow.com | password | Employee | IT |

## Команды Artisan

### Базовые команды

```bash
# Очистка кеша
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Обновление конфигурации
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Работа с очередями

```bash
# Запуск обработки очередей
php artisan queue:work

# Запуск в режиме демона
php artisan queue:work --daemon

# Перезапуск воркеров очереди
php artisan queue:restart

# Просмотр статистики очередей (требует Laravel Horizon)
php artisan horizon
```

### Миграции и сидеры

```bash
# Откат миграций
php artisan migrate:rollback

# Полный пересоздание базы данных
php artisan migrate:fresh --seed

# Запуск конкретного сидера
php artisan db:seed --class=RolePermissionSeeder
```

## Этапы разработки

Система DocsFlow разрабатывается поэтапно. Каждый этап представляет собой законченную функциональность:

1. **Этап 0** - Базовая настройка проекта ✅
2. **Этап 1** - Аутентификация и пользователи
3. **Этап 2** - Роли и права доступа (Spatie)
4. **Этап 3** - Управление отделами
5. **Этап 4** - Категории и документы
6. **Этап 5** - Workflow и согласования
7. **Этап 6** - Задачи и уведомления
8. **Этап 7** - Административная панель
9. **Этап 8** - Аудит и логирование
10. **Этап 9** - Финальная настройка и документация

## Техническая документация

### Используемые пакеты

- **spatie/laravel-permission** - управление ролями и правами
- **spatie/laravel-medialibrary** - версионирование файлов
- **spatie/laravel-activitylog** - аудит и логирование
- **symfony/workflow** - система workflow

### Архитектура

Система построена по принципу Domain-Driven Design (DDD) с использованием:

- **Сервисные классы** для бизнес-логики
- **Политики доступа** для авторизации
- **События и слушатели** для декларативного программирования
- **Очереди** для асинхронной обработки
- **Фабрики и сидеры** для тестовых данных

## Поддержка

При возникновении проблем:

1. Проверьте логи в `storage/logs/`
2. Убедитесь, что все сервисы (MySQL, Redis) запущены
3. Проверьте права доступа к файлам и папкам
4. Очистите кеш: `php artisan cache:clear`

## Лицензия

Этот проект распространяется под лицензией MIT.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
