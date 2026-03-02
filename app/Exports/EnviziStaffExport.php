<?php

namespace App\Exports;

class EnviziStaffExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Staff';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}