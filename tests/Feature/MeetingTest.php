<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function create()
    {
        $organizer = factory(User::class)->create();

        $startTime = $this->faker->dateTimeBetween('now', '+2 years');
        $endTime = $this->faker->dateTimeBetween(
            $startTime->format('Y-m-d H:i:s') . ' +1 hours',
            $startTime->format('Y-m-d H:i:s') . ' +6 hours'
        );

        $meetingName = 'Secret Pirate Meeting';

        $response = $this->actingAs($organizer, 'api')->postJson(
            route('api.meeting.create'),
            [
                'name' => $meetingName,
                'description' => 'Brainstorm about better pilfering methods and hiding places.',
                'location' => 'Third Secret Island',
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'meeting' => [
                    'name' => $meetingName,
                ],
            ]);

        $meeting = Meeting::first();
        $this->assertTrue($organizer->is($meeting->user));
        $this->assertEquals($meetingName, $meeting->name);
    }

    /**
     * @test
     */
    public function index()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting1 = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting1);

        $meeting2 = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting2);

        $response = $this->actingAs($user, 'api')->getJson(
            route('api.meeting.list')
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 2,
            ])
            ->assertJsonFragment([
                'name' => $meeting1->name,
            ]);
    }

    /**
     * @test
     */
    public function search_found_full_name()
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
                    'name' => $meeting1->name,
                ]
            )
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 1,
            ])
            ->assertJsonFragment([
                'name' => $meeting1->name,
            ]);
    }

    /**
     * @test
     */
    public function search_found_partial_name()
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
                    'name' => 'Fake Meeting',
                ]
            )
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 1,
            ])
            ->assertJsonFragment([
                'name' => $meeting2->name,
            ]);
    }

    /**
     * @test
     */
    public function search_not_found()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->getJson(
            route(
                'api.meeting.list',
                [
                    'name' => 'Wrong Meeting Name',
                ]
            )
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 0,
            ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $organizer = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $meetingName = 'Fake Meeting';

        $response = $this->actingAs($organizer, 'api')->putJson(
            route('api.meeting.update', ['meeting' => $meeting->getKey()]),
            [
                'name' => $meetingName,
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $meeting = Meeting::first();
        $this->assertTrue($organizer->is($meeting->user));
        $this->assertEquals($meetingName, $meeting->name);
    }

    /**
     * @test
     */
    public function update_only_by_organizer()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->putJson(
            route('api.meeting.update', ['meeting' => $meeting->getKey()]),
            [
                'name' => 'Fake Meeting',
            ]
        );

        $response
            ->assertStatus(403);
    }

    /**
     * @test
     */
    public function delete_will_delete_meeting()
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
                'success' => true,
            ]);

        $this->assertEquals(0, Meeting::count());
    }

    /**
     * @test
     */
    public function delete_only_by_organizer()
    {
        $organizer = factory(User::class)->create();
        $user = factory(User::class)->create();

        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $response = $this->actingAs($user, 'api')->deleteJson(
            route('api.meeting.delete', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(403);

        $this->assertEquals(1, Meeting::count());
    }
}
