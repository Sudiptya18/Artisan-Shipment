<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $remember = (bool) ($credentials['remember'] ?? false);
        $loginField = $credentials['email'] ?? $credentials['username'] ?? null;
        $password = $credentials['password'];

        if (!$loginField) {
            throw ValidationException::withMessages([
                'email' => ['Please provide either email or username.'],
            ]);
        }

        // Determine if login field is email or username
        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'username';

        $user = User::where($field, $loginField)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;

        $user = DB::transaction(function () use ($data) {
            return User::create($data);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(201);
    }
}
