<?php

namespace App\Exports;

class EnviziLevel6Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 6';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}