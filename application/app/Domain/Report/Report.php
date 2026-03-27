<?php

namespace App\Domain\Report;

readonly class Report
{
    public function __construct(
        public string       $uuid,
        public ReportFormat $format,
        public string       $content,
        public string       $mimeType,
    ) {}
}
