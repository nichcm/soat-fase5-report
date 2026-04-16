<?php

namespace App\Application\UseCases\GetAnalysisStatus;

use App\Domain\Analysis\AnalysisStatus;

readonly class GetAnalysisStatusOutput
{
    public function __construct(
        public string         $protocolUuid,
        public AnalysisStatus $status,
    ) {}
}
