<?php

use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insert([
            'scan_id' => 2,
            'url' => SEED_SITE_1,
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 0,
            'mime_type' => 'text/html',
            'html_errors' => '[]',
            'status_code' => 200,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 2,
            'url' => SEED_SITE_1 . 'landlords',
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 1,
            'mime_type' => 'text/html',
            'html_errors' => '[]',
            'status_code' => 200,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 2,
            'url' => SEED_SITE_1 . 'tenants',
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 1,
            'mime_type' => 'text/html',
            'html_errors' => '[]',
            'status_code' => 200,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 1,
            'referred_id' => 1,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 1,
            'referred_id' => 2,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 1,
            'referred_id' => 3,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 2,
            'referred_id' => 3,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);



        DB::table('pages')->insert([
            'scan_id' => 3,
            'url' => SEED_SITE_2,
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 0,
            'mime_type' => 'text/html',
            'html_errors' => '[]',
            'status_code' => 200,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 3,
            'url' => SEED_SITE_2 . 'rover',
            'method' => 'get',
            'is_external' => false,
            'checked' => false,
            'depth' => 1,
            'mime_type' => null,
            'status_code' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 3,
            'url' => SEED_SITE_2 . 'toyota',
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 1,
            'mime_type' => null,
            'status_code' => 404,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 4,
            'referred_id' => 4,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

    ]);

        DB::table('page_references')->insert([
            'referrer_id' => 4,
            'referred_id' => 5,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 4,
            'referred_id' => 6,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',
            'target' => '_blank'
        ]);



        DB::table('pages')->insert([
            'scan_id' => 4,
            'url' => SEED_SITE_3 . 'landlords',
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 0,
            'mime_type' => 'text/html',
            'html_errors' => '[]',
            'status_code' => 200,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 4,
            'url' => SEED_SITE_1,
            'method' => 'get',
            'is_external' => true,
            'checked' => true,
            'depth' => 1,
            'mime_type' => 'text/html',
            'html_errors' => '[]',
            'status_code' => 200,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 4,
            'url' => SEED_SITE_3 . '/faq',
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 1,
            'mime_type' => null,
            'status_code' => 404,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('pages')->insert([
            'scan_id' => 4,
            'url' => SEED_SITE_3 . '/faqs',
            'method' => 'get',
            'is_external' => false,
            'checked' => true,
            'depth' => 1,
            'mime_type' => null,
            'status_code' => 302,
            'redirect' => SEED_SITE_3 . '/faq',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 7,
            'referred_id' => 7,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 7,
            'referred_id' => 8,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);

        DB::table('page_references')->insert([
            'referrer_id' => 7,
            'referred_id' => 9,
            'type' => 'Html',
            'tag' => 'a',
            'attribute' => 'href',

        ]);
    }
}
