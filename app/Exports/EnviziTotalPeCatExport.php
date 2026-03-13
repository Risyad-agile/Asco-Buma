<?php

namespace App\Exports;

class EnviziTotalPeCatExport extends BaseCSRExportFormat5
{
    private const STYLE = 'CSR Employee - Total per cat';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
