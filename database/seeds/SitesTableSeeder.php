<?php

use Illuminate\Database\Seeder;

use Ramsey\Uuid\Uuid;

class SitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sites')->insert([
            'url' => SEED_SITE_1,
            'throttle' => 'default : default',
            'validation_code' => SEED_SITE_1_VERIFICATION_CODE,
            'validated' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

            DB::table('sites')->insert([
            'url' => SEED_SITE_2,
            'throttle' => 'default : 10',
            'validation_code' => Uuid::Uuid4(),
            'validated' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('sites')->insert([
            'url' => SEED_SITE_3,
            'throttle' => 'default : default',
            'validation_code' => Uuid::Uuid4(),
            'validated' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
