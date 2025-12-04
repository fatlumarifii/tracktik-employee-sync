<?php

namespace App\Services;

use App\Exceptions\TrackTikApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackTikApiService
{
    protected string $clientId;

    protected string $clientSecret;

    protected string $tokenUrl;

    protected string $apiBaseUrl;

    protected string $scope;

    public function __construct()
    {
        $this->clientId = config('tracktik.client_id');
        $this->clientSecret = config('tracktik.client_secret');
        $this->tokenUrl = config('tracktik.token_url');
        $this->apiBaseUrl = config('tracktik.api_base_url');
        $this->scope = config('tracktik.scope');
    }

    public function getAccessToken(): string
    {
        $cacheKey = config('tracktik.token_cache_key');
        $cacheTtl = config('tracktik.token_cache_ttl');

        return Cache::remember($cacheKey, $cacheTtl, function () {
            Log::info('Requesting new TrackTik OAuth2 token');

            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $this->scope,
            ]);

            if ($response->failed()) {
                Log::error('Failed to get TrackTik OAuth2 token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new TrackTikApiException('Failed to authenticate with TrackTik API');
            }

            $data = $response->json();

            return $data['access_token'];
        });
    }

    public function createEmployee(array $employeeData): array
    {
        $token = $this->getAccessToken();

        Log::info('Creating employee in TrackTik', ['data' => $employeeData]);

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->apiBaseUrl}/employees", $employeeData);

        if ($response->status() === 401) {
            Log::warning('Received 401 from TrackTik API, refreshing token and retrying');

            Cache::forget(config('tracktik.token_cache_key'));
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiBaseUrl}/employees", $employeeData);
        }

        if ($response->failed()) {
            Log::error('Failed to create employee in TrackTik', [
                'status' => $response->status(),
                'body' => $response->body(),
                'employee_data' => $employeeData,
            ]);

            throw new TrackTikApiException(
                'Failed to create employee in TrackTik',
                $response->status()
            );
        }

        $result = $response->json();

        Log::info('Employee created successfully in TrackTik', ['response' => $result]);

        return $result;
    }
}
