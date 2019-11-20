<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Passwords\ResetPasswordNotification;

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
        $request = $this->postJson(route('auth.forgot'), $this->validParamaters());

        $request->assertStatus(200);

        $this->assertEquals('Password reset email sent.', $request->getData()->message);
    }

    /** @test */
    public function a_user_cannot_request_password_reset_with_a_non_existing_email()
    {
        $request = $this->postJson(route('auth.forgot'), $this->validParamaters([
            'email' => 'non@existing.com'
        ]));

        $this->assertEquals('Email could not be sent to this email address.', $request->getData()->message);
    }

    /** @test */
    public function user_can_reset_password()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));

        $request = $this->postJson(route('auth.reset-password'), $this->validParamaters([
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => Password::createToken($this->user),
        ]));

        $request->assertStatus(200);

        $this->assertTrue(Hash::check('newpassword', $this->user->fresh()->password));
        $this->assertEquals('Password reset successfully.', $request->getData()->message);
    }

    /** @test */
    public function user_cannot_reset_password_with_invalid_token()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));

        $request = $this->postJson(route('auth.reset-password'), $this->validParamaters([
            'email' => 'non@existent.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => '',
        ]));

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
        $this->assertEquals('The given data was invalid.', $request->getData()->message);
    }
}
