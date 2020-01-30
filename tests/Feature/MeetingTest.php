<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_create()
    {
        $organizer = factory(User::class)->create();

        $startTime = $this->faker->dateTimeBetween('now', '+2 years');
        $endTime = $this->faker->dateTimeBetween(
            $startTime->format('Y-m-d H:i:s').' +1 hours',
            $startTime->format('Y-m-d H:i:s').' +6 hours'
        );

        $response = $this->actingAs($organizer, 'api')->postJson(
            route('api.meeting.create'),
            [
                'name' => $this->faker->company,
                'description' => $this->faker->paragraph,
                'location' => $this->faker->address,
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s')
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_index()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->getJson(
            route('api.meeting.list')
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 1
            ]);
    }

    public function test_search_found()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting1 = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting1);
        $meeting2 = factory(Meeting::class)->make(['name' => 'My Fake Meetings']);
        $organizer->meetings()->save($meeting2);

        $response = $this->actingAs($user, 'api')->getJson(
            route(
                'api.meeting.list',
                [
                    'name' => $meeting1->name
                ]
            )
        );

        // search for partial name
        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 1
            ]);

        $response = $this->actingAs($user, 'api')->getJson(
            route('api.meeting.list',
            [
                'name' => 'Fake Meeting'
            ])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 1
            ]);
    }

    public function test_search_notFound()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->getJson(
            route(
                'api.meeting.list',
                [
                    'name' => 'Wrong Meeting Name'
                ]
            )
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 0
            ]);
    }

    public function test_update()
    {
        $organizer = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($organizer, 'api')->putJson(
            route('api.meeting.update', ['meeting' => $meeting->getKey()]),
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

        $response = $this->actingAs($user, 'api')->putJson(
            route('api.meeting.update', ['meeting' => $meeting->getKey()]),
            [
                'name' => 'Fake Meeting'
            ]
        );

        $response
            ->assertStatus(403)
            /*->assertJson([
                'success' => false
            ])*/;
    }

    public function test_delete()
    {
        $organizer = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($organizer, 'api')->deleteJson(
            route('api.meeting.delete', ['meeting' => $meeting->getKey()])
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

        $response = $this->actingAs($user, 'api')->deleteJson(
            route('api.meeting.delete', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(403)
            /*->assertJson([
                'success' => false
            ])*/;
    }
}
