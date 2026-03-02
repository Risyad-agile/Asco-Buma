<?php

namespace App\Exports;
 

class EnviziResignmentExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Turnover - Resignation';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
