<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function store()
    {
        $organizer = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->postJson(
            route('api.meeting.reservation.create', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $attend = $user->reservations()->whereHas('meeting', function (Builder $query) use ($meeting) {
            $query->where($meeting->getKeyName(), $meeting->getKey());
        })->first();

        $this->assertNotNull($attend);
    }

    /**
     * @test
     */
    public function index()
    {
        $organizer = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $user1 = factory(User::class)->create();
        $attendee = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user1->reservations()->save($attendee);

        $user2 = factory(User::class)->create();
        $attendee = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user2->reservations()->save($attendee);

        $response = $this->actingAs($organizer, 'api')->getJson(
            route('api.meeting.reservation.list', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rowCount' => 2,
            ])
            ->assertJsonFragment([
                'attending' => Reservation::USER_ATTENDING,
            ])
            ->assertJsonFragment([
                'user_id' => $user1->getKey(),
            ])
            ->assertJsonFragment([
                'user_id' => $user2->getKey(),
            ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $organizer = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $organizer->meetings()->save($meeting);

        $user = factory(User::class)->create();
        $attendee = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->reservations()->save($attendee);

        $response = $this->actingAs($organizer, 'api')->getJson(
            route(
                'api.meeting.reservation.show',
                ['meeting' => $meeting->getKey(), 'user' => $user->getKey()]
            )
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'attendee' => [
                    'meeting_id' => $meeting->id,
                    'user_id' => $user->id,
                    'attending' => Reservation::USER_ATTENDING,
                ],
            ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->reservations()->save($attendee);

        $response = $this->actingAs($user, 'api')->putJson(
            route('api.meeting.reservation.update', ['meeting' => $meeting->getKey()]),
            ['attending' => Reservation::USER_MAYBE_ATTENDING]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $attend = $user->reservations()->whereHas('meeting', function (Builder $query) use ($meeting) {
            $query->where($meeting->getKeyName(), $meeting->getKey());
        })->first();

        $this->assertEquals(Reservation::USER_MAYBE_ATTENDING, $attend->attending);
    }

    /**
     * @test
     */
    public function update_invalid_value()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->reservations()->save($attendee);

        $response = $this->actingAs($user, 'api')->putJson(
            route('api.meeting.reservation.update', ['meeting' => $meeting->getKey()]),
            ['attending' => 'not sure']
        );

        $response
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function delete_will_delete_reservation()
    {
        $meeting = factory(Meeting::class)->create();

        $user = factory(User::class)->create();
        $attendee = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attendee->meeting()->associate($meeting);
        $user->reservations()->save($attendee);

        $response = $this->actingAs($user, 'api')->deleteJson(
            route('api.meeting.reservation.delete', ['meeting' => $meeting->getKey()])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $attend = $user->reservations()->whereHas('meeting', function (Builder $query) use ($meeting) {
            $query->where($meeting->getKeyName(), $meeting->getKey());
        })->first();

        $this->assertEquals(Reservation::USER_NOT_ATTENDING, $attend->attending);
    }
}
