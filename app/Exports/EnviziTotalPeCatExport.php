<?php

namespace App\Exports;

class EnviziTotalPeCatExport extends BaseCSRExportFormat3
{
    private const STYLE = 'CSR Employee - Turnover - Retirement';// lupa cara cek acc comp style lewat mysql workbench

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}