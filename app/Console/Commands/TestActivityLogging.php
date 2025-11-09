<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Document;
use Spatie\Activitylog\Models\Activity;

class TestActivityLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:activity-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test activity logging system functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing activity logging system...');

        $user = User::first();
        if (!$user) {
            $this->error('No users found! Please run seeders first.');
            return 1;
        }

        $this->info("Creating test activity logs for user: {$user->name}");

        // Создаем несколько тестовых записей
        activity()
            ->causedBy($user)
            ->log('User logged in via test script');

        activity('auth')
            ->causedBy($user)
            ->withProperties(['ip' => '127.0.0.1', 'user_agent' => 'Test Script'])
            ->log('Test authentication activity');

        activity('document')
            ->causedBy($user)
            ->withProperties(['action' => 'view', 'module' => 'documents'])
            ->log('Viewed documents list');

        // Создаем активность для документа если есть
        $document = Document::first();
        if ($document) {
            activity('document')
                ->causedBy($user)
                ->performedOn($document)
                ->withProperties(['changes' => ['title' => 'Updated title']])
                ->log('Document updated');
            
            $this->info("Created activity log for document: {$document->title}");
        }

        // Тестируем различные события
        activity('web')
            ->causedBy($user)
            ->withProperties([
                'ip' => '192.168.1.100',
                'method' => 'GET', 
                'url' => '/documents',
                'user_agent' => 'Mozilla/5.0 (Test Browser)'
            ])
            ->log('HTTP request logged');

        activity('admin')
            ->causedBy($user)
            ->withProperties(['action' => 'view_dashboard'])
            ->log('Admin dashboard accessed');

        $totalLogs = Activity::count();
        $this->info("Total activity logs in database: {$totalLogs}");

        $userLogs = Activity::where('causer_id', $user->id)->count();
        $this->info("Logs for user {$user->name}: {$userLogs}");

        // Показываем последние 5 логов
        $this->info('Recent activity logs:');
        $recentLogs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $headers = ['ID', 'Date', 'User', 'Event', 'Description', 'Type'];
        $rows = [];

        foreach ($recentLogs as $log) {
            $rows[] = [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->causer ? $log->causer->name : 'System',
                $log->event ?: 'N/A',
                substr($log->description, 0, 50) . (strlen($log->description) > 50 ? '...' : ''),
                $log->log_name ?: 'default',
            ];
        }

        $this->table($headers, $rows);

        $this->info('Activity logging test completed successfully!');
        
        return 0;
    }
}
