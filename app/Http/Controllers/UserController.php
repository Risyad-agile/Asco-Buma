<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Token;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\RegistrationApproval;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Companies; 
use Validator;



class UserController extends Controller
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
        return Auth::user()->organization->company->id;
    }
    public function userid(){
        return Auth::user()->id;
    }
    public function index()
    {
        $user=User::with('organization')->get();
        return view('tools/superadmin/userindex',compact('user'));
    }
    public function userMain(Request $request){
        $stat=$request->userstate;
        $username=$request->username;

        $role=Role::all();
        $companies=Companies::where('comp_state','1')->get(); 

        if($stat=="NEW"){ 
            return view('tools/superadmin/usernew',compact('role','companies'));
        }
        if($stat=="UPDATES"){
            $user=User::with('organization')->where('username',$username)->first(); 
            return view('tools/superadmin/userupdate',compact('user','companies'));
        }
        if($stat=="CHANGE"){
            $user=User::with('organization')->where('username',$username)->first(); 
            return view('tools/superadmin/userpwdchange',compact("user"));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $user=User::all();
        $role=Role::all();
        $user=new User;
        return view('tools/usernew',compact('role','user'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $roleids = $request->input('form')['role']; 
        $compid = $request->input('form')['comp_id'];
        $username=$request->input('form')['username'];

        $validator = Validator::make($request->all(), [
            '*.username' => 'required|string|unique:users',
            '*.name' => 'required|string',
            '*.email' => 'required|string|unique:users',
        ],[ 
            'username.required' => 'User Name cant be empty',
            'name.required' => 'Please fill the name', 
            'email.required' => 'Email required', 
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        //spatie 
        $role=Role::findById($roleids);

        try{
            $user=new User;
            $user->assignRole($role);
            $user->username=$username;
            $user->comp_id=$compid;
            $user->name=$request->input('form')['name'];
            $user->email=$request->input('form')['email'];
            $user->password=Hash::make($request->input('form')['password']);
            $execute = $user->save();
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Data Berhasil di Simpan', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
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
        $name=$request->input('form')['name'];
        $compid = $request->input('form')['comp_id'];

        $validator = Validator::make($request->all(), [
            '*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        try{
            $execute=User::where('id',$id)
            ->update([
              'comp_id'=>$compid,
              'name'=>$name,
            ]);
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Data Berhasil di Simpan', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    }
    public function userResetPassword(Request $request, $id){
        $newpass=$request->input('form')['password'];
        $password=Hash::make($newpass);
        try{
            $execute=User::where('id','=',$id)
            ->update([
                'password'=>$password, 
              ]);
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Password berhasil diperbaharui', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
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

   
    //--------CREATE TOKEN FOR ACCESSING ASRI API-------------
     //---- Create API Token
     public function userAPITokenIndex(){
        $user=User::with('roles','company')->get(); 
        foreach ($user as $key => $usr) {
            $userid=$usr->id;
            $token=Token::where([['user_id', $userid],['revoked','0']])->first();
            if($token){
                $usr->user_state='ACTIVE';
            }else{
                $usr->user_state='NOT ACTIVE';
            }
        }
        return view('tools/api-token/index',compact('user'));
    }
    public function userAPITokenMain(Request $request){
        $stat=$request->userstate;
        $userid=$request->userid;

        if($stat=="CREATE"){
            $user=User::with('company')->where('id',$userid)->first(); 
            $token = $user->createToken('asri_token')->accessToken;
            $user->user_token=$token;
            return view('tools/api-token/create',compact('user'));
        }
        if($stat=="DELETE"){
            // $userTokens=User::with('company')->where('id',$userid)->first()->tokens; 
            // foreach($userTokens as $token) {
            //     $token->revoke();   
            // }
            $token=Token::where('user_id', $userid)->delete();
            return redirect()->route('users.api.token.index');
        }
    }
    public function userAPITokenSendEmail(Request $request){
        $userid=$request->user['id'];
        $user=User::with('company')->where('id',$userid)->first(); 
        $user->user_token=$request->user['user_token'];
        $email=$user->email;
        
        Mail::to($email)->send(new RegistrationApproval($user));
        return response()->json(['status' => 'success', 'message' => 'Token Has been Send to Email', 'code' => 200]);
    }

    #------Registration Aprroval and Create Token
    public function userRegistrationList(){
        $role=Role::all();
        $users=User::with('company')->where('approved','0')->get(); 
        $companies=Companies::where('comp_state','1')->get();
        foreach ($users as $key => $user) {
            $user->approved='REQUESTED';
        }
        return view("tools/api-token/registration-list",compact('users','companies','role'));
    }
    public function userRegistrationMain(Request $request){
        $stat=$request->userstate;
        $userid=$request->userid;

        if($stat=="CREATE"){
            return view('tools/api-token/create',compact('user'));
        }
        if($stat=="DELETE"){
            // $userTokens=User::with('company')->where('id',$userid)->first()->tokens; 
            // foreach($userTokens as $token) {
            //     $token->revoke();   
            // }
            $token=Token::where('user_id', $userid)->delete();
            return redirect()->route('users.api.token.index');
        }
    }
    public function userRegistrationAccepted(Request $request){
        $compid=$request->compid;
        $userid=$request->userid;
        $rolenames = $request->rolename;  
        
        User::where('id',$userid)->update([
            'comp_id'=>$compid,
            'approved'=>'1',
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $user=User::with('company')->where('id',$userid)->first(); 
        $user->syncRoles($rolenames);
        $token = $user->createToken('asri_token')->accessToken;
        $user->user_token=$token;
        $email=$user->email;
        Mail::to($email)->send(new RegistrationApproval($user));
        return response()->json(['status' => 'success', 'message' => 'Request Has been approved', 'code' => 200]);
    }

    //API Access Token
    public function register(){
        $fields=$request->validate([
            'name' => 'required|string',
            'email'=> 'required|string',
            'password' => 'required|string|confirmed'
        ]);
        $user=User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            // 'password' => bcrypt($fields['password'])
            'password' => Hash::make($fields['password'])
        ]);   

        $token=$user->createToken('asri_token')->plainTextToken;

        $response=[
            'user'=> $user,
            'token'=> $token
        ];
        return $response;
    }


    //------------COMPANY---------------
    //pengaturan user selain superadmin, di masing masing perusahaan
    public function userChangePasswordIdx(){
        $user=User::where('id',$this->userid())->first();
        return view('tools/company/userchangepwd',compact('user'));
    }
    public function userChangePassword(Request $request, $id){
        $newpass=$request->input('form')['password'];
        $password=Hash::make($newpass);
        try{
            $execute=User::where('id','=',$id)
            ->update([
                'password'=>$password, 
              ]);
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Password berhasil diperbaharui', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    }

    
    public function userCompanyIndex(){
        $compid=$this->compid();
        $stores=function ($query) use ($compid){
            $query->where('comp_id',$compid);
        };
        $user=User::with(['stores'=>$stores])->whereHas('stores',$stores)->get();
        return view('tools/company/userindex',compact('user'));
    }
    public function userCompanyMain(Request $request){
        $stat=$request->userstate;
        $username=$request->username;
        $stores=Stores::where('comp_id',$this->compid())->get();
        $role=Role::where('name','!=','superadmin')->get();
        if($stat=="NEW"){ 
            return view('tools/company/usernew',compact('role','stores'));
        }
        if($stat=="UPDATES"){
            $user=User::with('stores')->where('username',$username)->first(); 
            return view('tools/company/userupdate',compact('user','stores'));
        }
        if($stat=="RESET"){
            $user=User::with('stores')->where('username',$username)->first(); 
            return view('tools/company/userpwdreset',compact("user"));
        }        
    }
    public function userCompanySave(Request $request){
        $roleids = $request->input('form')['role']; 
        $storeid = $request->input('form')['store'];
        $validator = Validator::make($request->all(), [
            '*.username' => 'required|string|unique:users',
            '*.name' => 'required|string',
            '*.email' => 'required|string|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        try{
            $user=new User;
            $username=$request->input('form')['username'];
            $user->username=$username;
            $user->store_id=$storeid;
            $user->name=$request->input('form')['name'];
            $user->email=$request->input('form')['email'];
            $user->password=Hash::make($request->input('form')['password']);
            $execute = $user->save();
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            $userid=User::where('username','=',$username)->first()->id;
            $role=Role::where('name','!=','superadmin')->get();
            foreach ($role as $key => $rl) {
                $id=strval($rl->id); 
                if (strpos($roleids, $id) !== false) {
                    $roleuser=new RoleUser;
                    $roleuser->user_id=$userid;
                    $roleuser->role_id=$rl->id;
                    $roleuser->timestamps = false;
                    $roleuser->save();
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Data Berhasil di Simpan', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }        
    }
    public function userCompanyUpdate(Request $request, $id){
        $name=$request->input('form')['name'];
        $storeid = $request->input('form')['storeid'];

        $validator = Validator::make($request->all(), [
            '*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }
        try{
            $execute=User::where('id','=',$id)
            ->update([
              'store_id'=>$storeid,
              'name'=>$name,
            ]);
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Data Berhasil di Simpan', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    }
    public function userCompanyPasswordReset(Request $request, $id){
        $newpass=$request->input('form')['password'];
        $password=Hash::make($newpass);
        try{
            $execute=User::where('id','=',$id)
            ->update([
                'password'=>$password, 
              ]);
            if (!$execute) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Password berhasil diperbaharui', 'code' => 200]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }        
    }

    
}
