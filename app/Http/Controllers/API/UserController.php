<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Rules\Password as RulesPassword;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            //validasi request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            //mencari user by email
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Unautorized', 401);
            }
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid password');
            }
            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            //mengembalikan response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Login success');
        } catch (Exception $e) {
            return ResponseFormatter::error('Autentication Failed');
        }
    }

    public function register(Request $request)
    {
        try {
            //validasi request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new RulesPassword],
            ]);

            //create user after validasi
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //mengembalikan response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Register success');
        } catch (Exception $error) {
            //response jika error
            return ResponseFormatter::error('Autentication Failed');
        }
    }

    public function logout(Request $request)
    {
        //Revoke token
        $token = $request->user()->currentAccessToken()->delete();

        //return response
        return ResponseFormatter::success($token, 'Logot Success');
    }

    public function fetch(Request $request)
    {
        //get user
        $user = $request->user();

        //return response formatter

        return ResponseFormatter::success($user, 'Fetch success');
    }
}
