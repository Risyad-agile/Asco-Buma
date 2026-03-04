<?php

namespace App\Exports;

class EnviziTOCLiveliEduLiExport extends BaseCSRExportFormat2
{
    private const STYLE = 'CSR Employee - TOC Liveli Edu Li';// tidak ada style khusus untuk TOC liveli edu li, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}