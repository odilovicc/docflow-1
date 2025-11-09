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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Название шага
            $table->string('state'); // Состояние в workflow
            $table->integer('order')->default(0); // Порядок шага
            $table->json('required_roles')->nullable(); // Роли, которые могут выполнить шаг
            $table->json('required_permissions')->nullable(); // Права, которые требуются
            $table->integer('sla_days')->nullable(); // SLA в днях
            $table->boolean('auto_assign')->default(false); // Автоназначение
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
