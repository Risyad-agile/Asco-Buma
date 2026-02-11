<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationsPartner extends Model
{
    use HasFactory;

    protected $table = 'mst_locations';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';   // sesuaikan: 'int' atau 'string'
    protected $connection = 'mysql'; // default connection

    protected $fillable = [
        'id',
        'org_name',
        'group_type',
        'group_hierarchy_name',
        'group_name1',
        'group_name2',
        'group_name3', 
        'location_name',
        'location_type',
        'location_reff',
        'location_reff_no',
        'location_id',
        'location_address',
        'location_city',
        'location_state_province', 
        'postal_code',
        'country',
        'latitude_y',
        'longitude_x',
        'location_close_date'   
    ];
}
