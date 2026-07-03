<?php

namespace App\Exports;

class EnviziLevel1Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 1';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}