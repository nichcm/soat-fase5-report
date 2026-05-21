<?php

namespace Tests\Feature;

use App\Domain\Analysis\AnalysisData;
use App\Domain\Analysis\AnalysisStatus;
use App\Domain\Contracts\TriggerServiceGatewayInterface;
use App\Domain\Report\Report;
use App\Domain\Report\ReportFormat;
use App\Infrastructure\Renderer\DompdfReportRenderer;
use RuntimeException;
use Tests\TestCase;

class ReportConnectionControllerTest extends TestCase
{
    private const UUID = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    public function test_ping_returns_pong(): void
    {
        $this->getJson('/api/ping')
            ->assertOk()
            ->assertJson(['err' => false, 'msg' => 'pong']);
    }

    /** @dataProvider analysisStatusProvider */
    public function test_get_status_returns_correct_status_value(
        AnalysisStatus $status,
        string $expectedValue
    ): void {
        $this->mock(TriggerServiceGatewayInterface::class)
            ->shouldReceive('fetchStatus')
            ->with(self::UUID)
            ->once()
            ->andReturn($status);

        $this->getJson('/api/status/' . self::UUID)
            ->assertOk()
            ->assertJson([
                'err'          => false,
                'protocolUuid' => self::UUID,
                'status'       => $expectedValue,
            ]);
    }

    public static function analysisStatusProvider(): array
    {
        return [
            'pending'   => [AnalysisStatus::Pending, 'RECEBIDO'],
            'running'   => [AnalysisStatus::Running, 'EM_PROCESSAMENTO'],
            'completed' => [AnalysisStatus::Completed, 'SUCESSO'],
            'failed'    => [AnalysisStatus::Failed, 'ERRO'],
        ];
    }

    public function test_get_status_returns_502_when_gateway_throws(): void
    {
        $this->mock(TriggerServiceGatewayInterface::class)
            ->shouldReceive('fetchStatus')
            ->once()
            ->andThrow(new RuntimeException('Service unavailable'));

        $this->getJson('/api/status/' . self::UUID)
            ->assertStatus(502)
            ->assertJson(['err' => true, 'msg' => 'Service unavailable']);
    }

    public function test_get_report_returns_pdf_with_correct_headers(): void
    {
        $analysisData = new AnalysisData(protocolUuid: self::UUID, payload: ['key' => 'value']);
        $report       = new Report(self::UUID, ReportFormat::Pdf, '%PDF-1.4 fake content', 'application/pdf');

        $this->mock(TriggerServiceGatewayInterface::class)
            ->shouldReceive('fetchData')
            ->with(self::UUID)
            ->once()
            ->andReturn($analysisData);

        $renderer = $this->mock(DompdfReportRenderer::class);
        $renderer->shouldReceive('supports')->with(ReportFormat::Pdf)->once()->andReturn(true);
        $renderer->shouldReceive('render')->with($analysisData)->once()->andReturn($report);

        $this->get('/api/report/' . self::UUID)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="report-' . self::UUID . '.pdf"')
            ->assertContent('%PDF-1.4 fake content');
    }

    public function test_get_report_with_explicit_pdf_format_query_param(): void
    {
        $analysisData = new AnalysisData(protocolUuid: self::UUID, payload: []);
        $report       = new Report(self::UUID, ReportFormat::Pdf, 'pdf-bytes', 'application/pdf');

        $this->mock(TriggerServiceGatewayInterface::class)
            ->shouldReceive('fetchData')->once()->andReturn($analysisData);

        $renderer = $this->mock(DompdfReportRenderer::class);
        $renderer->shouldReceive('supports')->once()->andReturn(true);
        $renderer->shouldReceive('render')->once()->andReturn($report);

        $this->get('/api/report/' . self::UUID . '?format=pdf')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_get_report_with_unknown_format_falls_back_to_pdf(): void
    {
        $analysisData = new AnalysisData(protocolUuid: self::UUID, payload: []);
        $report       = new Report(self::UUID, ReportFormat::Pdf, 'pdf-bytes', 'application/pdf');

        $this->mock(TriggerServiceGatewayInterface::class)
            ->shouldReceive('fetchData')->once()->andReturn($analysisData);

        $renderer = $this->mock(DompdfReportRenderer::class);
        $renderer->shouldReceive('supports')->once()->andReturn(true);
        $renderer->shouldReceive('render')->once()->andReturn($report);

        $this->get('/api/report/' . self::UUID . '?format=unknown')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_get_report_returns_502_when_gateway_throws(): void
    {
        $this->mock(TriggerServiceGatewayInterface::class)
            ->shouldReceive('fetchData')
            ->once()
            ->andThrow(new RuntimeException('Gateway error'));

        $this->getJson('/api/report/' . self::UUID)
            ->assertStatus(502)
            ->assertJson(['err' => true, 'msg' => 'Gateway error']);
    }

    public function test_fallback_route_returns_not_found_with_error_json(): void
    {
        $this->getJson('/api/nonexistent/route')
            ->assertNotFound()
            ->assertJson(['err' => true, 'msg' => 'Recurso não encontrado']);
    }
}
