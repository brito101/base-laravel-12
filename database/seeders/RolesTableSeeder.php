<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        DB::table('roles')->insert([
            [
                'name' => 'Programador',
                'guard_name' => 'web',
                'created_at' => new DateTime('now'),
            ],
            [
                'name' => 'Administrador',
                'guard_name' => 'web',
                'created_at' => new DateTime('now'),
            ],
            [
                'name' => 'Usuário',
                'guard_name' => 'web',
                'created_at' => new DateTime('now'),
            ],
        ]);
    }
}
