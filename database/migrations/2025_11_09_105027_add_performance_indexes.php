<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Индексы для таблицы documents
        Schema::table('documents', function (Blueprint $table) {
            // Составной индекс для фильтрации по статусу и дате
            $table->index(['status', 'created_at'], 'documents_status_created_at_index');
            
            // Составной индекс для фильтрации по отделу и статусу
            $table->index(['department_id', 'status'], 'documents_department_status_index');
            
            // Составной индекс для фильтрации по категории и дате
            $table->index(['category_id', 'created_at'], 'documents_category_created_at_index');
            
            // Индекс для поиска по автору
            $table->index('author_id', 'documents_author_id_index');
            
            // Индекс для workflow
            $table->index('workflow_id', 'documents_workflow_id_index');
        });

        // Индексы для таблицы tasks
        Schema::table('tasks', function (Blueprint $table) {
            // Составной индекс для фильтрации по исполнителю и статусу
            $table->index(['assignee_id', 'status'], 'tasks_assignee_status_index');
            
            // Составной индекс для просроченных задач
            $table->index(['due_date', 'status'], 'tasks_due_date_status_index');
            
            // Составной индекс для задач по документам и статусу
            $table->index(['document_id', 'status'], 'tasks_document_status_index');
            
            // Индекс для сортировки по приоритету и дате создания
            $table->index(['priority', 'created_at'], 'tasks_priority_created_at_index');
            
            // Индекс для поиска по типу задачи
            $table->index('type', 'tasks_type_index');
        });

        // Индексы для таблицы users
        Schema::table('users', function (Blueprint $table) {
            // Составной индекс для активных пользователей по отделу
            $table->index(['department_id', 'is_active'], 'users_department_active_index');
            
            // Индекс для поиска по статусу активности
            $table->index('is_active', 'users_is_active_index');
        });

        // Индексы для таблицы notifications
        Schema::table('notifications', function (Blueprint $table) {
            // Составной индекс для непрочитанных уведомлений пользователя
            $table->index(['notifiable_id', 'read_at'], 'notifications_user_read_index');
            
            // Составной индекс для уведомлений по типу и дате
            $table->index(['type', 'created_at'], 'notifications_type_created_index');
        });

        // Индексы для таблицы workflow_steps
        Schema::table('workflow_steps', function (Blueprint $table) {
            // Составной индекс для шагов workflow по порядку
            $table->index(['workflow_id', 'order'], 'workflow_steps_workflow_order_index');
            
            // Индекс для поиска по роли
            $table->index('role', 'workflow_steps_role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаление индексов для documents
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_status_created_at_index');
            $table->dropIndex('documents_department_status_index');
            $table->dropIndex('documents_category_created_at_index');
            $table->dropIndex('documents_author_id_index');
            $table->dropIndex('documents_workflow_id_index');
        });

        // Удаление индексов для tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_assignee_status_index');
            $table->dropIndex('tasks_due_date_status_index');
            $table->dropIndex('tasks_document_status_index');
            $table->dropIndex('tasks_priority_created_at_index');
            $table->dropIndex('tasks_type_index');
        });

        // Удаление индексов для users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_department_active_index');
            $table->dropIndex('users_is_active_index');
        });

        // Удаление индексов для notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_index');
            $table->dropIndex('notifications_type_created_index');
        });

        // Удаление индексов для workflow_steps
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropIndex('workflow_steps_workflow_order_index');
            $table->dropIndex('workflow_steps_role_index');
        });
    }
};
