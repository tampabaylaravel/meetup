<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Firebase\JWT\JWT;
use App\Traits\InteractsWithJWT;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase, InteractsWithJWT;

    /**
     * @var string
     */
    protected $route;

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

        $this->user = factory(User::class)->create();

        $this->route = route('user.index');
    }

    /**
     * @param  int $statusCode
     * @param  string|null $token
     * @return void
     */
    public function tokenTest($statusCode, $token)
    {
        $this->getJson($this->route, [
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus($statusCode);
    }

    /** @test */
    public function guests_cannot_access_the_user_route()
    {
        $this->getJson($this->route)->assertStatus(401);
    }

    /** @test */
    public function users_can_access_the_user_route()
    {
        $this->actingAs($this->user, 'api')
            ->getJson($this->route)
            ->assertStatus(200);
    }

    /** @test */
    public function users_can_authenticate_with_a_bearer_token()
    {
        $this->tokenTest(200, $this->createJWT($this->user));
    }

    /** @test */
    public function a_401_is_returned_if_there_is_no_bearer_token()
    {
        $this->tokenTest(401, null);
    }

    /** @test */
    public function a_419_is_returned_if_the_token_is_expired()
    {
        $expiredToken = $this->createJWT($this->user, [
            'iat' => time(),
            'exp' => time() - 1
        ]);

        $this->tokenTest(419, $expiredToken);
    }

    /** @test */
    public function a_400_is_returned_if_the_token_was_generated_for_a_user_that_doesnt_exist()
    {
        $invalidToken = $this->createJWT($this->user, [
            'sub' => User::max('id') + 1, // a user id that doesn't exist
        ]);

        $this->tokenTest(400, $invalidToken);
    }
}
