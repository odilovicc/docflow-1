<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class CleanupActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup
                            {--days=90 : Number of days to keep logs}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--type= : Specific log type to clean up}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old activity logs to maintain database performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $type = $this->option('type');

        if ($days <= 0) {
            $this->error('Days must be a positive number');
            return 1;
        }

        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Starting activity logs cleanup...");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        // Строим запрос
        $query = Activity::where('created_at', '<', $cutoffDate);
        
        if ($type) {
            $query->where('log_name', $type);
            $this->info("Filtering by log type: {$type}");
        }

        // Показываем статистику что будет удалено
        $totalCount = $query->count();
        
        if ($totalCount === 0) {
            $this->info('No logs found to cleanup.');
            return 0;
        }

        // Группируем по типам логов
        $logTypes = Activity::where('created_at', '<', $cutoffDate)
            ->when($type, function($q) use ($type) {
                return $q->where('log_name', $type);
            })
            ->selectRaw('log_name, count(*) as count')
            ->groupBy('log_name')
            ->orderBy('count', 'desc')
            ->get();

        $this->info("Found {$totalCount} logs to cleanup:");
        
        $headers = ['Log Type', 'Count'];
        $rows = [];
        
        foreach ($logTypes as $logType) {
            $rows[] = [
                $logType->log_name ?: 'default',
                number_format($logType->count)
            ];
        }
        
        $this->table($headers, $rows);

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No logs will be actually deleted');
            return 0;
        }

        // Подтверждение удаления
        if (!$this->confirm("Do you want to delete {$totalCount} activity logs older than {$days} days?")) {
            $this->info('Operation cancelled');
            return 0;
        }

        // Удаляем логи батчами для производительности
        $batchSize = 1000;
        $deleted = 0;
        
        $this->info('Deleting logs in batches...');
        
        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        do {
            $batchIds = Activity::where('created_at', '<', $cutoffDate)
                ->when($type, function($q) use ($type) {
                    return $q->where('log_name', $type);
                })
                ->limit($batchSize)
                ->pluck('id');
            
            if ($batchIds->isEmpty()) {
                break;
            }

            $deletedInBatch = Activity::whereIn('id', $batchIds)->delete();
            $deleted += $deletedInBatch;
            
            for ($i = 0; $i < $deletedInBatch; $i++) {
                $progressBar->advance();
            }

            // Небольшая пауза между батчами
            usleep(100000); // 0.1 секунды

        } while (!$batchIds->isEmpty());

        $progressBar->finish();
        $this->newLine();

        $this->info("Successfully deleted {$deleted} activity logs");
        
        // Показываем статистику после очистки
        $remaining = Activity::count();
        $this->info("Remaining activity logs: {$remaining}");

        return 0;
    }
}
