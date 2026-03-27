<?php

namespace App\Providers;

use App\Application\UseCases\GenerateReport\GenerateReportUseCase;
use App\Application\UseCases\GetAnalysisStatus\GetAnalysisStatusUseCase;
use App\Domain\Contracts\TriggerServiceGatewayInterface;
use App\Infrastructure\Gateway\TriggerServiceHttpGateway;
use App\Infrastructure\Renderer\DompdfReportRenderer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TriggerServiceGatewayInterface::class, fn () =>
            new TriggerServiceHttpGateway(
                baseUrl: config('services.trigger_service.base_url'),
                timeoutSeconds: (int) config('services.trigger_service.timeout', 10),
            )
        );

        $this->app->bind(GetAnalysisStatusUseCase::class, fn ($app) =>
            new GetAnalysisStatusUseCase(
                gateway: $app->make(TriggerServiceGatewayInterface::class),
            )
        );

        $this->app->bind(GenerateReportUseCase::class, fn ($app) =>
            new GenerateReportUseCase(
                gateway: $app->make(TriggerServiceGatewayInterface::class),
                renderers: [$app->make(DompdfReportRenderer::class)],
            )
        );
    }

    public function boot(): void {}
}
