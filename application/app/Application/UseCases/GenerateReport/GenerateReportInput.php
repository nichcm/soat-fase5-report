<?php

namespace App\Application\UseCases\GenerateReport;

use App\Domain\Report\ReportFormat;

readonly class GenerateReportInput
{
    public function __construct(
        public string       $protocolUuid,
        public ReportFormat $format = ReportFormat::Pdf,
    ) {}
}
