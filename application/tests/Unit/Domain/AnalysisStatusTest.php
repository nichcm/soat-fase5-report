<?php

namespace Tests\Unit\Domain;

use App\Domain\Analysis\AnalysisStatus;
use PHPUnit\Framework\TestCase;

class AnalysisStatusTest extends TestCase
{
    public function test_enum_values(): void
    {
        $this->assertSame('RECEBIDO', AnalysisStatus::Pending->value);
        $this->assertSame('EM_PROCESSAMENTO', AnalysisStatus::Running->value);
        $this->assertSame('SUCESSO', AnalysisStatus::Completed->value);
        $this->assertSame('ERRO', AnalysisStatus::Failed->value);
    }

    public function test_from_string(): void
    {
        $this->assertSame(AnalysisStatus::Pending, AnalysisStatus::from('RECEBIDO'));
        $this->assertSame(AnalysisStatus::Running, AnalysisStatus::from('EM_PROCESSAMENTO'));
        $this->assertSame(AnalysisStatus::Completed, AnalysisStatus::from('SUCESSO'));
        $this->assertSame(AnalysisStatus::Failed, AnalysisStatus::from('ERRO'));
    }

    public function test_try_from_returns_null_for_unknown_value(): void
    {
        $this->assertNull(AnalysisStatus::tryFrom('unknown'));
    }
}
