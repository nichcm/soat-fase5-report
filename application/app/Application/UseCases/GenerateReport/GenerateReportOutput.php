<?php

namespace App\Application\UseCases\GenerateReport;

use App\Domain\Report\Report;

readonly class GenerateReportOutput
{
    public function __construct(
        public Report $report,
    ) {}
}
