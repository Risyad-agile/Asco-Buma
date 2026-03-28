<?php

namespace App\Exports;

class EnviziTOCLiveliLBMExport extends BaseCSRExportFormatTOC
{
    private const PROBLEM_CATEGORY = 'Life Beyond Mining';// tidak ada style khusus untuk TOC liveli LBM, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::PROBLEM_CATEGORY);
    }
}