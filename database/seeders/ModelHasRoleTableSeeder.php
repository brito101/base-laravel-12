<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buscar usuários pelos emails ao invés de IDs fixos
        $programador = User::where('email', env('PROGRAMMER_EMAIL'))->first();
        $administrador = User::where('email', env('ADMIN_EMAIL'))->first();

        DB::table('model_has_roles')->insert([
            [
                'role_id' => 1,
                'model_type' => 'App\Models\User',
                'model_id' => $programador->id,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\Models\User',
                'model_id' => $administrador->id,
            ],
        ]);
    }
}
