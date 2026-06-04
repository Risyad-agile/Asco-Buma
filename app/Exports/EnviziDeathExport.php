<?php

namespace App\Exports;

class EnviziDeathExport extends BaseCSRExportTurnOverFormat
{
    private const STYLE = 'CSR Employee - Turnover - Death';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}