<?php

namespace App\Exports;

class EnviziTrainingExport extends BaseCSRExportFormatTRN
{
    private const STYLE = 'CSR Training';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
