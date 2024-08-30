<?php

namespace App\Http\Controllers;

use App\Models\OTP;
use App\Models\User;
use Laravel\Passport\Token;

use Illuminate\Validation\Rule;
use Laravel\Passport\RefreshToken;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    //Login end user
    public function Login()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['nullable', 'string', 'max:63', 'email'],
            'username' => [Rule::requiredIf(!request()->email), 'string', 'max:31', 'alpha_dash:ascii'],
            'password' => ['required', 'string', 'max:255', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

         
        $user = User::
            when(request()->email, function ($query) {
                $query->where('email', request()->email);
            })->
            when(!request()->username, function ($query) {
                $query->where('username', request()->username);
            })->
            where('is_verified', 1)->first();


        if (!$user || !Hash::check(request()->password, $user->password)) {
            return response([
                'status' => 'failed',
                'error' => 'The credentials do not match.'
            ], 400);
        }

        if ($user->is_pending)
            return response([
                'status' => 'failed',
                'error' => 'Your account has been changed to pending, contact support please.'
            ], 400);

        if ($user->role_id == 2)
            if (!$user->company->is_approval)
                return response([
                    'status' => 'failed',
                    'error' => 'Your company still under pending, please contact support.'
                ], 400);


        $tokens = self::CreateToken(request()->email ?? request()->username, request()->password);

        if ($tokens->status() != 200)
            return response([
                'status' => 'failed',
                'error' => 'Something went wrong!, try to login later.'
            ], 500);

        $tokens = json_decode($tokens->body());

        $data = json_decode(UserResource::make($user)->toJson());
        $data->expires_in = $tokens->expires_in;
        $data->access_token = $tokens->access_token;
        $data->refresh_token = $tokens->refresh_token;

        return response([
            'status' => 'success',
            'message' => 'User has been logged in successfully.',
            'data' => $data
        ], 200);
    }
    public function Refresh()
    {
        if (!request()->refresh_token) {
            return response([
                'status' => 'failed',
                'error' => 'Please provide refresh token.'
            ], 400);
        }

        $tokens = AuthenticationController::RefreshToken(request()->refresh_token);

        if ($tokens->status() != 200)
            return response([
                'status' => 'failed',
                'error' => 'Token has not created successfully, try again later.'
            ], 500);

        $tokensBody = json_decode($tokens->body());

        return response([
            'status' => 'success',
            'message' => 'Token has been refreshed successfully.',
            'data' => [
                'expires_in' => $tokensBody->expires_in,
                'access_token' => $tokensBody->access_token,
                'refresh_token' => $tokensBody->refresh_token
            ]
        ], 200);
    }
    public function UserVerification()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'max:63', 'email'],
            'pin_code' => ['required', 'numeric', 'digits:6']
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!OTPController::CheckOTP(request()->email, 'user-verification', request()->pin_code)) {
            return response([
                'status' => 'failed',
                'errors' => 'The credentials do not match.'
            ], 400);
        }

        $user = User::where('email', request()->email)->first();
        $user->is_verified = 1;
        $user->touch();
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'User has been verified, Please login.',
        ], 200);
    }
    public function ReSendVerificationCode()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'max:63', 'email'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!User::where('email', request()->email)->where('is_verified', false)->first()) {
            return response([
                'status' => 'failed',
                'errors' => 'User is already verified.'
            ], 400);
        }

        if (count(OTP::where('email', request()->email)->get()) >= 5) {
            return response([
                'status' => 'failed',
                'error' => 'Please try after 24 hours.',
            ], 429);
        }

        if (!OTPController::SendVerificationCode(request()->email)) {
            return response([
                'status' => 'failed',
                'error' => 'Something went wrong',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Verification code has been sent.',
        ], 200);
    }
    public function ForgetPassword()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'max:63', 'email']
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        //TODO make a static call for this line..
        if (count(OTP::where('email', request()->email)->get()) >= 5) {
            return response([
                'status' => 'failed',
                'error' => 'Please try after 24 hours.',
            ], 429);
        }

        if (!OTPController::SendRecoveryCode(request()->email)) {
            return response([
                'status' => 'failed',
                'error' => 'Something went wrong',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Recovery code has been sent.',
        ], 200);
    }
    public function ResendForgetPasswordCode()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'max:63', 'email'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (count(OTP::where('email', request()->email)->get()) >= 5) {
            return response([
                'status' => 'failed',
                'error' => 'Please try after 24 hours.',
            ], 429);
        }

        if (!OTPController::SendRecoveryCode(request()->email)) {
            return response([
                'status' => 'failed',
                'error' => 'Something went wrong',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Verification code has been sent.',
        ], 200);
    }
    public function UserRecovery()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'max:63', 'email'],
            'pin_code' => ['required', 'numeric', 'digits:6']
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!OTPController::CheckOTP(request()->email, 'user-recovery', request()->pin_code)) {
            return response([
                'status' => 'failed',
                'errors' => 'The credentials do not match.'
            ], 400);
        }

        $access_otp = OTPController::CreateAccessOTP(request()->email);

        return response([
            'status' => 'success',
            'message' => 'Access otp has been created successfully.',
            'data' => [
                'access_otp' => $access_otp
            ]
        ], 200);
    }
    public function ChangePassword()
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'max:63', 'email'],
            'access_otp' => ['required', 'string', 'size:128'],
            'password' => ['required', 'string', 'max:255', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!OTPController::CheckOTP(request()->email, 'access-otp', request()->access_otp)) {
            return response([
                'status' => 'failed',
                'errors' => 'The credentials do not match.'
            ], 400);
        }

        $user = User::where('email', request()->email)->first();

        $user->password = Hash::make(request()->password);
        $user->touch();
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'User has been updated successfully.',
        ], 200);
    }
    public function Logout()
    {
        $tokenId = request()->user()->token()->id;

        Token::find($tokenId)->delete();
        RefreshToken::where('access_token_id', $tokenId)->first()->delete();

        return response([
            'status' => 'success',
            'message' => 'User has been logged out successfully.',
        ], 200);
    }
    static private function CreateToken($email, $password)
    {
        $client_id = env('PASSPORT_PASSWORD_GRANT_TYPE_CLIENT_ID');
        $client_secret = env('PASSPORT_PASSWORD_GRANT_TYPE_CLIENT_SECRET');

        $response = Http::post(url('/') . '/api/v1/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $email,
            'password' => $password,
        ]);
        return $response;
    }
    static private function RefreshToken($refresh_token)
    {
        $client_id = env('PASSPORT_PASSWORD_GRANT_TYPE_CLIENT_ID');
        $client_secret = env('PASSPORT_PASSWORD_GRANT_TYPE_CLIENT_SECRET');

        $response = Http::post(url('/') . '/api/v1/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
        ]);
        return $response;
    }
}
