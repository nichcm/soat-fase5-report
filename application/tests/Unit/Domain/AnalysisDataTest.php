<?php

namespace Tests\Unit\Domain;

use App\Domain\Analysis\AnalysisData;
use PHPUnit\Framework\TestCase;

class AnalysisDataTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $payload = ['key' => 'value', 'nested' => [1, 2, 3]];
        $data    = new AnalysisData('uuid-123', $payload);

        $this->assertSame('uuid-123', $data->protocolUuid);
        $this->assertSame($payload, $data->payload);
    }

    public function test_accepts_empty_payload(): void
    {
        $data = new AnalysisData('uuid-456', []);

        $this->assertSame([], $data->payload);
    }
}
