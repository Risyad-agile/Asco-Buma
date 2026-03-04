<?php

namespace App\Exports;

class EnviziTotalPeCatExport extends BaseCSRExportFormat4
{
    public function __construct($companyId)
    {
        parent::__construct($companyId);
    }
}