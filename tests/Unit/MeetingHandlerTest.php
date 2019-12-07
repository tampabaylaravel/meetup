<?php

namespace Tests\Unit;

use App\Handlers\MeetingHandler;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as FactorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create()
    {
        $faker = FactorFactory::create();
        $organizer = factory(User::class)->create();

        $meeting = MeetingHandler::create(
            $organizer,
            [
                'name' => $faker->company,
                'description' => $faker->paragraph,
                'location' => $faker->address,
                'start_time' => $faker->dateTime,
                'end_time' => $faker->dateTime
            ]
        );

        $this->assertNotNull($meeting);
        $this->assertTrue($meeting instanceof Meeting);
    }

    public function test_search()
    {
        $user = factory(User::class)->create();
        $meeting1 = factory(Meeting::class)->make();
        $user->meetings()->save($meeting1);
        $meeting2 = factory(Meeting::class)->make();
        $user->meetings()->save($meeting2);
        $meeting3 = factory(Meeting::class)->make();
        $user->meetings()->save($meeting3);

        $m2 = MeetingHandler::search(['name' => $meeting2->name])->first();
        $this->assertNotNull($m2);
        $this->assertTrue($m2->is($meeting2));

        $f1 = MeetingHandler::search(['name' => 'Wrong'])->first();
        $this->assertNull($f1);
    }

    public function test_update()
    {
        $user = factory(User::class)->create();
        $meeting = factory(Meeting::class)->make();
        $user->meetings()->save($meeting);

        $r = MeetingHandler::update($meeting, ['name' => 'Fake Meeting', 'start_time' => Carbon::now()]);

        $this->assertTrue($r);
    }

    public function test_delete()
    {
        $user = factory(User::class)->create();
        $meeting1 = factory(Meeting::class)->make();
        $user->meetings()->save($meeting1);
        $meeting2 = factory(Meeting::class)->make();
        $user->meetings()->save($meeting2);

        $r = MeetingHandler::delete($meeting1);

        $this->assertTrue($r);
    }
}
