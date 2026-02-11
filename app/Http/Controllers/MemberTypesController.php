<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MemberTypes;

class MemberTypesController extends Controller
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

    public function compid(){
        return Auth::user()->stores->companys->comp_id;
    }

    public function index()
    {
        $membertypes=MemberTypes::where('comp_id','=',$this->compid())->get();
        return view('members/membertypeindex',compact('membertypes'));
    }
    public function main(Request $request){
        $memtypeid=$request->get('memtype_id');
 
        if($memtypeid==null){  //addnew
            return view('members/membertypenew');
        }else{
            $membertype=MemberTypes::where('memtype_id','=',$memtypeid)->first();
            return view('members/membertypeupdate',compact('membertype'));
        }
    }
 
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $membertypes=MemberTypes::where('comp_id','=',$this->compid())->get();
        return $membertypes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $memtype_desc=$request->get('form')['memtype_desc']; 
        $memtype_min_value=$request->get('form')['memtype_min_value'];    
        $memtype_disc_state=$request->get('form')['memtype_disc_state']; 
        $memtype_disc_type=$request->get('form')['memtype_disc_type']; 
        $memtype_disc_type_value=$request->get('form')['memtype_disc_type_value']; 
        $memtype_disc_value=$request->get('form')['memtype_disc_value']; 
        $memtype_disc_percent=$request->get('form')['memtype_disc_percent']; 
        $memtype_point_state=$request->get('form')['memtype_point_state']; 
        $memtype_point_value=$request->get('form')['memtype_point_value']; 

        $memtype_rule="Setiap Anggota Jenis ".$memtype_desc." dengan minimal total transaksi Rp.".$memtype_min_value;
        if($memtype_disc_state=="1"){ //mendapat discount
            $memtype_rule=$memtype_rule." mendapatkan potongan harga yang diberikan pada";
            if($memtype_disc_type=="1"){ //dengan batas yang ditentukan
                $memtype_rule=$memtype_rule." batas belanja minimal Rp.".$memtype_disc_type_value;
            }else{
                $memtype_rule=$memtype_rule." setiap transaksi";
            }
            if($memtype_disc_value!=0){ //dengan rupiah
                $memtype_disc_percent=0; //membatasi discount hanya salah satu saja
                $memtype_rule=$memtype_rule." dengan potongan sebesar Rp.".$memtype_disc_value;
            }else{ //dengan persen
                $memdiscpecent=$memtype_disc_percent*100;
                $memtype_rule=$memtype_rule." dengan potongan sebesar ".$memdiscpecent."%";
            }
        }
        if($memtype_point_state=="1"){ //mendapatkan poin
            $memtype_rule=$memtype_rule." Mendapatkan poin setiap kelipatan transaksi Rp.".$memtype_point_value;
        }

        $membertype=new MemberTypes;
        $membertype->memtype_id=$this->autoMemberTypeId();
        $membertype->comp_id=$this->compid();
        $membertype->memtype_desc=$memtype_desc; 
        $membertype->memtype_rule=$memtype_rule;
        $membertype->memtype_min_value=$memtype_min_value;    
        $membertype->memtype_disc_state=$memtype_disc_state; 
        $membertype->memtype_disc_type=$memtype_disc_type; 
        $membertype->memtype_disc_type_value=$memtype_disc_type_value; 
        $membertype->memtype_disc_value=$memtype_disc_value; 
        $membertype->memtype_disc_percent=$memtype_disc_percent; 
        $membertype->memtype_point_state=$memtype_point_state; 
        $membertype->memtype_point_value=$memtype_point_value; 
        $membertype->memtype_state='1';
        $membertype->save();
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $memtype_desc=$request->get('form')['memtype_desc']; 
        $memtype_min_value=$request->get('form')['memtype_min_value'];    
        $memtype_disc_state=$request->get('form')['memtype_disc_state']; 
        $memtype_disc_type=$request->get('form')['memtype_disc_type']; 
        $memtype_disc_type_value=$request->get('form')['memtype_disc_type_value']; 
        $memtype_disc_value=$request->get('form')['memtype_disc_value']; 
        $memtype_disc_percent=$request->get('form')['memtype_disc_percent']; 
        $memtype_point_state=$request->get('form')['memtype_point_state']; 
        $memtype_point_value=$request->get('form')['memtype_point_value']; 

        $memtype_rule="Setiap Anggota Jenis ".$memtype_desc." dengan minimal total transaksi Rp.".$memtype_min_value;
        if($memtype_disc_state=="1"){ //mendapat discount
            $memtype_rule=$memtype_rule." mendapatkan potongan harga yang diberikan pada";
            if($memtype_disc_type=="1"){ //dengan batas yang ditentukan
                $memtype_rule=$memtype_rule." batas belanja minimal Rp.".$memtype_disc_type_value;
            }else{
                $memtype_rule=$memtype_rule." setiap transaksi";
            }
            if($memtype_disc_value!=0){ //dengan rupiah
                $memtype_disc_percent=0; //membatasi discount hanya salah satu saja
                $memtype_rule=$memtype_rule." dengan potongan sebesar Rp.".$memtype_disc_value;
            }else{ //dengan persen
                $memdiscpecent=$memtype_disc_percent*100;
                $memtype_rule=$memtype_rule." dengan potongan sebesar ".$memdiscpecent."%";
            }
        }
        if($memtype_point_state=="1"){ //mendapatkan poin
            $memtype_rule=$memtype_rule." Mendapatkan poin setiap kelipatan transaksi Rp.".$memtype_point_value;
        }


        MemberTypes::where('memtype_id',$id)->update([
            "memtype_desc"=>$memtype_desc, 
            "memtype_rule"=>$memtype_rule,
            "memtype_min_value"=>$memtype_min_value,    
            "memtype_disc_state"=>$memtype_disc_state, 
            "memtype_disc_type"=>$memtype_disc_type, 
            "memtype_disc_type_value"=>$memtype_disc_type_value, 
            "memtype_disc_value"=>$memtype_disc_value, 
            "memtype_disc_percent"=>$memtype_disc_percent, 
            "memtype_point_state"=>$memtype_point_state, 
            "memtype_point_value"=>$memtype_point_value, 
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
        //
    }
    public function autoMemberTypeId()
    {
      // aturan kode toko : comp_idB123
      $comp_id=$this->compid();
      $strNewId = $comp_id."MT01";
      // kalau belum ada record sebelumnya berarti ini yang pertama
      if(MemberTypes::where('memtype_id','=',$strNewId)->count()==0){
        return $strNewId;
      }

      $strLastId= MemberTypes::where('comp_id','=',$this->compid())->get()->last()->memtype_id;
      $intNewId= substr($strLastId,-2)+1;

      $strNewId=strlen($intNewId);
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$comp_id.'MT0'.$intNewId;
              break;
          case 2:
              $strNewId=$comp_id.'MT'.$intNewId;
              break;
      }
      return $strNewId;
    }
}
