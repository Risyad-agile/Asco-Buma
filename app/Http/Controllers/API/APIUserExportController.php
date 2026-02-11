<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class APIUserExportController extends Controller
{
    public function index(Request $request)
    {
        $since = $request->query('since');

        // $query = User::select('id','name','email','password' );
        $query = DB::table('users')
            ->select('id', 'name', 'email', 'password');

        if ($since) {
            $query->where('updated_at', '>=', $since);
        }

        $users = $query->get();

        return response()->json([
            'status' => 'success',
            'count'  => $users->count(),
            'data'   => $users
        ]);
    }
}
