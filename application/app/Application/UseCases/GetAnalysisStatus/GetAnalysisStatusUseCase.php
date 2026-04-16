<?php

namespace App\Application\UseCases\GetAnalysisStatus;

use App\Domain\Contracts\TriggerServiceGatewayInterface;

class GetAnalysisStatusUseCase
{
    public function __construct(
        private TriggerServiceGatewayInterface $gateway,
    ) {}

    public function execute(GetAnalysisStatusInput $input): GetAnalysisStatusOutput
    {
        $status = $this->gateway->fetchStatus($input->protocolUuid);

        return new GetAnalysisStatusOutput(
            protocolUuid: $input->protocolUuid,
            status: $status,
        );
    }
}
