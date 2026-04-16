<?php

namespace App\Domain\Contracts;

use App\Domain\Analysis\AnalysisData;
use App\Domain\Report\Report;
use App\Domain\Report\ReportFormat;

interface ReportRendererInterface
{
    public function supports(ReportFormat $format): bool;

    public function render(AnalysisData $data): Report;
}
