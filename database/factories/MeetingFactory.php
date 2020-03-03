<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\User;
use App\Models\Meeting;
use Faker\Generator as Faker;

$factory->define(Meeting::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'name' => $faker->company,
        'description' => $faker->text,
        'location' => $faker->address,
        'start_time' => $faker->dateTime,
        'end_time' => $faker->dateTime,
    ];
});
