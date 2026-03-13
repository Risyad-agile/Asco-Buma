<?php

namespace App\Services ;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;
use App\Models\AccountStyles;
use App\Models\Location;


class EnviziSyncService
{
    protected ConnectorService $connector;

    public function __construct(ConnectorService $connector)
    {
        $this->connector = $connector;
    }

    public function processSyncronize($result, $orgId)
    {
        dd($result);
        $rstOrg = DB::table('organizations')
                    ->select('comp_id','org_name','org_link')
                    ->where('id',$orgId)
                    ->first();

        if(!$rstOrg){
            throw new \Exception("Organization not found");
        }

        // set dynamic DB
        $this->connector->setDynamicConnection($rstOrg->comp_id);
        $db = DB::connection('dynamic');

        $locationRows = [];
        $accountStyleRows = [];

        foreach($result as $rs){

            if(!isset($rs->params)) continue;

            foreach($rs->params as $param){

                // ================= LOCATION =================
                if($param->name == "Location_Id"){

                    foreach($param->availableValues ?? [] as $val){

                        if(trim($val->name) == "All Locations") continue;

                        $locationRows[] = [
                            'reff_id'       => $val->value,
                            'org_name'      => $rstOrg->org_name,
                            'location_name' => trim($val->name),
                            'created_at'    => now(),
                            'updated_at'    => now()
                        ];
                    }
                }

                // ================= ACCOUNT STYLE =================
                if($param->name == "Filter_By"){

                    foreach($param->availableValues ?? [] as $val){

                        $accountStyleRows[] = [
                            'comp_id'            => $rstOrg->comp_id,
                            'acc_style_link'     => $val->value,
                            'acc_style_caption'  => $val->name,
                            'created_at'         => now(),
                            'updated_at'         => now()
                        ];
                    }
                }

            }
        }

        DB::beginTransaction();

        try {

            // ================= UPSERT LOCATION =================
            if(!empty($locationRows)){

                $db->table('locations')->upsert(
                    $locationRows,
                    ['reff_id'], // unique key
                    ['location_name','updated_at']
                );
            }

            // ================= UPSERT ACCOUNT STYLE =================
            if(!empty($accountStyleRows)){

                $db->table('account_style_companies')->upsert(
                    $accountStyleRows,
                    ['acc_style_comp_link'],
                    ['acc_style_comp_caption','updated_at']
                );
            }

            DB::commit();

        } catch (\Exception $e){

            DB::rollBack();
            throw $e;
        }
    }

    // public function processSyncronize($result, $orgId)
    // {
    //     $rstOrg = DB::table('organizations')
    //                 ->select ('comp_id','org_name','org_link')
    //                 ->where ('id',$orgId) 
    //                 ->first();

    //     $this->connector->setDynamicConnection($rstOrg->comp_id);
    //     $db = DB::connection('dynamic');
    //     foreach($result as $rs){

    //         if(!isset($rs->params)) continue;            
    //         foreach($rs->params as $param){
    //             dd($param);
    //             if($param->name == "Location_Id"){
    //                 foreach($param->availableValues as $availableValue){
    //                     $this->saveLocation($db, $rstOrg->org_name, $availableValue);
    //                 } 
    //             }

    //             if($param->name == "Filter_By"){
    //                 $this->saveAccountStyle($db, $company->id,$dta);
    //             }

    //         }
    //     }
    // }

    // // ====================== Private Save Location ======================
    // private function saveLocation($db, $orgName, $dta)
    // {
    //     // dd($orgname,$locid,$locname,$data); 
    //     if($dta->name=="All Locations") return;
  
    //     $exist = $db->table('locations')
    //                  ->where('reff_id', $dta->value)
    //                  ->exists(); 
       
    //     if(!$exist){
    //         $db->table('locations')->insert([
    //             'reff_id'=>$dta->values,
    //             'org_name'=>$orgName,
    //             'location_name'=>$dta->name   
    //         ]);
    //     }else { 
            
    //         $location->update(['location_id'=>$locid]);
    //     }
    // }

    // // ====================== Private Save Account Style ======================
    // private function saveAccountStyleCompany($db, $compid,$accstylelink,$accstylecaption)
    // {
    //     $accstyle = AccountStyleCompanies::where('acc_style_comp_link',$accstylelink)->first();
    //     if($accstyle) return;

    //     $accstyle = new AccountStyleCompanies;
    //     $accstyle->comp_id = $compid;
    //     $accstyle->acc_style_comp_link = $accstylelink;
    //     $accstyle->acc_style_comp_caption = $accstylecaption;
    //     $accstyle->save();
    // }
}