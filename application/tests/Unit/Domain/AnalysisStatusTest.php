<?php

namespace Tests\Unit\Domain;

use App\Domain\Analysis\AnalysisStatus;
use PHPUnit\Framework\TestCase;

class AnalysisStatusTest extends TestCase
{
    public function test_enum_values(): void
    {
        $this->assertSame('pending', AnalysisStatus::Pending->value);
        $this->assertSame('running', AnalysisStatus::Running->value);
        $this->assertSame('completed', AnalysisStatus::Completed->value);
        $this->assertSame('failed', AnalysisStatus::Failed->value);
    }

    public function test_from_string(): void
    {
        $this->assertSame(AnalysisStatus::Pending, AnalysisStatus::from('pending'));
        $this->assertSame(AnalysisStatus::Running, AnalysisStatus::from('running'));
        $this->assertSame(AnalysisStatus::Completed, AnalysisStatus::from('completed'));
        $this->assertSame(AnalysisStatus::Failed, AnalysisStatus::from('failed'));
    }

    public function test_try_from_returns_null_for_unknown_value(): void
    {
        $this->assertNull(AnalysisStatus::tryFrom('unknown'));
    }
}
