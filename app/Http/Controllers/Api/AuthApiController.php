<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Login surveyor / petugas, kembalikan token Sanctum.
     *
     * POST /api/v1/auth/login
     * POST /api/v1/mobile/auth/login
     * Body: { email, password, device_name? }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::with('roles')
            ->where('email', $request->email)
            ->first();

        // Cek kredensial
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Cek user aktif
        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Hanya role surveyor atau admin yang boleh login via API
        if (! $user->hasAnyRole(['surveyor', 'admin'])) {
            return response()->json([
                'message' => 'Akun Anda tidak memiliki akses ke aplikasi ini.',
            ], 403);
        }

        $deviceName = $request->input('device_name', 'rtlh-mobile');

        // Hapus token lama dengan nama yang sama (biar tidak numpuk)
        $user->tokens()->where('name', $deviceName)->delete();

        $abilities = $user->hasRole('admin') ? ['*'] : ['mobile:surveyor', 'surveyor'];
        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        $primaryRole = $user->roles->first()?->slug ?? 'surveyor';
        $userData = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $primaryRole,
            'roles' => $user->roles->pluck('slug'),
        ];

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => $userData,
            'data'    => $userData,
        ]);
    }

    /**
     * Logout — cabut token yang sedang dipakai.
     *
     * POST /api/v1/auth/logout
     * POST /api/v1/mobile/auth/logout
     * Header: Authorization: Bearer {token}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Info user yang sedang login.
     *
     * GET /api/v1/auth/me
     * GET /api/v1/mobile/auth/me
     * Header: Authorization: Bearer {token}
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');
        $primaryRole = $user->roles->first()?->slug ?? 'surveyor';

        $userData = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $primaryRole,
            'roles' => $user->roles->pluck('slug'),
        ];

        return response()->json([
            'data' => $userData,
            'user' => $userData,
        ]);
    }
}
