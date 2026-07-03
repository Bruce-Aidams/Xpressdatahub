<?php

namespace App\Services;

use App\Models\PasswordResetToken;

class PasswordResetService
{
    public function generateOTP(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function verifyToken(string $token, string $email): ?array
    {
        $record = PasswordResetToken::where('email', $email)
            ->where('used_at', null)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (! $record) {
            return null;
        }

        if ($record->attempts >= $record->max_attempts) {
            return null;
        }

        if (password_verify($token, $record->token_hash)) {
            $record->update(['used_at' => now()]);

            return ['valid' => true, 'token_id' => $record->id];
        }

        $record->increment('attempts');

        return null;
    }

    public function checkRateLimit(string $email, int $maxAttempts = 3, int $timeWindow = 3600): bool
    {
        $attemptCount = PasswordResetToken::where('email', $email)
            ->where('created_at', '>', now()->subSeconds($timeWindow))
            ->count();

        return $attemptCount < $maxAttempts;
    }

    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $strength = 0;

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $strength++;
        }

        if (! preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $strength++;
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $strength++;
        }

        if (! preg_match('/\d/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $strength++;
        }

        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        } else {
            $strength++;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $strength,
            'strength_level' => $this->getStrengthLevel($strength),
        ];
    }

    public function validateEmail(string $email): array
    {
        $errors = [];

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        $disposableDomains = [
            '10minutemail.com', 'tempmail.org', 'guerrillamail.com',
            'mailinator.com', 'throwaway.email', 'temp-mail.org',
        ];

        $domain = substr(strrchr($email, '@'), 1);
        if (in_array(strtolower($domain), $disposableDomains)) {
            $errors[] = 'Disposable email addresses are not allowed';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function cleanupTokens(): array
    {
        $expired = PasswordResetToken::where('expires_at', '<', now())->delete();
        $used = PasswordResetToken::where('used_at', '!=', null)
            ->where('created_at', '<', now()->subHour())
            ->delete();
        $maxAttempts = PasswordResetToken::whereColumn('attempts', '>=', 'max_attempts')->delete();

        return [
            'expired' => $expired,
            'used' => $used,
            'max_attempts' => $maxAttempts,
            'total' => $expired + $used + $maxAttempts,
        ];
    }

    private function getStrengthLevel(int $strength): string
    {
        if ($strength < 2) {
            return 'Very Weak';
        }
        if ($strength < 3) {
            return 'Weak';
        }
        if ($strength < 4) {
            return 'Fair';
        }
        if ($strength < 5) {
            return 'Good';
        }

        return 'Strong';
    }
}
