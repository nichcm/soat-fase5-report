<?php

namespace App\Http\Controllers;

use App\Application\UseCases\GenerateReport\GenerateReportInput;
use App\Application\UseCases\GenerateReport\GenerateReportUseCase;
use App\Application\UseCases\GetAnalysisStatus\GetAnalysisStatusInput;
use App\Application\UseCases\GetAnalysisStatus\GetAnalysisStatusUseCase;
use App\Domain\Report\ReportFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ReportConnectionController
{
    public function __construct(
        private GetAnalysisStatusUseCase $getAnalysisStatusUseCase,
        private GenerateReportUseCase    $generateReportUseCase,
    ) {}

    public function getReportStatus(Request $request, string $protocol_uuid): \Illuminate\Http\JsonResponse
    {
        try {
            $output = $this->getAnalysisStatusUseCase->execute(
                new GetAnalysisStatusInput(protocolUuid: $protocol_uuid)
            );

            return response()->json([
                'err'          => false,
                'protocolUuid' => $output->protocolUuid,
                'status'       => $output->status->value,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['err' => true, 'msg' => $e->getMessage()], HttpResponse::HTTP_BAD_GATEWAY);
        }
    }

    public function getReport(Request $request, string $protocol_uuid): \Symfony\Component\HttpFoundation\Response
    {
        $format = ReportFormat::tryFrom($request->query('format', 'pdf')) ?? ReportFormat::Pdf;

        try {
            $output = $this->generateReportUseCase->execute(
                new GenerateReportInput(protocolUuid: $protocol_uuid, format: $format)
            );

            $report = $output->report;

            return response($report->content, HttpResponse::HTTP_OK, [
                'Content-Type'        => $report->mimeType,
                'Content-Disposition' => "attachment; filename=\"report-{$protocol_uuid}.pdf\"",
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['err' => true, 'Deu erro aqui!' => true, 'msg' => $e->getMessage()], HttpResponse::HTTP_BAD_GATEWAY);
        }
    }
}
