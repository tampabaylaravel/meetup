<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use Illuminate\Http\Response;
use App\Traits\InteractsWithJWT;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use InteractsWithJWT;

    /**
     * Store a newly created resource in storage.
     *
     * @param RegisterRequest $request
     * @return Response
     */
    public function store(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json(['token' => $this->createJWT($user)]);
    }
}
