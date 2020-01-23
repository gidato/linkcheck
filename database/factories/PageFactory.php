<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Page;
use App\Support\Value\Url;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Page::class, function (Faker $faker) {
    return [
        'url' => new Url('http://localhost/'.$faker->uuid()),
        'method' => 'get',
        'is_external' => false,
        'checked' => true,
        'depth' => 1,
        'mime_type' => 'text/html',
        'status_code' => 200,
        'html_errors' => '[]',
        'exception' => ''
    ];
});
