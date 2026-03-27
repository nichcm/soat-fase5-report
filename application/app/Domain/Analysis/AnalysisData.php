<?php

namespace App\Domain\Analysis;

readonly class AnalysisData
{
    public function __construct(
        public string $protocolUuid,
        public array  $payload,
    ) {}
}
