<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

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
            'name' => SEED_USER_NAME,
            'email' => SEED_USER_EMAIL,
            'password' => Hash::make(SEED_USER_PASSWORD),
            'created_at' => now(),
            'updated_at' => now()
        ]);

    }
}
