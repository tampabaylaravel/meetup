<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Firebase\JWT\JWT;
use App\Traits\InteractsWithJWT;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase, InteractsWithJWT;

    /**
     * @param  array  $override
     * @return array
     */
    private function validParamaters($override = [])
    {
        return array_merge([
            'name' => 'Test User Name',
            'email' => 'test@example.org',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $override);
    }

    /** @test */
    public function a_guests_can_register_for_an_account()
    {
        $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => 'Test User Name',
            'email' => 'test@example.org',
        ]))->assertStatus(200);

        $user = User::first();
        $this->assertEquals('Test User Name', $user->name);
        $this->assertEquals('test@example.org', $user->email);
    }

    /** @test */
    public function a_valid_token_is_returned_when_the_user_registers()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters());

        $token = $request->getData()->token;
        $this->assertNotNull($token);

        $decodedToken = $this->decodeJWT($token);
        $this->assertEquals(User::first()->getKey(), $decodedToken->sub);
    }

    /** @test */
    public function an_name_is_required()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => null,
        ]));

        $request->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_name_must_be_a_string()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => 12345,
        ]));

        $request->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_name_cannot_be_longer_than_255_characters()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => str_repeat('a', 256),
        ]));

        $request->assertJsonValidationErrors('name');
    }

    /** @test */
    public function an_email_is_required()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => null,
        ]));

        $request->assertJsonValidationErrors('email');
    }

    /** @test */
    public function an_email_must_be_a_string()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => 12345,
        ]));

        $request->assertJsonValidationErrors('email');
    }

    /** @test */
    public function an_email_cannot_be_longer_than_255_characters()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => str_repeat('1', 255) . '@example.org',
        ]));

        $request->assertJsonValidationErrors('email');
    }

    /** @test */
    public function an_email_must_be_a_valid_email()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => 'not-a-valid-email',
        ]));

        $request->assertJsonValidationErrors('email');
    }

    /** @test */
    public function an_email_must_be_unique()
    {
        $user = factory(User::class)->create();

        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => $user->email,
        ]));

        $request->assertJsonValidationErrors('email');
    }

    /** @test */
    public function a_password_is_required()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => null,
        ]));

        $request->assertJsonValidationErrors('password');
    }

    /** @test */
    public function a_password_must_be_a_string()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => 12345,
        ]));

        $request->assertJsonValidationErrors('password');
    }

    /** @test */
    public function a_password_must_be_atleast_8_characters()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => '1234567',
        ]));

        $request->assertJsonValidationErrors('password');
    }

    /** @test */
    public function a_password_must_be_confirmed()
    {
        $request = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => 'password',
            'password_confirmation' => 'not-the-same-password',
        ]));

        $request->assertJsonValidationErrors('password');
    }
}
