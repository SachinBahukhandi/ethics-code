<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Utils\ResponseWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $roleUser= UserRole::create([
            'user_id'=> $user->id,
            'role_id'=> Role::where('name',$input['role'])->first()->id
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
            $token= $user->createToken(self::TOKEN_NAME)->accessToken;

            $user->token= $token;
            return response()->json($user);
        }
        return ResponseWrapper::errorResponse('Invalid Credentials', 400);



    }
}
