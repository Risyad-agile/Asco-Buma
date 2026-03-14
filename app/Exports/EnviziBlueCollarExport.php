<?php

namespace App\Exports;

class EnviziBlueCollarExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Blue Collar';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}