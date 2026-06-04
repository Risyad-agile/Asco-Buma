<?php

namespace App\Exports;

class EnviziTrainingExport extends BaseCSRExportFormatTRN
{
    private const STYLE = 'CSR Training BUMA';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}
