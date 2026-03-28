<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTOC extends Model
{
    use HasFactory;

    protected $table = 'data_tec';

    protected $fillable = [
        'job_site',
        'organization',

        'category',
        'problem_category',
        'location',

        'tipe_beneficiaries',

        'output',
        'outcome',
        'outcome_additional',

        'output_event',
        'output_all',

        'created_utc_date',
        'modified_utc_date',
    ];

    protected $casts = [
        'job_site' => 'integer',

        'output' => 'integer',
        'outcome' => 'integer',
        'outcome_additional' => 'integer',

        'output_event' => 'integer',
        'output_all' => 'integer',

        'created_utc_date' => 'datetime',
        'modified_utc_date' => 'datetime',
    ];
}