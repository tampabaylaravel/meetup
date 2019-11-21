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
    public function a_guest_can_register_for_an_account_and_get_a_valid_token()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => 'Test User Name',
            'email' => 'test@example.org',
        ]))->assertStatus(200);

        $user = User::first();
        $this->assertEquals('Test User Name', $user->name);
        $this->assertEquals('test@example.org', $user->email);

        $token = $response->getData()->token;
        $this->assertNotNull($token);

        $decodedToken = $this->decodeJWT($token);
        $this->assertEquals($user->getKey(), $decodedToken->sub);
    }

    /** @test */
    public function a_name_is_required()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => null,
        ]));

        $response->assertJsonValidationErrors('name');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_name_must_be_a_string()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => 12345,
        ]));

        $response->assertJsonValidationErrors('name');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_name_cannot_be_longer_than_255_characters()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'name' => str_repeat('a', 256),
        ]));

        $response->assertJsonValidationErrors('name');
        $response->assertStatus(422);
    }

    /** @test */
    public function an_email_is_required()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => null,
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function an_email_must_be_a_string()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => 12345,
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function an_email_cannot_be_longer_than_255_characters()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => str_repeat('1', 255) . '@example.org',
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function an_email_must_be_a_valid_email()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => 'not-a-valid-email',
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function an_email_must_be_unique()
    {
        $user = factory(User::class)->create();

        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'email' => $user->email,
        ]));

        $response->assertJsonValidationErrors('email');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_password_is_required()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => null,
        ]));

        $response->assertJsonValidationErrors('password');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_password_must_be_a_string()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => 12345,
        ]));

        $response->assertJsonValidationErrors('password');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_password_must_be_atleast_8_characters()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => '1234567',
        ]));

        $response->assertJsonValidationErrors('password');
        $response->assertStatus(422);
    }

    /** @test */
    public function a_password_must_be_confirmed()
    {
        $response = $this->postJson(route('auth.register'), $this->validParamaters([
            'password' => 'password',
            'password_confirmation' => 'not-the-same-password',
        ]));

        $response->assertJsonValidationErrors('password');
        $response->assertStatus(422);
    }
}
