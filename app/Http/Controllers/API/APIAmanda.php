<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIAmanda extends Controller
{
    public function amandaReceiver(Request $request){
        $method=$request->get('method');

        switch ($method) {
            case 'cek.server':
                return response()->json("SUCCESS");
                break;
            case 'save.purchase':
                return $method;
                break;
            case 'save.purchase.return':
                return $method;
                break;
            case 'save.official.memos':
                return response()->json("SAMPE");
                $officialmemos=$request->get('officialmemos');
                $exec=$this->saveAmandaOfficialMemos($officialmemos);
                return response()->json("SAMPE");
                break;
            
            default:
                # code...
                break;
        }
        // return $request;
    }
    
    private function saveAmandaOfficialMemos($officialmemos){
        //simpan pada tabel amanda di server
        //update status saja jika sudah ada
        //simpan sesuai dengan tabel yang dituju
        return "SAMPE";
    }
}
