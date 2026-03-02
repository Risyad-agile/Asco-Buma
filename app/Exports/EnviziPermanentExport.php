<?php

namespace App\Exports;

class EnviziPermanentExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Permanent';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}