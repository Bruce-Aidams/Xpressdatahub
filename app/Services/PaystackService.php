<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private static function getClient(): Client
    {
        $secretKey = config('paystack.secret_key');
        $baseUrl = config('paystack.base_url', 'https://api.paystack.co');

        return new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
            'verify' => config('app.env') !== 'local',
        ]);
    }

    public static function verifyTransaction(string $reference): array
    {
        try {
            $client = self::getClient();
            $response = $client->get("/transaction/verify/{$reference}");
            $body = json_decode((string) $response->getBody(), true);

            return [
                'success' => $body['status'] ?? false,
                'data' => $body['data'] ?? null,
                'message' => $body['message'] ?? null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Paystack transaction verification failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to verify transaction: ' . $e->getMessage(),
            ];
        }
    }

    public static function initializeTransaction(array $data): array
    {
        try {
            $client = self::getClient();
            $response = $client->post('/transaction/initialize', [
                'json' => $data,
            ]);
            $body = json_decode((string) $response->getBody(), true);

            return [
                'success' => $body['status'] ?? false,
                'data' => $body['data'] ?? null,
                'message' => $body['message'] ?? null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Paystack transaction initialization failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to initialize transaction: ' . $e->getMessage(),
            ];
        }
    }

    public static function getRecentTransactions(int $page = 1, int $perPage = 50): array
    {
        try {
            $client = self::getClient();
            $response = $client->get('/transaction', [
                'query' => [
                    'page' => $page,
                    'perPage' => $perPage,
                ],
            ]);
            $body = json_decode((string) $response->getBody(), true);

            return [
                'success' => $body['status'] ?? false,
                'data' => $body['data'] ?? [],
                'meta' => $body['meta'] ?? null,
                'message' => $body['message'] ?? null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Paystack fetch recent transactions failed', [
                'page' => $page,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'meta' => null,
                'message' => 'Failed to fetch transactions: ' . $e->getMessage(),
            ];
        }
    }
}
