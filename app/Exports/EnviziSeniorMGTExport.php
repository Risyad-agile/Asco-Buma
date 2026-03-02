<?php

namespace App\Exports;

class EnviziSeniorMGTExport extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Senior Management';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}