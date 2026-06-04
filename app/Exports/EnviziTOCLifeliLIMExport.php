<?php

namespace App\Exports;

class EnviziTOCLifeliLIMExport extends BaseCSRExportFormatLIM
{
    private const PROBLEM_CATEGORY = 'Life In Mining';//tidak ada style khusus untuk TOC lifeli LIM, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::PROBLEM_CATEGORY);
    }
}