<?php

namespace App\Exports;

class EnviziRecruitmentExport extends BaseCSRExportRecruitmentFormat
{
    private const STYLE = 'CSR Employee - Recruitment';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}