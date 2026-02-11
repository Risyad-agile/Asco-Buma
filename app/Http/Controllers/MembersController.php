<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Companys;
use App\Models\Stores;
use App\Models\Members;
use App\Models\MemberTypes;
use Carbon\Carbon;
use Entrust;
use Session;

class MembersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function username(){
        return Auth::user()->username;
    }
    public function storeid(){
        return Auth::user()->stores->store_id;
    }
    public function activeuserstoreid(){
        return Auth::user()->stores->store_id;
    }
    public function compid(){
        return Auth::user()->stores->companys->comp_id;
    }

    public function index()
    {
        $membertypes=MemberTypes::where('comp_id','=',$this->compid())->get();
        return view('members/members',compact('membertypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $members=Members::with('membertypes')->where([['comp_id','=',$this->compid()],['member_state','1']])->get();
        return $members;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'member_id' => 'required|unique:members|max:20',
            'member_name' => 'required',
        ]);


        $member=new Members;
        $member->member_no=$this->autoMemberID();
        $member->member_id=$request->get('member_id');
        $member->member_name=$request->get('member_name');
        // $member->member_card_no=$request->get('member_card_no');
        $member->comp_id=$this->compid();
        $member->store_id=$this->activeuserstoreid();
        $member->memtype_id=$request->get('membertypes')['memtype_id'];
        $member->member_birth_date=Carbon::createFromFormat('d-m-Y',$request->get('member_birth_date'));
        $member->member_birth_place=$request->get('member_birth_place');
        $member->member_email=$request->get('member_email');
        $member->member_pin=$request->get('member_pin');
        $member->member_gender=$request->get('member_gender'); //Pria Wanita
        $member->member_religion=$request->get('member_religion'); //islam Protestan Katolik Hindu Budha
        $member->member_address=$request->get('member_address');
        $member->member_ktp=$request->get('member_ktp');
        $member->member_state='1'; //0:tidak aktif 1:aktif
        $member->save();
    
        Session::flash('flash_message', 'Member Successfully Added!');
        
        return redirect()->back()->with('success','Member Successfully Added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $member=Members::where('member_id','=',$id)->get();
        return response($member);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $member=Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)->first();

      $membername=$request->get('member_name');
      $memtypeid=isset($request->get('membertypes')['memtype_id'])?$request->get('membertypes')['memtype_id']:null;
      $memberdesc=$request->get('member_desc');
      $membercardno=$request->get('member_card_no');
      $memberbirthdate=$request->get('member_birth_date');
      $memberbirthplace=$request->get('member_birth_place');
      $membergender=$request->get('member_gender');
      $memberreligion=$request->get('member_religion');
      $memberaddress=$request->get('member_address');
      $memberktp=$request->get('member_ktp');
      $memberemail=$request->get('member_email');
      $memberpin=$request->get('member_pin');

      if($membername==null){
        $membername=$member->member_name;
      }
      if($memtypeid==null){
        $memtypeid=$member->memtype_id;
      }
      if($memberdesc==null){
        $memberdesc=$member->member_desc;
      }
      if($membercardno==null){
        $membercardno=$member->member_card_no;
      }
      if($memberbirthdate==null){
        $memberbirthdate=$member->member_birth_date;
      }else{
        $memberbirthdate=Carbon::createFromFormat('d-m-Y',$request->member_birth_date);
      }
      if($memberbirthplace==null){
        $memberbirthplace=$member->member_birth_place;  // date("d-m-Y")
      }
      if($membergender==null){
        $membergender=$member->member_gender;
      }
      if($memberreligion==null){
        $memberreligion=$member->member_religion;
      }
      if($memberaddress==null){
        $memberaddress=$member->member_address;
      }
      if($memberktp==null){
        $memberktp=$member->member_ktp;
      }
      if($memberemail==null){
        $memberemail=$member->member_email;
      }
      if($memberpin==null){
        $memberpin=$member->member_pin;
      }

      Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)
      ->update([
        'member_name'=>$membername,
        'memtype_id'=>$memtypeid,
        'member_desc'=>$memberdesc,
        // 'member_card_no'=>$membercardno,
        'member_birth_date'=>$memberbirthdate,
        'member_birth_place'=>$memberbirthplace,
        'member_gender'=>$membergender,
        'member_religion'=>$memberreligion,
        'member_address'=>$memberaddress,
        'member_ktp'=>$memberktp,
        'member_email'=>$memberemail,
        'member_pin'=>$memberpin,
      ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

      $memberid=Members::where('member_no',$id)->first()->member_id;
      $memberid=$memberid.'-'.substr($id,-2);
      Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)
      ->update([
        'member_id'=>$memberid,
        'member_state'=>'0', 
      ]);
  }
  
  //------- member register @store
  public function storeMemberIndex(){
    $membertypes=MemberTypes::where('comp_id','=',$this->compid())->get();
    return view('members/storemembers',compact('membertypes'));
  }
  public function storeMemberLoad(){
    $members=Members::with('membertypes')->where([['store_id','=',$this->activeuserstoreid()],['member_state','1']])->get();
    return $members;
  }
  public function storeMemberSave(Request $request){
    $validatedData = $request->validate([
      'member_id' => 'required|unique:members|max:20',
      'member_name' => 'required',
    ]);
    $member=new Members;
    $member->member_no=$this->autoMemberID();
    $member->member_id=$request->get('member_id');
    $member->member_name=$request->get('member_name');
    $member->member_card_no='0000'; //member card dard dari agile pay
    $member->comp_id=$this->compid();
    $member->store_id=$this->activeuserstoreid();
    $member->memtype_id=$request->get('membertypes')['memtype_id'];
    $member->member_birth_date=Carbon::createFromFormat('d-m-Y',$request->get('member_birth_date'));
    $member->member_birth_place=$request->get('member_birth_place');
    $member->member_gender=$request->get('member_gender'); //Pria Wanita
    $member->member_religion=$request->get('member_religion'); //islam Protestan Katolik Hindu Budha
    $member->member_address=$request->get('member_address');
    $member->member_ktp=$request->get('member_ktp');
    $member->member_state='1'; //0:tidak aktif 1:aktif
    $member->save();

    Session::flash('flash_message', 'Member Successfully Added!');
    
    return redirect()->back()->with('success','Member Successfully Added!');    
  }
  public function storeMemberUpdate(Request $request,$id){
    $member=Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)->first();

    $membername=$request->get('member_name');
    $memtype_id=$request->get('membertypes')['memtype_id'];
    $memberdesc=$request->get('member_desc');
    $membercardno=$request->get('member_card_no');
    $memberbirthdate=$request->get('member_birth_date');
    $memberbirthplace=$request->get('member_birth_place');
    $membergender=$request->get('member_gender');
    $memberreligion=$request->get('member_religion');
    $memberaddress=$request->get('member_address');
    $memberktp=$request->get('member_ktp');

    if($membername==null){
      $membername=$member->member_name;
    }
    if($memtype_id==null){
      $memtype_id=$member->member_type;
    }
    if($memberdesc==null){
      $memberdesc=$member->member_desc;
    }
    if($membercardno==null){
      $membercardno=$member->member_card_no;
    }
    if($memberbirthdate==null){
      $memberbirthdate=$member->member_birth_date;
    }else{
      $memberbirthdate=Carbon::createFromFormat('d-m-Y',$request->member_birth_date);
    }
    if($memberbirthplace==null){
      $memberbirthplace=$member->member_birth_place;  // date("d-m-Y")
    }
    if($membergender==null){
      $membergender=$member->member_gender;
    }
    if($memberreligion==null){
      $memberreligion=$member->member_religion;
    }
    if($memberaddress==null){
      $memberaddress=$member->member_address;
    }
    if($memberktp==null){
      $memberktp=$member->member_ktp1;
    }

    Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)
    ->update([
      'member_name'=>$membername,
      'memtype_id'=>$memtype_id,
      'member_desc'=>$memberdesc,
      // 'member_card_no'=>$membercardno, //tidak di update dari agile pay
      'member_birth_date'=>$memberbirthdate,
      'member_birth_place'=>$memberbirthplace,
      'member_gender'=>$membergender,
      'member_religion'=>$memberreligion,
      'member_address'=>$memberaddress,
      'member_ktp'=>$memberktp,
    ]);
  }
  public function storeMemberDelete($id){
    $memberid=Members::where('member_no',$id)->first()->member_id;
    $memberid=$memberid.'-'.substr($id,-2);
    Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)
    ->update([
      'member_id'=>$memberid,
      'member_state'=>'0', 
    ]);
  }

  //-----------aktivasi online store
  public function memberActivateOnlineStoreIndex(){
    $membertypes=MemberTypes::where('comp_id','=',$this->compid())->get();
    return view('members/memberonstore');
  }

  public function memberActivateOnlineStoreLoad(){
    $members=Members::with('stores')->where([['comp_id','=',$this->compid()],['member_state','1']])->get();
    foreach ($members as $member) {
      $member->member_store_activation=false;
      if($member->stores->store_type=='0'){
        $member->member_store_activation=true;
      }
    }
    return $members;
  }

  public function memberActivateOnlineStoreUpdate(Request $request, $id){
    //cari lokasi dengan status online store, sementara ini online store hanya
    //satu, jadi yang pertama ketemu yang di aktifkan 

    $store=Stores::where([['comp_id','=',$this->compid()],['store_type','0']])->first();
    if(!$store){
      return;
    }
    $storeid=$store->store_id;

    Members::where('comp_id','=',$this->compid())->where('member_no','=',$id)
    ->update([
      'store_id'=>$storeid, 
    ]);
  }

  public function autoMemberNO($compid)
  {
    // aturan nomor Member: CompID123456
    $strNewId = $compid."000001";
    while ($this->findMemberNO($strNewId)) { 
      $intNewId= substr($strLastId,-6)+1;
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$compid.'00000'.$intNewId;
              break;
          case 2:
              $strNewId=$compid.'0000'.$intNewId;
              break;
          case 3:
              $strNewId=$compid.'000'.$intNewId;
              break;
          case 4:
              $strNewId=$compid.'00'.$intNewId;
              break;
          case 5:
              $strNewId=$compid.'0'.$intNewId;
              break;
          case 6:
              $strNewId=$compid.$intNewId;
              break;
      }
    }
    return $strNewId;
  }


private function findMemberNO($memberid){
    $member=Memebers::where('member_id',$memberid)->first();
    if($member){
      return true;
    }
    return false;
}




  //------- member poins
  public function memberPointList(){
    return view('members/memberpoints');
  }

  //-------------TOOLS-----------
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
}
