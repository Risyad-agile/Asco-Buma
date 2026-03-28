<?php

namespace App\Exports;

class EnviziTOCEduExport extends BaseCSRExportFormatTOC
{
    private const PROBLEM_CATEGORY = 'Education Access and Quality';// tidak ada style khusus untuk TOC edu, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::PROBLEM_CATEGORY);
    }
}