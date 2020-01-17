<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        define('SEED_SITE_1', env('DB_SEED_SITE_1', 'http://localhost/'));
        define('SEED_SITE_2', env('DB_SEED_SITE_2', 'http://my.localhost/'));
        define('SEED_SITE_3', env('DB_SEED_SITE_3', 'http://localhost/landlord'));
        $this->call(UsersTableSeeder::class);
        $this->call(SitesTableSeeder::class);
        $this->call(FilterSeeder::class);
        $this->call(ScansTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(OwnersTableSeeder::class);
    }
}
