<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\RoleUser;

class UserMigrationController extends Controller
{
    #migrasi tabel dilakukan secara manual
    #- import database asli
    #- import agile user migration

    public function assignUserRole(){
        $roleusers=RoleUser::all();
        $users_without_any_roles = User::doesntHave('roles')->get();
        foreach ($roleusers as $key => $role) {
            $userid=$role->user_id;
            $roleid=$role->role_id;
            $user=User::where('id',$userid)->first();
            $rolename=Role::findById($roleid)->name;
            $user->assignRole($rolename);
            print($user);
        }
        print("\n=================Migrasi User Selesai=================");

    }
}
