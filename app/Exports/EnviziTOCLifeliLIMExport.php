<?php

namespace App\Exports;

class EnviziTOCLifeliLIMExport extends BaseCSRExportFormat2
{
    private const STYLE = 'CSR Employee - TOC Lifeli LIM';//tidak ada style khusus untuk TOC lifeli LIM, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}