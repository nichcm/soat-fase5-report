<?php

namespace App\Infrastructure\Renderer;

use App\Domain\Analysis\AnalysisData;
use App\Domain\Contracts\ReportRendererInterface;
use App\Domain\Report\Report;
use App\Domain\Report\ReportFormat;
use Barryvdh\DomPDF\Facade\Pdf;
use Ramsey\Uuid\Uuid;

class DompdfReportRenderer implements ReportRendererInterface
{
    public function supports(ReportFormat $format): bool
    {
        return $format === ReportFormat::Pdf;
    }

    public function render(AnalysisData $data): Report
    {
        $pdf = Pdf::loadView('reports.analysis', ['data' => $data->payload]);

        return new Report(
            uuid: (string) \Illuminate\Support\Str::uuid(),
            format: ReportFormat::Pdf,
            content: $pdf->output(),
            mimeType: 'application/pdf',
        );
    }
}
