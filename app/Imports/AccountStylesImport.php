<?php

namespace App\Imports;

use App\Models\AccountStyles;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AccountStylesImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    SkipsEmptyRows
{
    // If your header row is NOT row 1, change this:
    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        // normalize helper
        $yesNo = fn($v) => in_array(strtolower(trim((string)$v)), ['yes', 'y', 'true', '1'], true) ? 1 : 0;

        // In your screenshot, "Account Style Caption" looks like the best unique key.
        $caption = $row['account_style_caption'] ?? null;
        $name    = $row['account_style_name'] ?? null;

        // skip garbage/blank lines
        if (!$caption && !$name) {
            return null;
        }

        // If you want UPSERT (avoid duplicates), use updateOrCreate
        return AccountStyles::updateOrCreate(
            [
                'acc_style_caption' => $caption,   // unique key candidate
            ],
            [
                'acc_style_product'        => $row['product'] ?? null,
                'acc_style_datatype'       => $row['data_type'] ?? null,
                'acc_style_scope'          => $row['scope'] ?? null,
                'acc_style_category'       => $row['category'] ?? null,

                'acc_style_name'           => $name,
                'acc_style_caption'        => $caption,

                // if you have these columns in your file (not visible in screenshot)
                'acc_style_number'         => $row['account_number'] ?? null,
                'acc_style_reference'      => $row['account_reference'] ?? null,
                'acc_style_supplier'       => $row['account_supplier'] ?? null,

                'acc_style_qty_uom'        => $row['quantity_value_uom'] ?? null,
                'acc_style_cost_supported' => $yesNo($row['total_cost_supported'] ?? null),

                // default values
                'acc_style_state'          => 1,
            ]
        );
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
