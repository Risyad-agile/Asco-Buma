<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationRun extends Model
{
    protected $fillable = [
        'run_type','source','status','scope','organization',
        'total_pages','total_rows','total_files',
        'started_at','finished_at','error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(IntegrationRunLog::class);
    }
}
