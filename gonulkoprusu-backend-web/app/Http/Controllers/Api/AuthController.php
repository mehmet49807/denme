<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrivateUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Cross-platform authentication (Web, Android, iOS share the same accounts).
 * Issues a Sanctum token so the same credentials work on every client.
 */
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username'   => ['required', 'string', 'max:50', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:190', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
            'phone'      => ['required', 'string', 'max:30'],
            'gender'     => ['required', Rule::in(['male', 'female'])],
            'city'       => ['required', 'string', 'max:80'],
            'district'   => ['required', 'string', 'max:80'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $token = $user->createToken('gonulkoprusu')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new PrivateUserResource($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => ['required', 'string'], // username or email
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kullanıcı adı veya şifre hatalı.'],
            ]);
        }

        if ($user->status === 'banned') {
            throw ValidationException::withMessages([
                'login' => ['Hesabınız askıya alınmıştır.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('gonulkoprusu')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new PrivateUserResource($user),
        ]);
    }

    public function me(Request $request): PrivateUserResource
    {
        return new PrivateUserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Çıkış yapıldı.']);
    }
}
