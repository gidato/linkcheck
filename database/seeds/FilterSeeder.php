<?php

use Illuminate\Database\Seeder;

use App\Site;

class FilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('filters')->insert([
            'filterable_type' => Site::class,
            'filterable_id' => Site::where('url', SEED_SITE_1)->get()[0]->id,
            'key' => 'internal-only',
            'on' => true,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('filters')->insert([
            'filterable_type' => Site::class,
            'filterable_id' => Site::where('url', SEED_SITE_1)->get()[0]->id,
            'key' => 'internal-only',
            'on' => true,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('filters')->insert([
            'filterable_type' => Site::class,
            'filterable_id' => Site::where('url', SEED_SITE_3)->get()[0]->id,
            'key' => 'internal-only',
            'on' => true,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
