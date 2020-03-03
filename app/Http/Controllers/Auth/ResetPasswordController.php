<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
     */

    use ResetsPasswords;

    /**
     * Reset the given user's password.
     *
     * @param  CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);

        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param Request $request
     * @param  string  $response
     * @return RedirectResponse|JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        $statusCodes = [
            PasswordBroker::PASSWORD_RESET => 200,
            PasswordBroker::INVALID_USER => 400,
            PasswordBroker::INVALID_TOKEN => 401,
        ];

        return response()->json(['message' => trans($response)], $statusCodes[$response]);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param Request $request
     * @param  string  $response
     * @return RedirectResponse|JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return $this->sendResetResponse($request, $response);
    }

    public function __invoke(Request $request)
    {
        return $this->reset($request);
    }
}
