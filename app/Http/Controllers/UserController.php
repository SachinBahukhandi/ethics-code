<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    const TOKEN_NAME="ethics";
    public function register(Request $request){
       $request->validate([
            'name'=>'required',
            'email'=> 'required|unique:users',
            'password'=>'required',
            'password_confirmation'=>'required',
            'username'=>'required|unique:users'
       ]);
       $user= User::create($request->all());
       $user->token = $user->createToken(self::TOKEN_NAME)->accessToken;
       return response()->json($user);
    }
}
