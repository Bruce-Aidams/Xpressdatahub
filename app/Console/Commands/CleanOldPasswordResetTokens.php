<?php

namespace App\Console\Commands;

use App\Services\PasswordResetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanOldPasswordResetTokens extends Command
{
    protected $signature = 'tokens:clean-reset';

    protected $description = 'Delete expired, used, and max-attempted password reset tokens';

    public function handle(PasswordResetService $passwordResetService): int
    {
        try {
            $this->info('Cleaning password reset tokens...');

            $result = $passwordResetService->cleanupTokens();

            $this->info("Removed {$result['total']} tokens (expired: {$result['expired']}, used: {$result['used']}, max_attempts: {$result['max_attempts']})");
            Log::info('Password reset tokens cleaned', [
                'expired' => $result['expired'],
                'used' => $result['used'],
                'max_attempts' => $result['max_attempts'],
                'total' => $result['total'],
            ]);

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Password reset tokens cleanup exception', ['error' => $e->getMessage()]);

            return static::FAILURE;
        }
    }
}
