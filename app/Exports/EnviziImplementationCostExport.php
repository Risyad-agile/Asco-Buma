<?php

namespace App\Exports;

class EnviziImplementationCostExport extends BaseCSRExportFormatImplementationCost
{
    private const STYLE = 'Implementation Cost';

    public function __construct($companyId)
    {
        parent::__construct($companyId, self::STYLE);
    }
}