<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Response;

class ApiAuthController extends Controller
{
    public function token(Request $request) : JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::whereEmail($request->get('email'))->first();

        try {
            if ( ! $user || ! Hash::check($request->get('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $user->createToken("{$user->name}'s {$request->get('device_name')}")->plainTextToken;

            return Response::json(compact('token'));

        } catch (ValidationException $exception) {
            return Response::json($exception->errors(), 400);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
    }

    public function user(Request $request) : JsonResponse
    {
        $user = $request->user();

        return Response::json(compact('user'));
    }
}
