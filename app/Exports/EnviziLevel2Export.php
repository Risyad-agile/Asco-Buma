<?php

namespace App\Exports;

class EnviziLevel2Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 2';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}