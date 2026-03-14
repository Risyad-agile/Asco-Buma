<?php

namespace App\Exports;

class EnviziLevel5Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 5';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}