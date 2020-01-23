<?php

use Illuminate\Database\Seeder;

use Ramsey\Uuid\Uuid;

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
        define('SEED_SITE_1_VERIFICATION_CODE', env('DB_SEED_SITE_1_VERIFICATION_CODE', Uuid::uuid4()));

        define('SEED_SITE_2', env('DB_SEED_SITE_2', 'http://my.localhost/'));
        define('SEED_SITE_3', env('DB_SEED_SITE_3', 'http://localhost/landlord'));

        define('SEED_USER_NAME', env('DB_SEED_USER_NAME', 'Sample User'));
        define('SEED_USER_EMAIL', env('DB_SEED_USER_EMAIL', 'user@example.com'));
        define('SEED_USER_PASSWORD', env('DB_SEED_USER_PASSWORD', 'password'));

        $this->call(UsersTableSeeder::class);
        $this->call(SitesTableSeeder::class);
        $this->call(FilterSeeder::class);
        $this->call(ScansTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(OwnersTableSeeder::class);
    }
}
