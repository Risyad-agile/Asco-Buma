<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = \App\Models\Companies::create([
            'org_id' => 1,
            'comp_name' => 'ASRI',
            'comp_address' => 'Setiabudi',
            'comp_city' => 'Jakarta',
            'comp_province' => 'Jakarta',
            'comp_phone' => '021',
            'comp_email' =>'asri@agile.co.id',
        ]); 
        // $organization = \App\Models\Organizations::create([
        //     'id' => 1,
        //     'org_name' => 'ASRI',
        //     'org_street_address' => 'Setiabudi',
        //     'org_city' => 'Jakarta',
        //     'org_state_province' => 'Jakarta',
        //     'org_postal_code' => '010101', 
        // ]); 
    }
}
 