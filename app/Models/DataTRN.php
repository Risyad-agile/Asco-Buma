<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTRN extends Model
{
    use HasFactory;

    protected $table = 'data_trn';

    protected $fillable = [
        'reff_id',
        'organization',
        'location',
        'account_style',
        'account_number',

        'personnel_number',
        'name',

        'level',
        'gender',
        'age_category',

        'course_training_name',
        'total_hours',
        'total',

        'created_utc_date',
        'modified_utc_date',
    ];

    protected $casts = [
        'level' => 'integer',
        'total_hours' => 'integer',
        'total' => 'integer',
        'created_utc_date' => 'datetime',
        'modified_utc_date' => 'datetime',
    ];
}