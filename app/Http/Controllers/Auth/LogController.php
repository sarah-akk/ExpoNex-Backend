<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LogInRequest;


class LogController extends Controller
{
    public function login(LogInRequest $request)
    {
        //TODO: the otp code must be when login in not when register
        //what if log in with not verefied email

        $user = User::query() ;
        if(request()->has('email'))
        {
            $user->where('email' , $request->email) ;
        }else{
            $user->where('username' , $request->username) ;
        }
        $user = $user->first();

       if(!$user || !Hash::check($request->password , $user->password)){
            return response()->json([
                'error' => 'the provided credentials are incorrect :(' ,
            ]);
        }

        // if(!!! $user->email_verified_at){
        //     return response()->json([
        //         'error'=> 'you need to verify your email' ,
        //     ]);
        // }

        $device = substr($request->userAgent() ?? '' , 0 , 255) ;

        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken ,
            'username' =>$user->username,
            'email' =>$user->email
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
