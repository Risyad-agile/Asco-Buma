<?php

// app/Models/FactorSet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorSet extends Model
{
    protected $fillable = [
        'name', 'description', 'source', 'year', 'company_id'
    ];

    // Relasi ke company (jika ada)
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Relasi ke custom factors
    public function customFactors()
    {
        return $this->hasMany(CustomFactor::class);
    }
}
