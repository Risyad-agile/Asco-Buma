<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Stores;
use App\Models\Companys;
use App\Models\Members;
use App\Models\MemberTypes;
use App\Models\EVouchers;
use Session;
use DB;

class MemberServices
{
    //22-10-22
    //membuat dummy member GUEST
    public function createMemberGuest($storeid){
        $member=Members::where('store_id',$storeid)->first();
        $storename=Stores::where('id',$storeid)->first()->store_name;
        if($member){
          return; //sudah ada
        };
    
        $member=new Members;
        $member->member_no="0000-0000-0000-0000";
        $member->store_id=$storeid;
        $member->member_id="1234567890";
        $member->member_name="GUEST ".$storename; 
        $member->member_birth_date=date('Y-m-d');
        $member->member_birth_place="";
        $member->member_desc="UNKNOWN";
        $member->member_gender="PRIA"; //Pria Wanita
        $member->member_religion=""; //islam Protestan Katolik Hindu Budha
        $member->member_address="";
        $member->member_ktp="0000";
        $member->member_state='1'; //0:tidak aktif 1:aktif
        $member->save();
    }

    //menambahkan poin dan sales pada tabel member
    public static function saveMemberPointSales($memberno,$sales){
        $member=Members::where('member_no',$memberno)->first();

        $memtypeid=$member->memtype_id;
        $mempoint=$member->member_points;
        $memtrans=$member->member_total_trans+$sales;

        // $membertype=MemberTypes::where('memtype_id',$memtypeid)->first();
        // if($membertype->memtype_point_state=="1"){ //mendapatkan poin
        //     $poin=floor($sales/$membertype->memtype_point_value);
        //     $mempoint=$mempoint+$poin;
        // }
        $mempoint=0;
        Members::where('member_no','=',$memberno)
        ->update([
          'member_total_trans'=>$memtrans,
          'member_points'=>$mempoint, 
        ]);
        return "SUCCESS";
    }

    //mendapatkan besaran discount member, jika tidak ada
    //kembalinya adalah nol
    public static function getMemberDiscount($memberno,$sales){
        $memtypeid=Members::where('member_id',$memberno)->first()->memtype_id;
        $membertype=MemberTypes::where('memtype_id',$memtype_id)->first();
        $disclimit=$membertype->memtype_disc_type_value;
        $discvalue=$membertype->memtype_disc_value;
        $discpercent=$membertype->memtype_disc_percent;
 
        $memberdisc=0;
        if($membertype->memtype_disc_state=="1"){ //mendapat discount
            if($membertype->memtype_disc_type=="1"){ //dengan batas yang ditentukan
                //jika jumlah sales lebih kecil dari batas discount 
                //maka kembali 0 (tidak dapat discount)
                if($sales<$disclimit){
                    return 0;
                }
            }
            //periksa discount yang digunakan
            if($discvalue!=0){ //berarti pake perecent
                return $discvalue;
            }else{
                return $sales*$discpercent;
            }
        }
        return 0;
    }

    //update voucher yang digunakan, karena sementara status pending
    //setelah terjadi sales di ubah menjadi used
    public static function updateMemberVoucher($memberno){
        $member=function($query) use ($memberno){
            $query->where('member_no',$memberno);
        };

        EVouchers::with(['members'=>$member,'companies'])->where('evoucher_state','4')
                   ->whereHas('members',$member)->update([
                    'evoucher_state'=>'3', //used
                  ]);
    }

}