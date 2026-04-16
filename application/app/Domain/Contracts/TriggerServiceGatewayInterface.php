<?php

namespace App\Domain\Contracts;

use App\Domain\Analysis\AnalysisData;
use App\Domain\Analysis\AnalysisStatus;

interface TriggerServiceGatewayInterface
{
    public function fetchData(string $uuid): AnalysisData;

    public function fetchStatus(string $uuid): AnalysisStatus;
}
