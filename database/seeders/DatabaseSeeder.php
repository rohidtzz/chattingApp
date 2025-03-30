<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('secret')
        ]);

        DB::table('users')->insert([
            'id' => Str::uuid(),
            'username' => 'user',
            'email' => 'user@gmail.com',
            'password' => bcrypt('secret')
        ]);
    }
}
