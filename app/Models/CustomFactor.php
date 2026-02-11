<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Organization;
use App\Models\FactorSet;

class CustomFactor extends Model
{
    use HasFactory;

    protected $table = 'custom_factors';

    protected $fillable = [
        'organization_id',
        'factor_set_id',
        'source',
        'reference',
        'category',
        'subcategory',
        'name',
        'associate_code',
        'factor_link',
        'data_type',
        'sub_type',
        'unit',
        'factor_value',
        'co2',
        'ch4',
        'n2o',
        'biogenic',
        'co2e',
        'energy',
        'mass',
        'volume',
        'area',
        'distance',
        'calculation_method',
        'description',
        'effective_date',
        'effective_to',
        'published_date',
        'published_to',
        'country',
        'state',
        'city',
        'sector',
        'scope',
        'is_active',
    ];

    protected $casts = [
        'factor_value' => 'decimal:10',
        'co2' => 'decimal:10',
        'ch4' => 'decimal:10',
        'n2o' => 'decimal:10',
        'biogenic' => 'decimal:10',
        'co2e' => 'decimal:10',
        'energy' => 'decimal:10',
        'mass' => 'decimal:10',
        'volume' => 'decimal:10',
        'area' => 'decimal:10',
        'distance' => 'decimal:10',
        'effective_date' => 'date',
        'effective_to' => 'date',
        'published_date' => 'date',
        'published_to' => 'date',
        'is_active' => 'boolean',
    ];

    // Relasi ke organization
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    // Relasi ke factor set
    public function factorSet()
    {
        return $this->belongsTo(FactorSet::class, 'factor_set_id', 'id');
    }
}
