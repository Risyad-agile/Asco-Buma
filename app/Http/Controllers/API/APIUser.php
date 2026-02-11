<?php

namespace App\Http\Controllers\API; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use App\Models\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use GuzzleHttp\Client; 
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\PaymentsController;

// supaya bisa buat token harus ada personal access
// php artisan passport:install


class APIUser extends Controller
{
    public $successStatus = 200;
    public function ping(){
        #modul untuk test koneksi ke server
        // return response()->json(['status'=>'success','message'=>'Reply From Server','code' => 200]); 
        return response()->json('success'); 
    }
    public function login(Request $request){  
        $user= User::with('store.company')->where('username',$request->username)->first();
    	if(!$user){
            return response()->json(['status'=>'error','message'=>'User not found','user'=>$user]); 
        }
        //periksan data pembayaran
        $payControl=new PaymentsController;
        if($payControl->paymentActiveStoreExist($user->store_id)==false){
            return response()->json(['status'=>'error','message'=>'Silakan Aktifkan Pembayaran','user'=>$user]); 
        }
        if(Hash::check($request->password, $user->password)){
            $user->rolename=$user->roles->first()->name;
            return response()->json(['status'=>'success','message'=>'OK','user'=>$user]); 
		}else{
			return response()->json(['status'=>'error','message'=>'Password didnt match','user'=>null]); 
        }
    }
    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        // register via api 
        // nunggu approval
        // selama belum di approve tetap tidak bisa login
        // aproval dikirim via email berisi token
        // sekaligus lokasi download file dokuemetasi

        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'organization' => 'required',
            'email' => 'required|email|unique:users', 
            'password' => 'required', 
            'password_confirm' => 'required|same:password', 
            'username' => ['required','numeric','unique:users'],
            'role' => 'required', 
        ],[ 
            'username.required' => 'username usualy using Mobile or Phone Number', 
            'username.numeric' => 'username usualy using Mobile or Phone Number', 
            'role.required' => 'role fill with either : Manager, Supervisor or User', 
        ]);

        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 404);            
        }

        $input = $request->all(); 
        // $input['password'] = bcrypt($input['password']); 
        $input['password'] =Hash::make($input['password']); 
        $user = User::create($input); 

        // $success['token'] =  $user->createToken('asri-token')-> accessToken;  token create pada saat approve
        $success['user'] =  $user;
        $success['message'] = "Register Success, email confirmation and detail information will be send if request accepted (max 2x24)";

        return response()->json(['success'=>$success], $this->successStatus); 
    }
/** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this-> successStatus); 
    } 

    //Ini tidak dihapus dan tidak dipakai juga, kalau pake metode ini
    //ketika eksekusi pake postmant headernya harus app/json
    public function registerAPI(Request $request){
       
        $fields=$request->validate([
            'name' => 'required|string',
            'email'=> 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $user=User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'username' =>'0812',
            
        ]);   

        $token=$user->createToken('asri_token')->accessToken;

        $response=[
            'user'=> $user,
            'token'=> $token
        ];
        return $response;
    }
    public function loginAPI(Request $request){
        $fields=$request->validate([
            'email'=> 'required|string',
            'password' => 'required|string'
        ]);
        if(!Auth::attempt(['email' => $fields['email'], 'password' => $fields['password']])){
            return response()->json([
                'state' =>false,
                'message' => 'Invalid Username or Password',
                'code' =>404
            ]);
        }

        $token=Auth::user()->createToken('asri_token')->accessToken;
        return response()->json([
            'state' =>true,
            'message' => 'Login Success',
            'user'=>Auth::user(),
            'token'=>$token,
            'code' =>200
        ]);
    }
    public function logoutAPI(Request $request){
        $fields=$request->validate([
            'email'=> 'required|string',
            'password' => 'required|string'
        ]);
        // if(!Auth::attempt(['email' => $fields['email'], 'password' => $fields['password']])){
        //     return response()->json([
        //         'state' =>false,
        //         'message' => 'Invalid Username or Password',
        //         'code' =>404
        //     ]);
        // }

        $token=Auth::user()->token();
        $token->revoke();

        return response()->json([
            'state' =>true,
            'message' => 'Logout Success',
            'user'=>Auth::user(),
            'token'=>$token,
            'code' =>200
        ]);
    }
}
