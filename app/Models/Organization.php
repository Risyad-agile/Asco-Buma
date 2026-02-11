<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory; 

    protected $table = 'organizations';

    protected $fillable = [
        'org_link',
        'org_name', 
        'org_state', //0=non active 1=active
        'comp_id'
    ];  

    public function company()
    {
        return $this->belongsTo(Companies::class,'comp_id','id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'org_name', 'org_name');
    }
    
}
