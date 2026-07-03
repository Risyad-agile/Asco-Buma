<?php

namespace App\Exports;

class EnviziLevel3Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 3';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}