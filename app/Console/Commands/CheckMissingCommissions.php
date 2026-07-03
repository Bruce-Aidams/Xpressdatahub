<?php

namespace App\Console\Commands;

use App\Services\ReferralService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckMissingCommissions extends Command
{
    protected $signature = 'referral:check-missing {--limit=50}';

    protected $description = 'Find and backfill delivered orders that are missing referral commissions';

    public function handle(ReferralService $referralService): int
    {
        $limit = (int) $this->option('limit');

        try {
            $this->info("Checking for missing commissions (limit: {$limit})...");

            $result = $referralService->processMissingCommissions($limit);

            $this->info("Processed: {$result['processed']}, Failed: {$result['failed']}");
            Log::info('Missing commissions check', [
                'processed' => $result['processed'],
                'failed' => $result['failed'],
                'message' => $result['message'],
            ]);

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Missing commissions check exception', ['error' => $e->getMessage()]);

            return static::FAILURE;
        }
    }
}
