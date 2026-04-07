<?php

namespace App\Exports;

class EnviziTOCWellbeingExport extends BaseCSRExportFormatTOC
{
    private const PROBLEM_CATEGORY = 'Employee Wellbeing & Development';// tidak ada style khusus untuk TOC liveli edu li, jadi pakai style umum saja

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::PROBLEM_CATEGORY);
    }
}