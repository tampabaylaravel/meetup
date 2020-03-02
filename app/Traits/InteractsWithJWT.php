<?php

namespace App\Traits;

use App\Models\User;
use Firebase\JWT\JWT;

trait InteractsWithJWT
{
    /**
     * @param  User   $user
     * @param  array  $override
     * @return string
     */
    private function createJWT(User $user, $override = []): string
    {
        return JWT::encode(array_merge([
            'iss' => config('auth.jwt.issuer'),
            'sub' => $user->getKey(),
            'iat' => time(),
            'exp' => time() + (60 * config('auth.jwt.expires_in_minutes')),
        ], $override), config('auth.jwt.secret'));
    }

    /**
     * @param  string $token
     * @return object
     */
    private function decodeJWT($token): object
    {
        return JWT::decode($token, config('auth.jwt.secret'), ['HS256']);
    }
}
