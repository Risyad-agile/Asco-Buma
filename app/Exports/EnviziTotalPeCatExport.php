<?php

namespace App\Exports;

class EnviziTotalPeCatExport extends BaseCSRExportFormat4
{
    private const STYLE = 'CSR Employee - Direct';// apakah ada style khusus untuk Total PE Cat? kalau tidak ada, pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}