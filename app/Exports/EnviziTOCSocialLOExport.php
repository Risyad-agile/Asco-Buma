<?php

namespace App\Exports;

class EnviziTOCSocialLOExport extends BaseCSRExportFormatTOC
{
    private const PROBLEM_CATEGORY = 'Social License To Operate';// tidak ada style khusus untuk TOC liveli edu li, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::PROBLEM_CATEGORY);
    }
}