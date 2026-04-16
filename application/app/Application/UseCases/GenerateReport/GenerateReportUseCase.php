<?php

namespace App\Application\UseCases\GenerateReport;

use App\Domain\Contracts\ReportRendererInterface;
use App\Domain\Contracts\TriggerServiceGatewayInterface;
use RuntimeException;

class GenerateReportUseCase
{
    /** @param ReportRendererInterface[] $renderers */
    public function __construct(
        private TriggerServiceGatewayInterface $gateway,
        private array                          $renderers,
    ) {}

    public function execute(GenerateReportInput $input): GenerateReportOutput
    {
        $data = $this->gateway->fetchData($input->protocolUuid);

        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($input->format)) {
                return new GenerateReportOutput($renderer->render($data));
            }
        }

        throw new RuntimeException("No renderer supports format: {$input->format->value}");
    }
}
