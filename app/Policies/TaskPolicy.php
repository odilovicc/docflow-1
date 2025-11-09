<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'DepartmentHead', 'Accountant', 'Lawyer', 'Employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Администраторы могут видеть все задачи
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Руководители департаментов видят задачи своего департамента
        if ($user->hasRole('DepartmentHead') && 
            $user->department_id === $task->document->department_id) {
            return true;
        }

        // Пользователь может видеть назначенные ему задачи или созданные им
        return $task->assigned_to === $user->id || $task->assigned_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'DepartmentHead']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Администраторы могут обновлять все задачи
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Руководители департаментов могут управлять задачами своего департамента
        if ($user->hasRole('DepartmentHead') && 
            $user->department_id === $task->document->department_id) {
            return true;
        }

        // Пользователь может обновлять только назначенные ему задачи
        return $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Только администраторы и руководители департаментов могут отменять задачи
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasRole('DepartmentHead') && 
            $user->department_id === $task->document->department_id) {
            return true;
        }

        // Создатель задачи может её отменить, если она ещё не выполнена
        return $task->assigned_by === $user->id && !$task->isCompleted();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->hasRole('Admin');
    }
}
