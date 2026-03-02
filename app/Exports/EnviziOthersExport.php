<?php

namespace App\Exports;

class EnviziOthersExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Turnover - Others';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}