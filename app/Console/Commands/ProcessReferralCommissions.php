<?php

namespace App\Console\Commands;

use App\Services\ReferralService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessReferralCommissions extends Command
{
    protected $signature = 'referral:process-commissions {--date=}';

    protected $description = 'Process and pay out pending referral commissions for a given date';

    public function handle(ReferralService $referralService): int
    {
        $date = $this->option('date') ?? date('Y-m-d');

        try {
            $this->info("Processing referral commissions for {$date}...");

            $result = $referralService->processDailyCommissions($date);

            if (! $result['success']) {
                $this->error("Failed: {$result['message']}");
                Log::error('Referral commission processing failed', ['message' => $result['message'], 'date' => $date]);

                return static::FAILURE;
            }

            $this->info("Processed {$result['processed']} commissions, total: {$result['total_amount']}");
            Log::info('Referral commissions processed', ['date' => $date, 'processed' => $result['processed'], 'total_amount' => $result['total_amount']]);

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Referral commission processing exception', ['date' => $date, 'error' => $e->getMessage()]);

            return static::FAILURE;
        }
    }
}
