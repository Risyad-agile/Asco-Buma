<?php

namespace App\Exports;

class EnviziLevel7Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 7';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}