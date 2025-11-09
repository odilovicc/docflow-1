<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTaskNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-notifications 
                            {--overdue : Send notifications only for overdue tasks}
                            {--upcoming : Send notifications for upcoming deadline tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for task deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting task notifications...');

        $overdueCount = 0;
        $upcomingCount = 0;

        // Отправляем уведомления о просроченных задачах
        if ($this->option('overdue') || (!$this->option('overdue') && !$this->option('upcoming'))) {
            $overdueCount = $this->sendOverdueNotifications();
            $this->info("Sent {$overdueCount} overdue task notifications");
        }

        // Отправляем уведомления о приближающихся дедлайнах
        if ($this->option('upcoming') || (!$this->option('overdue') && !$this->option('upcoming'))) {
            $upcomingCount = $this->sendUpcomingDeadlineNotifications();
            $this->info("Sent {$upcomingCount} upcoming deadline notifications");
        }

        $this->info("Task notifications completed. Total: " . ($overdueCount + $upcomingCount));
        
        return 0;
    }

    /**
     * Send notifications for overdue tasks
     */
    private function sendOverdueNotifications(): int
    {
        $overdueTasks = Task::with(['assignedToUser', 'document'])
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $count = 0;
        $groupedTasks = $overdueTasks->groupBy('assigned_to');

        foreach ($groupedTasks as $userId => $tasks) {
            $user = User::find($userId);
            if (!$user || !$user->email) {
                continue;
            }

            try {
                $this->sendOverdueEmail($user, $tasks);
                $count++;
                
                // Логируем отправку уведомления
                Log::info("Sent overdue tasks notification to {$user->email}", [
                    'user_id' => $user->id,
                    'tasks_count' => $tasks->count()
                ]);
                
            } catch (\Exception $e) {
                $this->error("Failed to send notification to {$user->email}: " . $e->getMessage());
                Log::error("Failed to send overdue tasks notification", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }

    /**
     * Send notifications for upcoming deadline tasks
     */
    private function sendUpcomingDeadlineNotifications(): int
    {
        // Задачи с дедлайном в течение 24 часов
        $upcomingTasks = Task::with(['assignedToUser', 'document'])
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addHours(24))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $count = 0;
        $groupedTasks = $upcomingTasks->groupBy('assigned_to');

        foreach ($groupedTasks as $userId => $tasks) {
            $user = User::find($userId);
            if (!$user || !$user->email) {
                continue;
            }

            try {
                $this->sendUpcomingDeadlineEmail($user, $tasks);
                $count++;
                
                Log::info("Sent upcoming deadline notification to {$user->email}", [
                    'user_id' => $user->id,
                    'tasks_count' => $tasks->count()
                ]);
                
            } catch (\Exception $e) {
                $this->error("Failed to send notification to {$user->email}: " . $e->getMessage());
                Log::error("Failed to send upcoming deadline notification", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }

    /**
     * Send overdue tasks email
     */
    private function sendOverdueEmail(User $user, $tasks): void
    {
        $subject = 'Просроченные задачи в системе DocsFlow';
        
        $body = view('emails.overdue-tasks', [
            'user' => $user,
            'tasks' => $tasks
        ])->render();

        Mail::html($body, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)
                   ->subject($subject);
        });
    }

    /**
     * Send upcoming deadline tasks email
     */
    private function sendUpcomingDeadlineEmail(User $user, $tasks): void
    {
        $subject = 'Напоминание о приближающихся дедлайнах в DocsFlow';
        
        $body = view('emails.upcoming-deadline-tasks', [
            'user' => $user,
            'tasks' => $tasks
        ])->render();

        Mail::html($body, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)
                   ->subject($subject);
        });
    }
}
