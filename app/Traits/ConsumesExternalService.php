<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait ConsumesExternalService
{
    public function performRequest(
        string $method,
        string $url,
        array $queryParams = [],
        array $bodyParams = [],
        array $headers = []
    ): array {

        $request = Http::timeout(10)
            ->withHeaders($headers);

        // GET requests
        if (strtoupper($method) === 'GET') {

            $response = $request->get($url, $queryParams);

        } else {

            // Non-GET requests
            $response = $request->send($method, $url, [
                'json' => $bodyParams,
            ]);
        }

        if ($response->failed()) {

            throw new \Exception(
                'External API request failed: ' . $response->body()
            );
        }

        return $response->json();
    }
}