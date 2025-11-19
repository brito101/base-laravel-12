<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Programador',
                'email' => env('PROGRAMMER_EMAIL'),
                'password' => bcrypt(env('PROGRAMMER_PASSWD')),
                'created_at' => new DateTime('now'),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Administrator',
                'email' => env('ADMIN_EMAIL'),
                'password' => bcrypt(env('ADMIN_PASSWD')),
                'created_at' => new DateTime('now'),
            ],
        ]);
    }
}
