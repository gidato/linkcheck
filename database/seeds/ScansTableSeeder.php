<?php

use Illuminate\Database\Seeder;

use App\Site;

class ScansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('scans')->insert([
            'site_id' => Site::where('url', SEED_SITE_1)->get()[0]->id,
            'status' => 'errors',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('scans')->insert([
            'site_id' => Site::where('url', SEED_SITE_1)->get()[0]->id,
            'status' => 'success',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('scans')->insert([
            'site_id' => Site::where('url', SEED_SITE_2)->get()[0]->id,
            'status' => 'processing',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('scans')->insert([
            'site_id' => Site::where('url', SEED_SITE_3)->get()[0]->id,
            'status' => 'errors',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
