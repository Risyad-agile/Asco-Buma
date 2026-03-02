<?php

namespace App\Exports;

class EnviziMidMGTExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Middle Management';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}