<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\Coupon;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller
{
    public function Get()
    {
        $user = request()->user();

        return response([
            'status' => 'success',
            'message' => 'Balance has been fetched successfully.',
            'data' => [
                'balance' => $user->wallet->balance
            ]
        ], 200);
    }

    public function AddBalance()
    {
        $user = request()->user();

        if (!request()->coupon)
            return response([
                'status' => 'failed',
                'error' => 'please provide a valid coupon'
            ], 400);

        $coupon = explode('#~#', request()->coupon);
        $id = $coupon[0];
        $serial_number = $coupon[1];

        $couponModel = Coupon::find($id);

        if (!$couponModel || !Hash::check($serial_number, $couponModel->serial_number))
            return response([
                'status' => 'failed',
                'error' => 'please provide a valid coupon'
            ], 400);

        $user->wallet->balance += $couponModel->amount;
        $user->wallet->touch();
        $user->wallet->save();

        $couponModel->delete();

        if ($user->phone_number) {
            NotificationController::SendSMS(
                $user->phone_number,
                "A new coupon has been applied recently, you balance now is: " . $user->wallet->balance
            );
        }

        return response([
            'status' => 'success',
            'error' => 'Balance has been added successfully to your wallet.'
        ], 200);
    }
}
