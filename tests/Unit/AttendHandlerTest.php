<?php

namespace Tests\Unit;

use App\Handlers\AttendHandler;
use App\Models\Attend;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create()
    {
        $meeting = factory(Meeting::class)->create();
        $user = factory(User::class)->create();

        $attend = AttendHandler::create($user, $meeting);

        $this->assertNotNull($attend);
        $this->assertTrue($attend instanceof Attend);
        $this->assertEquals(Attend::USER_ATTENDING, $attend->attending);
    }

    public function test_get()
    {
        $meeting = factory(Meeting::class)->create();
        $user = factory(User::class)->create();

        // user states they are attending the meeting
        $attend = AttendHandler::create($user, $meeting);

        // get the attendance record (intersection of the meeting and the user)
        $a1 = AttendHandler::get($user, $meeting);
        $this->assertTrue($attend->is($a1));
        $this->assertTrue($attend->user->is($user));
        $this->assertTrue($attend->meeting->is($meeting));
    }

    public function test_search()
    {
        $meeting = factory(Meeting::class)->create();
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create();

        AttendHandler::create($user1, $meeting);
        AttendHandler::create($user2, $meeting);
        $a3 = AttendHandler::create($user3, $meeting);
        AttendHandler::update($a3, ['attending' => Attend::USER_NOT_ATTENDING]);

        $c1 = AttendHandler::search($meeting, ['attending' => Attend::USER_ATTENDING]);
        $this->assertCount(2, $c1);

        $c2 = AttendHandler::search($meeting, ['user.name' => $user1->name]);
        $this->assertCount(1, $c2);
    }

    public function test_update()
    {
        $meeting = factory(Meeting::class)->create();
        $user = factory(User::class)->create();

        $attend = AttendHandler::create($user, $meeting);

        $r = AttendHandler::update($attend, ['attending' => Attend::USER_MAYBE_ATTENDING]);

        $this->assertTrue($r);
    }
}
