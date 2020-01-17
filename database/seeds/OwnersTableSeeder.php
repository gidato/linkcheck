<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use App\Site;

class OwnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        DB::table('owners')->insert([
            'site_id' => Site::where('url', SEED_SITE_1 )->get()[0]->id,
            'name' => $faker->name(),
            'email' => $faker->email(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
