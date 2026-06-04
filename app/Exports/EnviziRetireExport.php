<?php

namespace App\Exports;

class EnviziRetireExport extends BaseCSRExportTurnOverFormat
{
    private const STYLE = 'CSR Employee - Turnover - Retirement';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}