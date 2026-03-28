<?php

namespace App\Exports;

class EnviziTrainingTotalExport extends BaseCSRExportFormatTRNT
{
    private const STYLE = 'CSR Training Total';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
