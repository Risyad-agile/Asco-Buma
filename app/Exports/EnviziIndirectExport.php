<?php

namespace App\Exports;

class EnviziIndirectExport extends BaseCSRExportFormat1
{
    private const STYLE = 'In Direct';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}