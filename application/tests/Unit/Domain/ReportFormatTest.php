<?php

namespace Tests\Unit\Domain;

use App\Domain\Report\ReportFormat;
use PHPUnit\Framework\TestCase;

class ReportFormatTest extends TestCase
{
    public function test_pdf_enum_value(): void
    {
        $this->assertSame('pdf', ReportFormat::Pdf->value);
    }

    public function test_from_string(): void
    {
        $this->assertSame(ReportFormat::Pdf, ReportFormat::from('pdf'));
    }

    public function test_try_from_returns_null_for_unknown_value(): void
    {
        $this->assertNull(ReportFormat::tryFrom('docx'));
    }
}
