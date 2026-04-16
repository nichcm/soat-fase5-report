<?php

namespace App\Application\UseCases\GetAnalysisStatus;

readonly class GetAnalysisStatusInput
{
    public function __construct(
        public string $protocolUuid,
    ) {}
}
