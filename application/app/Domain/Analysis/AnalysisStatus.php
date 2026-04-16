<?php

namespace App\Domain\Analysis;

enum AnalysisStatus: string
{
    case Pending   = 'pending';
    case Running   = 'running';
    case Completed = 'completed';
    case Failed    = 'failed';
}
