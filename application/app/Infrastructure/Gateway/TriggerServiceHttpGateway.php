<?php

namespace App\Infrastructure\Gateway;

use App\Domain\Analysis\AnalysisData;
use App\Domain\Analysis\AnalysisStatus;
use App\Domain\Contracts\TriggerServiceGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TriggerServiceHttpGateway implements TriggerServiceGatewayInterface
{
    public function __construct(
        private string $baseUrl,
        private int    $timeoutSeconds = 10,
    ) {}

    public function fetchData(string $uuid): AnalysisData
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->get("{$this->baseUrl}/data/{$uuid}");

        if ($response->failed()) {
            throw new RuntimeException(
                "TriggerService fetchData failed for uuid={$uuid}: HTTP {$response->status()}"
            );
        }

        return new AnalysisData(
            protocolUuid: $uuid,
            payload: $response->json(),
        );
    }

    public function fetchStatus(string $uuid): AnalysisStatus
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->get("{$this->baseUrl}/status/{$uuid}");

        if ($response->failed()) {
            throw new RuntimeException(
                "TriggerService fetchStatus failed for uuid={$uuid}: HTTP {$response->status()}"
            );
        }

        $value = $response->json()['data']['status'];

        $status = AnalysisStatus::tryFrom($value);

        if ($status === null) {
            throw new RuntimeException("Unknown analysis status value: {$value}");
        }

        return $status;
    }
}
