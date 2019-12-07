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

        $response = $this->actingAs($user, 'api')->post(
            "/api/meeting/{$meeting->id}/attend"
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
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

        $response = $this->actingAs($organizer, 'api')->get(
            "/api/meeting/{$meeting->id}/attend/{$user->id}"
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

        $response = $this->actingAs($user, 'api')->put(
            "/api/meeting/{$meeting->id}/attend",
            ['attending' => Attend::USER_MAYBE_ATTENDING]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_delete()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->attends()->save($attendee);

        $response = $this->actingAs($user, 'api')->delete(
            "/api/meeting/{$meeting->id}/attend"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }
}
