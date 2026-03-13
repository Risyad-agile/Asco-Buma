<?php

namespace App\Exports;

class EnviziTotalEmployeeExport extends BaseCSRExportFormat2
{
    private const STYLE = 'CSR Employee - Employee Total';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
