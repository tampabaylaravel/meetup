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
     * @var string
     */
    protected $route;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->route = route('auth.register');
    }

    /**
     * @param  array  $override
     * @return void
     */
    private function assertValidationError($override = [])
    {
        $response = $this->postJson($this->route, $this->validParamaters($override));

        $response->assertStatus(422);

        if (array_key_exists('password_confirmation', $override)) {
            unset($override['password_confirmation']);
        }

        $response->assertJsonValidationErrors(array_keys($override));
    }

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
        $response = $this->postJson($this->route, $this->validParamaters([
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
        $this->assertValidationError(['name' => null]);
    }

    /** @test */
    public function a_name_must_be_a_string()
    {
        $this->assertValidationError(['name' => 12345]);
    }

    /** @test */
    public function a_name_cannot_be_longer_than_255_characters()
    {
        $this->assertValidationError(['name' => str_repeat('a', 256)]);
    }

    /** @test */
    public function an_email_is_required()
    {
        $this->assertValidationError(['email' => null]);
    }

    /** @test */
    public function an_email_must_be_a_string()
    {
        $this->assertValidationError(['email' => 12345]);
    }

    /** @test */
    public function an_email_cannot_be_longer_than_255_characters()
    {
        $this->assertValidationError(['email' => str_repeat('1', 255) . '@example.org']);
    }

    /** @test */
    public function an_email_must_be_a_valid_email()
    {
        $this->assertValidationError(['email' => 'not-a-valid-email']);
    }

    /** @test */
    public function an_email_must_be_unique()
    {
        $user = factory(User::class)->create();

        $this->assertValidationError(['email' => $user->email]);
    }

    /** @test */
    public function a_password_is_required()
    {
        $this->assertValidationError(['password' => null]);
    }

    /** @test */
    public function a_password_must_be_a_string()
    {
        $this->assertValidationError(['password' => 12345]);
    }

    /** @test */
    public function a_password_must_be_atleast_8_characters()
    {
        $this->assertValidationError(['password' => '1234567']);
    }

    /** @test */
    public function a_password_must_be_confirmed()
    {
        $this->assertValidationError([
            'password' => 'password',
            'password_confirmation' => 'not-the-same-password',
        ]);
    }
}
