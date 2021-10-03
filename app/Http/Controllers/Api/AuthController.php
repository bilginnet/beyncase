<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Validation\Validator as ValidatorContracts;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validation = $this->validator($request->all());

        if ($validation->fails())
        {
            return response()->json($validation->getMessageBag());
        }

        $user = User::query()->where(['email' => $request->email])->first();

        if (! $user || ! Hash::check($request->password, $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'api_token' => $user->createToken($request->device_name)->plainTextToken
        ]);
    }

    /**
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validator(array $data): ValidatorContracts
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string'],
        ]);
    }
}
