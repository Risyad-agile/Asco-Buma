<?php

namespace App\Exports;
 

class EnviziResignmentExport extends BaseCSRExportTurnOverFormat
{
    private const STYLE = 'CSR Employee - Turnover - Resignation';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
