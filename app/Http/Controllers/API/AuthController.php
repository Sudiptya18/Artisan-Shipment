<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
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

        // Log login activity
        activity()
            ->causedBy($user)
            ->withProperties(['page' => 'login'])
            ->log('User logged in');

        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            // Log logout activity before logging out
            activity()
                ->causedBy($user)
                ->withProperties(['page' => 'logout'])
                ->log('User logged out');
            
            $user->tokens()->delete();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->load('role');
        }
        $resource = new UserResource($user);
        return $resource;
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

        // Log registration activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['page' => 'user-registration'])
            ->log("New user registered: {$user->name} ({$user->username})");

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'user_id' => ['required', 'exists:users,id'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        // Verify that the user belongs to the selected role
        $user = User::where('id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['The selected user does not belong to the selected role.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['page' => 'forget-password'])
            ->log("Password reset for user: {$user->name} ({$user->username})");

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $user = $request->user();

        if (!$user) {
            throw ValidationException::withMessages([
                'password' => ['User not authenticated.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['page' => 'change-password'])
            ->log("Password changed for user: {$user->name} ({$user->username})");

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
