<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Site;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
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

$factory->define(Site::class, function (Faker $faker) {
    return [
        'url' => new Url('http://localhost'),
        'throttle' => new Throttle('default : default'),
        'validation_code' => '9f249064-c834-4523-b3fb-b9146dc386dc',
        'validated' => true,
    ];
});
