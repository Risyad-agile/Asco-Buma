<?php

namespace App\Exports;

class EnviziLevel4Export extends BaseCSRExportFormat1
{
    private const STYLE = 'CSR Employee - Level 4';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}