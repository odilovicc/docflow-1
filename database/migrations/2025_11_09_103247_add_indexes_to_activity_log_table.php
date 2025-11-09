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
        Schema::table('activity_log', function (Blueprint $table) {
            // Добавляем индексы для улучшения производительности запросов
            // Используем try-catch для избежания конфликтов с существующими индексами
            
            try {
                // Индекс для поиска по дате создания (используется при очистке)
                $table->index('created_at', 'activity_log_created_at_index');
            } catch (\Exception $e) {
                // Индекс уже существует, пропускаем
            }
            
            try {
                // Индекс для поиска по типу лога
                $table->index('log_name', 'activity_log_log_name_index');
            } catch (\Exception $e) {
                // Индекс уже существует, пропускаем
            }
            
            try {
                // Составной индекс для поиска по пользователю и дате
                $table->index(['causer_id', 'causer_type', 'created_at'], 'activity_log_causer_created_index');
            } catch (\Exception $e) {
                // Индекс уже существует, пропускаем
            }
            
            try {
                // Составной индекс для поиска по субъекту и дате
                $table->index(['subject_id', 'subject_type', 'created_at'], 'activity_log_subject_created_index');
            } catch (\Exception $e) {
                // Индекс уже существует, пропускаем
            }
            
            try {
                // Индекс для поиска по событию
                $table->index('event', 'activity_log_event_index');
            } catch (\Exception $e) {
                // Индекс уже существует, пропускаем
            }
            
            // Составной индекс для поиска по типу лога и дате (для очистки по типам)
            $table->index(['log_name', 'created_at'], 'activity_log_log_name_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Удаляем добавленные индексы
            $table->dropIndex('activity_log_created_at_index');
            $table->dropIndex('activity_log_log_name_index');
            $table->dropIndex('activity_log_causer_created_index');
            $table->dropIndex('activity_log_subject_created_index');
            $table->dropIndex('activity_log_event_index');
            $table->dropIndex('activity_log_log_name_created_index');
        });
    }
};
