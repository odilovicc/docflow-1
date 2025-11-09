<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArchiveOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:archive
                            {--model= : Model to archive (documents, tasks, all)}
                            {--days=365 : Archive records older than X days}
                            {--dry-run : Show what would be archived without actually archiving}
                            {--batch-size=100 : Number of records to process at once}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old documents and tasks to improve database performance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->option('model') ?? 'all';
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $force = $this->option('force');

        if ($days <= 0) {
            $this->error('Days must be a positive number');
            return 1;
        }

        $cutoffDate = Carbon::now()->subDays($days);
        $this->info("Archiving data older than: {$cutoffDate->format('Y-m-d H:i:s')}");

        $results = [];

        // Архивируем в зависимости от выбранной модели
        switch ($model) {
            case 'documents':
                $results['documents'] = $this->archiveDocuments($cutoffDate, $dryRun, $batchSize, $force);
                break;
            case 'tasks':
                $results['tasks'] = $this->archiveTasks($cutoffDate, $dryRun, $batchSize, $force);
                break;
            case 'all':
                $results['documents'] = $this->archiveDocuments($cutoffDate, $dryRun, $batchSize, $force);
                $results['tasks'] = $this->archiveTasks($cutoffDate, $dryRun, $batchSize, $force);
                break;
            default:
                $this->error('Invalid model specified. Use: documents, tasks, or all');
                return 1;
        }

        // Показываем итоги
        $this->showSummary($results, $dryRun);

        return 0;
    }

    /**
     * Архивировать старые документы
     */
    private function archiveDocuments(Carbon $cutoffDate, bool $dryRun, int $batchSize, bool $force): array
    {
        $this->info('Processing documents...');

        // Находим документы для архивирования (только завершенные/отмененные)
        $query = Document::where('updated_at', '<', $cutoffDate)
            ->whereIn('status', ['completed', 'cancelled']);

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No documents found for archiving');
            return ['total' => 0, 'archived' => 0, 'errors' => 0];
        }

        $this->info("Found {$totalCount} documents to archive");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No documents will be actually archived');
            return ['total' => $totalCount, 'archived' => 0, 'errors' => 0];
        }

        if (!$force && !$this->confirm("Do you want to archive {$totalCount} documents?")) {
            $this->info('Operation cancelled');
            return ['total' => $totalCount, 'archived' => 0, 'errors' => 0];
        }

        return $this->processArchiving('documents', $query, $batchSize);
    }

    /**
     * Архивировать старые задачи
     */
    private function archiveTasks(Carbon $cutoffDate, bool $dryRun, int $batchSize, bool $force): array
    {
        $this->info('Processing tasks...');

        // Находим задачи для архивирования (только завершенные/отмененные)
        $query = Task::where('updated_at', '<', $cutoffDate)
            ->whereIn('status', ['completed', 'cancelled']);

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No tasks found for archiving');
            return ['total' => 0, 'archived' => 0, 'errors' => 0];
        }

        $this->info("Found {$totalCount} tasks to archive");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No tasks will be actually archived');
            return ['total' => $totalCount, 'archived' => 0, 'errors' => 0];
        }

        if (!$force && !$this->confirm("Do you want to archive {$totalCount} tasks?")) {
            $this->info('Operation cancelled');
            return ['total' => $totalCount, 'archived' => 0, 'errors' => 0];
        }

        return $this->processArchiving('tasks', $query, $batchSize);
    }

    /**
     * Процесс архивирования с батчевой обработкой
     */
    private function processArchiving(string $tableName, $query, int $batchSize): array
    {
        $archived = 0;
        $errors = 0;
        $totalCount = $query->count();

        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        DB::beginTransaction();

        try {
            // Создаем архивную таблицу если не существует
            $this->createArchiveTable($tableName);

            // Обрабатываем батчами
            $query->chunk($batchSize, function ($records) use (&$archived, &$errors, $progressBar, $tableName) {
                foreach ($records as $record) {
                    try {
                        // Копируем в архивную таблицу
                        $this->copyToArchive($tableName, $record);
                        
                        // Удаляем из основной таблицы
                        $record->delete();
                        
                        $archived++;
                    } catch (\Exception $e) {
                        $this->error("Error archiving record {$record->id}: " . $e->getMessage());
                        $errors++;
                    }
                    
                    $progressBar->advance();
                }
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Transaction failed: ' . $e->getMessage());
            $errors = $totalCount;
        }

        $progressBar->finish();
        $this->newLine();

        return ['total' => $totalCount, 'archived' => $archived, 'errors' => $errors];
    }

    /**
     * Создать архивную таблицу
     */
    private function createArchiveTable(string $tableName): void
    {
        $archiveTableName = "archived_{$tableName}";
        
        if (!DB::getSchemaBuilder()->hasTable($archiveTableName)) {
            $this->info("Creating archive table: {$archiveTableName}");
            
            // Создаем таблицу на основе структуры оригинальной таблицы
            DB::statement("CREATE TABLE {$archiveTableName} AS SELECT * FROM {$tableName} WHERE 1=0");
            
            // Добавляем дополнительные поля для архива
            DB::statement("ALTER TABLE {$archiveTableName} ADD COLUMN archived_at DATETIME DEFAULT CURRENT_TIMESTAMP");
            DB::statement("ALTER TABLE {$archiveTableName} ADD COLUMN archived_by VARCHAR(255)");
            
            // Создаем индексы
            DB::statement("CREATE INDEX idx_{$archiveTableName}_archived_at ON {$archiveTableName}(archived_at)");
            DB::statement("CREATE INDEX idx_{$archiveTableName}_original_id ON {$archiveTableName}(id)");
        }
    }

    /**
     * Копировать запись в архивную таблицу
     */
    private function copyToArchive(string $tableName, $record): void
    {
        $archiveTableName = "archived_{$tableName}";
        
        $data = $record->toArray();
        $data['archived_at'] = now();
        $data['archived_by'] = 'system';

        DB::table($archiveTableName)->insert($data);
    }

    /**
     * Показать итоговую статистику
     */
    private function showSummary(array $results, bool $dryRun): void
    {
        $this->newLine();
        $this->info('=== ARCHIVING SUMMARY ===');

        $totalRecords = 0;
        $totalArchived = 0;
        $totalErrors = 0;

        foreach ($results as $model => $stats) {
            $totalRecords += $stats['total'];
            $totalArchived += $stats['archived'];
            $totalErrors += $stats['errors'];

            $this->line(sprintf(
                '%s: %d total, %d archived, %d errors',
                ucfirst($model),
                $stats['total'],
                $stats['archived'],
                $stats['errors']
            ));
        }

        $this->newLine();
        $this->line(sprintf(
            'TOTAL: %d records processed, %d archived, %d errors',
            $totalRecords,
            $totalArchived,
            $totalErrors
        ));

        if ($dryRun) {
            $this->warn('This was a DRY RUN - no data was actually archived');
        } elseif ($totalErrors > 0) {
            $this->error("Archiving completed with {$totalErrors} errors");
        } else {
            $this->info('Archiving completed successfully!');
        }
    }
}
