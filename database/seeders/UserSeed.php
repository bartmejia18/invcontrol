<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_list = Permission::create(['name' => 'user.list']);
        $user_view = Permission::create(['name' => 'user.view']);
        $user_create = Permission::create(['name' => 'user.update']);
        $user_update = Permission::create(['name' => 'user.create']);
        $user_delete = Permission::create(['name' => 'user.delete']);

        $admin_role = Role::create(['name' => 'admin']);
        $admin_role->givePermissionTo([
            $user_create,
            $user_update,
            $user_view,
            $user_list,
            $user_delete
        ]);

        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('123456'),
        ]);

        $admin->assignRole($admin_role);
        $admin->givePermissionTo([
            $user_create,
            $user_update,
            $user_view,
            $user_list,
            $user_delete
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@mail.com',
            'password' => Hash::make('123456'),
        ]);

        $user_role = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            $user_list
        ]);
        $user->assignRole($user_role);
        $admin->givePermissionTo([
            $user_list
        ]);
    }
}
