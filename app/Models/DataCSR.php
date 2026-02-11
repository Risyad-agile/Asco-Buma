<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCSR extends Model
{
    use HasFactory;

    protected $table = 'data_csr';

    protected $fillable = [
        'reff_id',
        'location',
        'account_style',
        'year',

        'male',
        'female',
        'k30_th',
        'thirty_to_50_th',
        'more_than_50_th',

        'phd',
        'postgraduate',
        'undergraduate',
        'diploma',
        'high_school',
        'junior_high_school',
        'elementary_school',
        'others',

        'islam',
        'kristen',
        'katolik',
        'hindu',
        'budha',
        'konghucu',

        'employee_total',
        'total',

        'created_utc_date',
        'modified_utc_date',
    ];

    protected $casts = [
        'created_utc_date' => 'datetime',
        'modified_utc_date' => 'datetime',
    ];
}
