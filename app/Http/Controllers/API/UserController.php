<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormater;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Rules\Password;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // mendapatkan user by email
            $user = User::where('email', $request->email)->first();

            // membuat access token untuk user yang login
            $tokenresult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormater::success([
                'access_token' => $tokenresult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Berhasil Register');
        } catch (Exception $error) {

            return ResponseFormater::error([
                'message' => 'Something When Wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credential = request(['email', 'password']);

            // mengecek authentication email dan password
            if (!Auth::attempt($credential)) {
                return ResponseFormater::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            // mengambil user berdasarkan email
            $user = User::where('email', $request->email)->first();

            // cek password yang tidak di hash
            if (!Hash::check($request->password, $user->password, [])) {
                throw new Exception('Invalid Credential');
            }

            // user login token
            $tokenresult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormater::success([
                'access_token' => $tokenresult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormater::error([
                'message' => 'Something Wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormater::success($request->user(), 'Data Profile Berhasil Didaptkan');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['string', 'max:255'],
            'username' => ['string', 'max:255', 'unique:users'],
            'email' => ['string', 'email', 'max:255', 'unique:users']
        ]);

        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormater::success($user, 'Profile Berhasil di update');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormater::success($token, 'Token Revoked');
    }
}
