<?php

namespace App\Providers;

use Exception;
use App\Models\User;
use App\Models\Meeting;
use App\Policies\MeetingPolicy;
use App\Traits\InteractsWithJWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    use InteractsWithJWT;

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Meeting::class => MeetingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->registerAuthGuard();
    }

    /**
     * @return void
     */
    private function registerAuthGuard(): void
    {
        Auth::viaRequest('jwt', function ($request) {
            $token = $request->bearerToken();
            abort_unless($token, 401, 'Token not provided.');

            try {
                $credentials = $this->decodeJWT($token);
            } catch (ExpiredException $e) {
                abort(419, 'Provided token is expired.');
            } catch (Exception $e) {
                abort(400, 'An error while decoding token.');
            }

            $user = User::find($credentials->sub);
            abort_unless($user, 400, 'Email or password is wrong.');

            return $user;
        });
    }
}
