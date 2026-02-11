<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;
    protected $table='tasks';
    protected $fillable=[
        'trans_no',
        'comp_id', 
        'task_name',
        'task_maker_name',
        'task_maker_message',
        'task_maker_time', 
        'task_checker_name', 
        'task_checker_message', 
        'task_checker_time',
        'task_approval_name',
        'task_approval_message',
        'task_approval_time',
        'task_file_name',
        'task_progress', //0,30, 65,100%
        'task_last_message', //overwrite with last message
        'task_approval_type', //APV-AUTO APV-LEVEL
        'task_state', //0=not active, 1=create/Ready to Submit, 2=cheks/Submitted/Ready to Approve, 3=approve/Approved 4=rejected on check 5=rejected on approve
        //new by AF
        'input_methode', //I:Import C:Data Input (Manual) A:API
        'type_csr' //Y:CSR N:Non CSR
      ];
    
      public function company()
      {
          return $this->belongsTo('App\Models\Companies','comp_id','id');
      }

      public function accstyleimport(){
        return $this->hasMany('App\Models\AccountDataLoadImport','task_id','id');
      }
}

   