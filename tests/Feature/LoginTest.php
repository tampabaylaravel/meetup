<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Traits\InteractsWithJWT;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase, InteractsWithJWT;

    /**
     * @var User
     */
    protected $user;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'email' => 'test@example.org',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * @param  array  $override
     * @return array
     */
    private function validParamaters($override = [])
    {
        return array_merge([
            'email' => 'test@example.org',
            'password' => 'password',
        ], $override);
    }

    /** @test */
    public function a_user_can_login_and_retrieve_a_valid_token()
    {
        $response = $this->postJson(route('auth.login'), $this->validParamaters());

        $response->assertStatus(200);

        $token = $response->getData()->token;
        $this->assertNotNull($token);

        $decodedToken = $this->decodeJWT($token);
        $this->assertEquals($this->user->getKey(), $decodedToken->sub);
    }

    /** @test */
    public function an_email_is_required_to_login()
    {
        $response = $this->postJson(route('auth.login'), $this->validParamaters([
            'email' => null,
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_valid_email_is_required_to_login()
    {
        $response = $this->postJson(route('auth.login'), $this->validParamaters([
            'email' => 'not-an-email-address',
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_password_is_required_to_login()
    {
        $response = $this->postJson(route('auth.login'), $this->validParamaters([
            'password' => null,
        ]));

        $response->assertJsonValidationErrors('password');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_400_response_will_be_returned_if_you_use_an_email_that_doesnt_exist()
    {
        $response = $this->postJson(route('auth.login'), $this->validParamaters([
            'email' => 'non-existant-email@example.org',
        ]));

        $response->assertStatus(400);
    }

    /** @test */
    public function a_400_response_will_be_returned_if_you_use_an_invalid_passowrd()
    {
        $response = $this->postJson(route('auth.login'), $this->validParamaters([
            'password' => 'not the right password for this user',
        ]));

        $response->assertStatus(400);
    }

    /** @test */
    public function login_will_be_throttled_after_5_attempts()
    {
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();

        $response = $this->badLoginAttempt();

        $response->assertStatus(429);
    }

    /**
     * @return TestResponse
     */
    private function badLoginAttempt()
    {
        return $this->postJson(route('auth.login'), $this->validParamaters([
            'password' => 'invalid',
        ]));
    }

    /** @test */
    public function throttled_users_cant_attempt_again_for_1_minute()
    {
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        Carbon::setTestNow(now()->addSeconds(59));

        $response = $this->badLoginAttempt();

        $response->assertStatus(429);
    }

    /** @test */
    public function throttled_users_can_attempt_again_after_1_minute()
    {
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        $this->badLoginAttempt();
        Carbon::setTestNow(now()->addSeconds(61));

        $response = $this->badLoginAttempt();

        $response->assertStatus(400);
    }
}
