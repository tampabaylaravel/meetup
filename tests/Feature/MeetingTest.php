<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Faker\Factory as FactorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test meeting API endpoints
     *
     * @return void
     */
    public function test_create()
    {
        $faker = FactorFactory::create();
        $organizer = factory(User::class)->create();

        $response = $this->actingAs($organizer, 'api')->post(
            '/api/meeting',
            [
                'name' => $faker->company,
                'description' => $faker->paragraph,
                'location' => $faker->address,
                'start_time' => $faker->dateTime,
                'end_time' => $faker->dateTime
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_update()
    {
        $organizer = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($organizer, 'api')->put(
            "/api/meeting/{$meeting->id}",
            [
                'name' => 'Fake Meeting'
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_update_onlyByOrganizer()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->put(
            "/api/meeting/{$meeting->id}",
            [
                'name' => 'Fake Meeting'
            ]
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_delete()
    {
        $organizer = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($organizer, 'api')->delete(
            "/api/meeting/{$meeting->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_delete_onlyByOrganizer()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->delete(
            "/api/meeting/{$meeting->id}"
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false
            ]);
    }
}
