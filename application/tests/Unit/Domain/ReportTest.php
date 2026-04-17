<?php

namespace Tests\Unit\Domain;

use App\Domain\Report\Report;
use App\Domain\Report\ReportFormat;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $report = new Report('uuid-123', ReportFormat::Pdf, '%PDF-1.4 content', 'application/pdf');

        $this->assertSame('uuid-123', $report->uuid);
        $this->assertSame(ReportFormat::Pdf, $report->format);
        $this->assertSame('%PDF-1.4 content', $report->content);
        $this->assertSame('application/pdf', $report->mimeType);
    }
}
