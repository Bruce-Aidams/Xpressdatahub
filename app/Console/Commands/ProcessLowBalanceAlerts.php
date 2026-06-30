<?php

namespace App\Console\Commands;

use App\Services\LowBalanceAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessLowBalanceAlerts extends Command
{
    protected $signature = 'alerts:low-balance';

    protected $description = 'Send low balance alerts to users whose balance is below the configured threshold';

    public function handle(LowBalanceAlertService $alertService): int
    {
        try {
            $this->info('Processing low balance alerts...');

            $result = $alertService->processAlerts();

            $this->info("Total: {$result['total']}, Sent: {$result['sent']}, Failed: {$result['failed']}");
            Log::info('Low balance alerts processed', [
                'total' => $result['total'],
                'sent' => $result['sent'],
                'failed' => $result['failed'],
            ]);

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Low balance alert processing exception', ['error' => $e->getMessage()]);
            return static::FAILURE;
        }
    }
}
