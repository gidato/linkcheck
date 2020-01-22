<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\ApprovedRedirect;
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

$factory->define(ApprovedRedirect::class, function (Faker $faker) {
    return [
        'from_url' => new Url($faker->url),
        'to_url' => new Url($faker->url),
    ];
});
