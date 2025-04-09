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
            'password' => bcrypt('Qwerty0009@')
        ]);

        DB::table('users')->insert([
            'id' => Str::uuid(),
            'username' => 'Rohidtzz',
            'email' => 'rohid@gmail.com',
            'password' => bcrypt('Qwerty0009@')
        ]);

        if(config("app.env") == "production") {
            return;
        }

        DB::table('users')->insert([
            'id' => Str::uuid(),
            'username' => 'anang',
            'email' => 'anang@gmail.com',
            'password' => bcrypt('Qwerty0009@')
        ]);

        DB::table('users')->insert([
            'id' => Str::uuid(),
            'username' => 'prabowo',
            'email' => 'prabowo@gmail.com',
            'password' => bcrypt('Qwerty0009@')
        ]);
    }
}
