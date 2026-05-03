<?php

namespace App\Domain\Analysis;

enum AnalysisStatus: string
{
    case Pending   = 'RECEBIDO';
    case Running   = 'EM_PROCESSAMENTO';
    case Completed = 'SUCESSO';
    case Failed    = 'ERRO';

    // public const STATUS_RECEBIDO           = 'RECEBIDO';
    // public const STATUS_EM_PROCESSAMENTO   = 'EM_PROCESSAMENTO';
    // public const STATUS_ERRO               = 'ERRO';
    // public const STATUS_SUCESSO            = 'SUCESSO';
    // public const STATUS_ERRO_PROCESSAMENTO = 'ERRO_PROCESSAMENTO';
}
