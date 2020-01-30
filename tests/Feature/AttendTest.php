<?php

namespace Tests\Feature;

use App\Models\Attend;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendTest extends TestCase
{
    use RefreshDatabase;

    public function test_store()
    {
        $organizer = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->postJson(
            route('api.meeting.attend.create', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_index()
    {
        $organizer = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $user1 = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user1->attends()->save($attendee);

        $user2 = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user2->attends()->save($attendee);

        $response = $this->actingAs($organizer, 'api')->getJson(
            route('api.meeting.attend.list', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 2
            ]);
    }

    public function test_show()
    {
        $organizer = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $user = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->attends()->save($attendee);

        $response = $this->actingAs($organizer, 'api')->getJson(
            route(
                'api.meeting.attend.show',
                ['meeting' => $meeting->getKey(), 'user' => $user->getKey()]
            )
        );

        $response
            ->assertStatus(200)
            //->assertJsonStructure(['success', 'attendee'])
            ->assertJson([
                'success' => true,
                'attendee' => [
                    'meeting_id' => $meeting->id,
                    'user_id' => $user->id,
                    'attending' => Attend::USER_ATTENDING
                ]
            ]);
    }

    public function test_update()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->attends()->save($attendee);

        $response = $this->actingAs($user, 'api')->putJson(
            route('api.meeting.attend.update', ['meeting' => $meeting->getKey()]),
            ['attending' => Attend::USER_MAYBE_ATTENDING]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_update_invalidValue()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->attends()->save($attendee);

        $response = $this->actingAs($user, 'api')->putJson(
            route('api.meeting.attend.update', ['meeting' => $meeting->getKey()]),
            ['attending' => 'not sure']
        );

        $response
            ->assertStatus(422)
            /*->assertJson([
                'success' => true
            ])*/;
    }

    public function test_delete()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->attends()->save($attendee);

        $response = $this->actingAs($user, 'api')->deleteJson(
            route('api.meeting.attend.delete', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }
}
