<?php

namespace App\Exports;

use App\Models\Location;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class LocationsExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    protected $company; 

    public function collection()
    {
        return Location::get([
            'org_name',
            'group_type',
            'group_hierarchy_name',
            'group_name1',
            'group_name2',
            'group_name3',
            'location_name',
            'location_type',
            'location_reff',
            'location_reff_no',
            'location_id',
            'location_address',
            'location_city',
            'location_state_province',
            'postal_code',
            'country',
            'latitude_y',
            'longitude_x',
            'location_close_date'
        ]);
    }

    public function headings(): array
    {
        return [
            'ORGANIZATION',
            'GROUP TYPE',
            'GROUP HIERARCHY NAME',
            'GROUP NAME 1',
            'GROUP NAME 2',
            'GROUP NAME 3',
            'LOCATION',
            'LOCATION TYPE',
            'LOCATION REFERENCE',
            'LOCATION REF NO',
            'LOCATION ID',
            'STREET ADDRESS',
            'CITY',
            'STATE PROVINCE',
            'POSTAL CODE',
            'COUNTRY',
            'LATITUDE Y',
            'LONGITUDE X',
            'LOCATION CLOSE DATE'
        ];
    }

    public function title(): string
    {
        return 'Setup';
    }

    public function map($row): array
    {
        return [
            $row->org_name,
            $row->group_type,
            $row->group_hierarchy_name,
            $row->group_name1,
            $row->group_name2,
            $row->group_name3,
            $row->location_name,
            $row->location_type,
            $row->location_reff,
            $row->location_reff_no,
            $row->location_id,
            $row->location_address,
            $row->location_city,
            $row->location_state_province,
            $row->postal_code,
            $row->country,
            "'" . $row->latitude_y,   // 👉 prefix `'` biar Excel anggap teks
            "'" . $row->longitude_x,  // 👉 prefix `'`
            $row->location_close_date,
        ];
    }
}
