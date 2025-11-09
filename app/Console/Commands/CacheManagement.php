<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:manage
                            {action : Action to perform: stats, flush, warm, test}
                            {--keys= : Specific cache keys pattern to work with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application cache: view stats, flush, warm up, test performance';

    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match($action) {
            'stats' => $this->showStats(),
            'flush' => $this->flushCache(),
            'warm' => $this->warmUpCache(),
            'test' => $this->testCache(),
            default => $this->showHelp(),
        };
    }

    /**
     * Показать статистику кеша
     */
    private function showStats(): int
    {
        $this->info('Cache Statistics:');
        $this->line('');

        $stats = $this->cacheService->getCacheStats();

        $this->table(['Metric', 'Value'], [
            ['Driver', $stats['driver']],
            ['Entries Count', number_format($stats['entries_count'])],
            ['Total Size', $stats['size_formatted']],
        ]);

        if (isset($stats['error'])) {
            $this->warn('Error getting detailed stats: ' . $stats['error']);
        }

        return 0;
    }

    /**
     * Очистить кеш
     */
    private function flushCache(): int
    {
        if (!$this->confirm('Are you sure you want to flush all cache?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Flushing cache...');
        
        $result = $this->cacheService->flush();
        
        if ($result) {
            $this->info('Cache flushed successfully!');
        } else {
            $this->error('Failed to flush cache.');
            return 1;
        }

        return 0;
    }

    /**
     * Прогрев кеша
     */
    private function warmUpCache(): int
    {
        $this->info('Warming up cache...');
        
        $bar = $this->output->createProgressBar(8);
        $bar->start();

        // Прогреваем основные данные
        $this->cacheService->getDepartmentsList();
        $bar->advance();

        $this->cacheService->getCategoriesList();
        $bar->advance();

        $this->cacheService->getUsersList(['active' => true]);
        $bar->advance();

        $this->cacheService->getDocumentsCount();
        $bar->advance();

        $this->cacheService->getDocumentsCount('pending');
        $bar->advance();

        $this->cacheService->getDocumentsCount('completed');
        $bar->advance();

        $this->cacheService->getAdminStats();
        $bar->advance();

        $this->cacheService->getAdminStats('week');
        $bar->advance();

        $bar->finish();
        $this->newLine();
        $this->info('Cache warmed up successfully!');

        return 0;
    }

    /**
     * Тестировать производительность кеша
     */
    private function testCache(): int
    {
        $this->info('Testing cache performance...');
        $iterations = 100;

        // Тест записи
        $writeStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::put("test_key_{$i}", "test_value_{$i}", 300);
        }
        $writeTime = microtime(true) - $writeStart;

        // Тест чтения
        $readStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::get("test_key_{$i}");
        }
        $readTime = microtime(true) - $readStart;

        // Очистка тестовых ключей
        for ($i = 0; $i < $iterations; $i++) {
            Cache::forget("test_key_{$i}");
        }

        $this->table(['Operation', 'Time (ms)', 'Operations/sec'], [
            ['Write (' . $iterations . ' keys)', round($writeTime * 1000, 2), round($iterations / $writeTime, 2)],
            ['Read (' . $iterations . ' keys)', round($readTime * 1000, 2), round($iterations / $readTime, 2)],
        ]);

        return 0;
    }

    /**
     * Показать помощь
     */
    private function showHelp(): int
    {
        $this->error('Invalid action specified.');
        $this->line('');
        $this->info('Available actions:');
        $this->line('  stats - Show cache statistics');
        $this->line('  flush - Clear all cache');
        $this->line('  warm  - Warm up cache with common data');
        $this->line('  test  - Test cache performance');
        $this->line('');
        $this->info('Examples:');
        $this->line('  php artisan cache:manage stats');
        $this->line('  php artisan cache:manage flush');
        $this->line('  php artisan cache:manage warm');

        return 1;
    }
}
