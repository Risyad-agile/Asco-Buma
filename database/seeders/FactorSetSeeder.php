<?php

// database/seeders/FactorSetSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FactorSet;

class FactorSetSeeder extends Seeder
{
    public function run(): void
    {
        FactorSet::create([
            'name' => 'POC - Agile Co',
            'description' => 'Proof of Concept factor set for Agile Co',
            'source' => 'Internal',
            'year' => 2024,
            'company_id' => null, // kalau ada relasi company bisa diisi
        ]);

        FactorSet::create([
            'name' => 'DEFRA 2023',
            'description' => 'DEFRA public emissions factor set for 2023',
            'source' => 'DEFRA',
            'year' => 2023,
            'company_id' => null,
        ]);
    }
}

