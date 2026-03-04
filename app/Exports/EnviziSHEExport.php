<?php

namespace App\Exports;

class EnviziSHEExport extends BaseCSRExportFormat3
{
    private const STYLE = 'CSR Employee - SHE';// tidak ada style khusus untuk SHE, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}