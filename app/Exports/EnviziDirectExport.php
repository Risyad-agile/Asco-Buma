<?php

namespace App\Exports;

class EnviziDirectExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Direct';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}