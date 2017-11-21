<?php

use Faker\Generator as Faker;
use App\Reply;
use App\Thread;
use App\User;
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

$factory->define( User::class , function (Faker $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define( Thread::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'text' => implode(' ', $faker->paragraphs),
        'user_id' => function(){
            return factory( User::class )->create()->id;
        },
    ];
});

$factory->define( Reply::class , function (Faker $faker) {
    return [
        'body' => $faker->paragraph ,
        'user_id' => function(){
            return factory( User::class )->create()->id;
        },
        'thread_id' => function(){
            return factory( Thread::class )->create()->id;
        }
    ];
});
