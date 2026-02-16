<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationRunLog extends Model
{
    protected $fillable = [
        'integration_run_id','level','event','scope','page','batch','type',
        'filename','s3_key','count','message','context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function run()
    {
        return $this->belongsTo(IntegrationRun::class);
    }
}
