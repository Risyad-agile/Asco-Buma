<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $table='jobs';
    protected $fillable=[
      'comp_id',
      'connect_id',
      'job_name',
      'job_interval', //HOURLY DAILY WEEKLY MONTHLY YEARLY
      'job_repeating_date', //01 02 03 ... 30 31
      'job_repeating_day', //SUNDAY MONDAY...SATURDAY
      'job_execute_time',
      'job_state',  //0=non active 1=active
    ];
}
