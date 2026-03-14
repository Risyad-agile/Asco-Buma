<?php

namespace App\Exports;

class EnviziLevel8Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 8';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}