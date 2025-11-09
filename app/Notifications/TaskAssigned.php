<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Новая задача назначена в DocsFlow')
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Вам назначена новая задача в системе DocsFlow.')
            ->line('**Задача:** ' . $this->task->title)
            ->line('**Документ:** ' . $this->task->document->title)
            ->when($this->task->due_date, function ($message) {
                return $message->line('**Срок выполнения:** ' . $this->task->due_date->format('d.m.Y H:i'));
            })
            ->when($this->task->description, function ($message) {
                return $message->line('**Описание:** ' . $this->task->description);
            })
            ->action('Перейти к задаче', route('tasks.show', $this->task))
            ->line('Пожалуйста, выполните эту задачу в установленные сроки.')
            ->salutation('С уважением, система DocsFlow');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'type' => 'task_assigned',
            'title' => 'Новая задача назначена',
            'message' => "Вам назначена задача: {$this->task->title}",
            'document_id' => $this->task->document_id,
            'document_title' => $this->task->document->title,
            'due_date' => $this->task->due_date,
            'assigned_by' => $this->task->assignedByUser->name,
            'url' => route('tasks.show', $this->task),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}