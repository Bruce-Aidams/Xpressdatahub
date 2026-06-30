<?php

namespace App\Console\Commands;

use App\Services\UserLoginTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanOldLoginLogs extends Command
{
    protected $signature = 'logs:clean-login {--days=90}';

    protected $description = 'Delete login logs older than the specified number of days';

    public function handle(UserLoginTracker $tracker): int
    {
        $days = (int) $this->option('days');

        try {
            $this->info("Cleaning login logs older than {$days} days...");

            $result = $tracker->cleanOldLogs($days);

            $this->info("Deleted {$result['deleted_count']} records");
            Log::info('Login logs cleaned', ['days' => $days, 'deleted_count' => $result['deleted_count']]);

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Login logs cleanup exception', ['days' => $days, 'error' => $e->getMessage()]);
            return static::FAILURE;
        }
    }
}
