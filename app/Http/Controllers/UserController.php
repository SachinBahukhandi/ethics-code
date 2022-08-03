<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Utils\ResponseWrapper;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    //
    const TOKEN_NAME = "ethics";
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required',
            'username' => 'required|unique:users',
            'role' => [
                'required',
                Rule::in(Role::all()->pluck('name')->toArray()),
            ],
        ]);
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $roleUser = UserRole::create([
            'user_id' => $user->id,
            'role_id' => Role::where('name', $input['role'])->first()->id
        ]);
        $user->token = $user->createToken(self::TOKEN_NAME)->accessToken;
        return response()->json($user);
    }
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'username' => 'required'
        ]);
        $user = User::where('username', $request->username)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken(self::TOKEN_NAME)->accessToken;

            $user->token = $token;
            return response()->json($user);
        }
        return ResponseWrapper::errorResponse('Invalid Credentials', 400);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT ?
            ResponseWrapper::response(__($status)) :
            ResponseWrapper::errorResponse(__($status), 400);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        return $status===Password::PASSWORD_RESET?   ResponseWrapper::response(__($status)) :
        ResponseWrapper::errorResponse(__($status), 400);

        // return $status === Password::PASSWORD_RESET
        //     ? redirect()->route('login')->with('status', __($status))
        //     : back()->withErrors(['email' => [__($status)]]);
    }
}
