<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

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

        Mail::fake();
        Notification::fake();

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
        ], $override);
    }

    /** @test */
    public function a_user_can_request_password_reset()
    {
        $response = $this->postJson(route('auth.forgot'), $this->validParamaters([
            'email' => $this->user->email,
        ]));

        $response->assertStatus(200);

        $this->assertEquals('Password reset email sent.', $response->getData()->message);

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    /** @test */
    public function a_user_cannot_request_password_reset_with_a_non_existing_email()
    {
        $response = $this->postJson(route('auth.forgot'), $this->validParamaters([
            'email' => 'non@existing.com',
        ]));

        $this->assertEquals(
            'Email could not be sent to this email address.',
            $response->getData()->message
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_reset_password()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));

        $response = $this->postJson(route('auth.reset-password'), $this->validParamaters([
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => Password::createToken($this->user),
        ]));

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('newpassword', $this->user->fresh()->password));
        $this->assertEquals('Your password has been reset!', $response->getData()->message);
    }

    /** @test */
    public function a_token_is_required_for_a_user_to_reset_their_password()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));

        $response = $this->postJson(route('auth.reset-password'), $this->validParamaters([
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => '',
        ]));

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
        $this->assertEquals('The given data was invalid.', $response->getData()->message);
        $response->assertStatus(422);
    }

    /** @test */
    public function a_valid_token_is_required_for_a_user_to_reset_their_password()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));

        $response = $this->postJson(route('auth.reset-password'), $this->validParamaters([
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => 'some_invalid_token',
        ]));

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
        $this->assertEquals('This password reset token is invalid.', $response->getData()->message);
        $response->assertStatus(401);
    }

    /** @test */
    public function a_valid_email_must_be_sent_to_reset_their_password()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));

        $response = $this->postJson(route('auth.reset-password'), $this->validParamaters([
            'email' => 'invalid@email.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => Password::createToken($this->user),
        ]));

        $this->assertEquals("We can't find a user with that e-mail address.", $response->getData()->message);
        $response->assertStatus(400);
    }
}
