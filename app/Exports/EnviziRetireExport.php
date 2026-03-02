<?php

namespace App\Exports;

class EnviziRetireExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Turnover - Retirement';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}