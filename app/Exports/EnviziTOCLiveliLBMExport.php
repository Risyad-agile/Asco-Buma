<?php

namespace App\Exports;

class EnviziTOCLiveliLBMExport extends BaseCSRExportFormat2
{
    private const STYLE = 'CSR Employee - Direct';// tidak ada style khusus untuk TOC liveli LBM, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}