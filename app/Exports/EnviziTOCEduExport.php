<?php

namespace App\Exports;

class EnviziTOCEduExport extends BaseCSRExportFormat2
{
    private const STYLE = 'CSR Employee - Direct';// tidak ada style khusus untuk TOC edu, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}