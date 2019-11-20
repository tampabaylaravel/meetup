<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Response;
use App\Traits\InteractsWithJWT;
use Illuminate\Routing\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class LoginController extends Controller
{
    use InteractsWithJWT, ThrottlesLogins;

    /**
     * The maximum number of attempts to allow.
     *
     * @return int
     */
    protected $maxAttempts = 5;

    /**
     * The number of minutes to throttle for.
     *
     * @return int
     */
    protected $decayMinutes = 1;

    /**
     * Store a newly created resource in storage.
     *
     * @param LoginRequest $request
     * @return Response
     * @throws ValidationException
     */
    public function store(LoginRequest $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $user = User::whereEmail($request->input('email'))->first();

        if (! $user || ! $this->verifyPasswordFor($user, $request->input('password'))) {
            $this->incrementLoginAttempts($request);

            abort(400, 'Email or password is wrong.');
        }

        $this->clearLoginAttempts($request);

        return response()->json(['token' => $this->createJWT($user)], 200);
    }

    /**
     * @param  User   $user
     * @param  string $password
     * @return boolean
     */
    private function verifyPasswordFor(User $user, $password)
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }
}
