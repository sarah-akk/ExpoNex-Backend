<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Controllers\OTPController;
use App\Http\Controllers\PictureController;
use App\Http\Resources\UserDetailsResource;
use App\Http\Resources\UserResource;
use App\Models\User;

use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //Create end user
    public function Create()
    {
        $validator = Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:63', 'email', 'unique:users'],
            'username' => ['required', 'string', 'max:31', 'alpha_dash:ascii', 'unique:users'],
            'password' => ['required', 'string', 'max:255', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $atters = [
            'name' => request()->name,
            'email' => strtolower(request()->email),
            'username' => strtolower(request()->username),
            'password' => request()->password,
            'channel_id' => bin2hex(random_bytes(7)) . random_int(0, 9),
        ];

        if (!OTPController::SendVerificationCode(request()->email)) {
            return response([
                'status' => 'failed',
                'error' => 'Something went wrong',
            ], 500);
        }

        $user = User::create($atters);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        return response([
            'status' => 'success',
            'message' => 'Verification code has been sent.',
        ], 200);
    }
    public function Update()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'password' => ['required', 'string', 'max:255', 'min:8'],
            'new_password' => ['nullable', 'string', 'max:255', 'min:8'],
            'name' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['sometimes', 'mimes:jpg,png,svg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!Hash::check(request()->password, $user->password)) {
            return response([
                'status' => 'failed',
                'error' => 'The password do not match.'
            ], 400);
        }


        if (request()->profile_picture) {
            $image = PictureController::ResizeAndDecode(request()->file('profile_picture'));
            Storage::makeDirectory('public/users');
            $path = 'storage/users/' . $user->username . '.webp';
            \chmod(public_path(
                'storage/users/' . $user->username
            ), 0755);
            $image->save(public_path($path));
            PictureController::Create($user, $path, 0);
        }

        $user->name = request()->name ?? $user->name;
        $user->password = request()->new_password ? Hash::make(request()->new_password) : $user->password;
        $user->touch();
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'User has been updated successfully.',
        ], 200);
    }
    public function ChangePhoneNumber()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'password' => ['required', 'string', 'max:255', 'min:8'],
            'new_phone_number' => ['required', 'string', 'numeric', 'digits:10', 'unique:users,phone_number'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!Hash::check(request()->password, $user->password)) {
            return response([
                'status' => 'failed',
                'error' => 'The password do not match.'
            ], 400);
        }

        if (!OTPController::SendSMSVerificationCode(request()->new_phone_number))
            return response([
                'status' => 'failed',
                'error' => 'Something went wrong',
            ], 500);

        return response([
            'status' => 'success',
            'message' => 'Verification code has been sent.',
        ], 200);
    }
    public function PhoneVerification()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'new_phone_number' => ['required', 'string', 'numeric', 'digits:10'],
            'pin_code' => ['required', 'string', 'numeric', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!OTPController::CheckOTP(request()->new_phone_number, 'phone-verification', request()->pin_code))
            return response([
                'status' => 'failed',
                'errors' => 'The credentials do not match.'
            ], 400);

        $user->phone_number = request()->new_phone_number;
        $user->touch();
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'User\'s phone has been updated successfully.',
        ], 200);
    }


    //Admin
    public function GetUsers()
    {
        $data = UserResource::collection(
            User::
                filter()->
                paginate(40)->
                withQueryString()
        )->response()->getData();
        return response([
            'status' => 'success',
            'message' => 'Users have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function GetUser()
    {
        $user = User::find(request()->user_id);
        if (!$user)
            return response([
                'status' => 'failed',
                'error' => 'User not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'User has been feteched successfully.',
            'data' => UserDetailsResource::make($user)
        ], 200);
    }

    public function ChangeState()
    {
        $validator = Validator::make(request()->all(), [
            'user_id' => ['required', 'string', 'numeric', 'exists:users,id'],
            'pending' => ['required', 'string', 'boolean',],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::find(request()->user_id);

        $user->is_pending = request()->pending;
        $user->touch();
        $user->save();
        return response([
            'status' => 'success',
            'message' => 'User\'s state has been updated successfully.',
        ], 200);
    }
    public function Delete()
    {
        $user = User::find(request()->user_id);
        if (!$user)
            return response([
                'status' => 'failed',
                'error' => 'User not found.',
            ], 404);
        $user->delete();
        return response([
            'status' => 'success',
            'message' => 'User has been deleted successfully.',
        ], 200);
    }
}