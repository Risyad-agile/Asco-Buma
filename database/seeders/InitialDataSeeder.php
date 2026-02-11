<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        // app()[PermissionsDemoSeeder::class]->forgetCachedPermissions();
        // php artisan migrate:fresh --seed --seeder=InitialDataSeeder
        // create permissions
        Permission::create(['name' => 'master']);
        Permission::create(['name' => 'transaction']);
        Permission::create(['name' => 'maker']);
        Permission::create(['name' => 'checker']);
        Permission::create(['name' => 'approval']);
        Permission::create(['name' => 'finance']);  
        Permission::create(['name' => 'reporting']);  
        Permission::create(['name' => 'security']); 
        Permission::create(['name' => 'holding']); 

        $role1 = Role::create(['name' => 'superadmin']);
        $role1->givePermissionTo('master');
        $role1->givePermissionTo('transaction');
        $role1->givePermissionTo('maker');
        $role1->givePermissionTo('checker');
        $role1->givePermissionTo('approval');
        $role1->givePermissionTo('reporting');
        $role1->givePermissionTo('security');
        $role1->givePermissionTo('holding');

        $role2 = Role::create(['name' => 'agile-manager']);
        $role2->givePermissionTo('master');
        $role2->givePermissionTo('transaction'); 
        $role2->givePermissionTo('maker');
        $role2->givePermissionTo('checker');
        $role2->givePermissionTo('approval');
        $role2->givePermissionTo('reporting');
        $role2->givePermissionTo('holding'); 
        
        $role3 = Role::create(['name' => 'agile-helpdesk']);
        $role3->givePermissionTo('master');
        $role3->givePermissionTo('transaction');
        $role3->givePermissionTo('maker');
        $role3->givePermissionTo('checker'); 
        $role3->givePermissionTo('reporting'); 

        $role4 = Role::create(['name' => 'agile-finance']);
        $role4->givePermissionTo('master');
        $role4->givePermissionTo('transaction');
        $role4->givePermissionTo('finance');
        $role4->givePermissionTo('reporting');  

        $role5 = Role::create(['name' => 'agile-admin']);
        $role5->givePermissionTo('master');
        $role5->givePermissionTo('transaction');
        $role5->givePermissionTo('reporting'); 
        $role5->givePermissionTo('security'); 

        $role6 = Role::create(['name' => 'manager']);
        $role6->givePermissionTo('master');
        $role6->givePermissionTo('transaction'); 
        $role6->givePermissionTo('approval');
        $role6->givePermissionTo('reporting'); 
        $role6->givePermissionTo('holding'); 

        $role7 = Role::create(['name' => 'supervisor']);
        $role7->givePermissionTo('master');
        $role7->givePermissionTo('transaction'); 
        $role7->givePermissionTo('checker'); 
        $role7->givePermissionTo('reporting');

        $role8 = Role::create(['name' => 'user']);
        $role8->givePermissionTo('master');
        $role8->givePermissionTo('transaction'); 
        $role8->givePermissionTo('maker');
        $role8->givePermissionTo('reporting');

        $role9 = Role::create(['name' => 'admin']);
        $role9->givePermissionTo('master');
        $role9->givePermissionTo('transaction'); 
        $role9->givePermissionTo('reporting');
        $role9->givePermissionTo('security');

        $this->call(CompaniesSeeder::class); 

        $user = \App\Models\User::create([
            'name' => 'Wawan Hartawan',
            'email' => 'harta@agile.co.id',
            'password' => Hash::make('harta'),
            'username' => '08121192926',
            'comp_id' =>1,
            'approved'=>1,
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $user->assignRole($role1); 
        $asriManager = \App\Models\User::create([
            'name' => 'Asri Manajer',
            'email' => 'agile-manager@agile.co.id',
            'password' => Hash::make('asri'),
            'username' => '0000',
            'comp_id' =>1,
            'approved'=>1,
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $asriManager->assignRole($role2); 
         

        //dummy data, asri as client
        $clientManager = \App\Models\User::create([
            'name' => 'Manajer',
            'email' => 'manager@agile.co.id',
            'password' => Hash::make('manager'),
            'username' => '0001',
            'comp_id' =>1,
            'approved'=>1,
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $clientManager->assignRole($role6); 
        $clientSupervisor = \App\Models\User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@agile.co.id',
            'password' => Hash::make('supervisor'),
            'username' => '0002',
            'comp_id' =>1,
            'approved'=>1,
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $clientSupervisor->assignRole($role7); 
        $clientUser = \App\Models\User::create([
            'name' => 'User',
            'email' => 'user@agile.co.id',
            'password' => Hash::make('user'),
            'username' => '0003',
            'comp_id' =>1,
            'approved'=>1,
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $clientUser->assignRole($role8); 
        $clientAdmin = \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@agile.co.id',
            'password' => Hash::make('administrator'),
            'username' => '0004',
            'comp_id' =>1,
            'approved'=>1,
            'email_verified_at' =>date("Y-m-d H:i:s"),
        ]);
        $clientAdmin->assignRole($role9); 
    }
}

