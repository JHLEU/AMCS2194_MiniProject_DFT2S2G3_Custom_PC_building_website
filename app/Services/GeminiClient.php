<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClient
{
    public function isConfigured(): bool
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model');

        return is_string($key) && trim($key) !== '' && is_string($model)
            && preg_match('/^gemini-[a-z0-9.-]+$/D', $model) === 1;
    }

    public function generate(array $request): array
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model');

        if (!$this->isConfigured()) {
            throw new RuntimeException('Shopping assistant configuration is unavailable.');
        }

        $client = Http::asJson()
            ->acceptJson()
            ->withHeader('x-goog-api-key', $key)
            ->connectTimeout(4)
            ->timeout(12);

        if (PHP_OS_FAMILY === 'Windows' && defined('CURLSSLOPT_NATIVE_CA')) {
            $client->withOptions(['curl' => [CURLOPT_SSL_OPTIONS => CURLSSLOPT_NATIVE_CA]]);
        }

        $response = $client->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
            $request
        );

        if (!$response->successful() || !is_array($response->json())) {
            throw new RuntimeException('Shopping assistant provider is unavailable.');
        }

        return $response->json();
    }
}
