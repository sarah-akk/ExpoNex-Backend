<?php

namespace App\Http\Controllers;

use App\Mail\RecoveryCode;
use App\Mail\VerificationCode;
use App\Models\OTP;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class OTPController extends Controller
{
    public static function SendVerificationCode($email)
    {
        $code = OTPController::GenerateCode();

        try {
            // Mail::to($email)->send(new VerificationCode($code));
        } catch (\Exception $e) {
            return false;
        }

        OTP::create([
            'email' => $email,
            'pin_code' => $code,
            'type' => 'user-verification',
            'expire_at' => Carbon::now()->addMinutes(10)->format('Y-m-d h:i:s')
        ]);

        return true;
    }

    public static function SendRecoveryCode($email)
    {
        $code = OTPController::GenerateCode();

        try {
            // Mail::to($email)->send(new RecoveryCode($code));
        } catch (\Exception $e) {
            return false;
        }

        OTP::create([
            'email' => $email,
            'pin_code' => $code,
            'type' => 'user-recovery',
            'expire_at' => Carbon::now()->addMinutes(10)->format('Y-m-d h:i:s')
        ]);

        return true;
    }
    public static function SendSMSVerificationCode($phone)
    {
        $code = OTPController::GenerateCode();

        try {
            // Http::acceptJson()->withHeader('Authorization', env("SMS_API_TOKEN"))->post(, [
            //     'to' => $phone,
            //     'message' => OTPController::GetMessage($code, 0)
            // ]);
        } catch (\Exception $e) {
            return false;
        }

        OTP::create([
            'email' => $phone,
            'pin_code' => $code,
            'type' => 'phone-verification',
            'expire_at' => Carbon::now()->addMinutes(10)->format('Y-m-d h:i:s')
        ]);

        return true;
    }
    public static function CheckOTP($email, $type, $pin_code)
    {
        $otp = OTP::
            where('email', $email)->
            where('type', $type)->
            where('expire_at', '>=', Carbon::now()->format('Y-m-d h:i:s'))->
            latest('id')->
            first();

        
        $flag = $otp && Hash::check($pin_code, $otp->pin_code);

        if ($flag) {
            $otp->delete();
        }

        return $flag;
    }
    public static function CreateAccessOTP($email)
    {
        $code = bin2hex(random_bytes(64));
        OTP::create([
            'email' => $email,
            'pin_code' => $code,
            'type' => 'access-otp',
            'expire_at' => Carbon::now()->addMinutes(10)->format('Y-m-d h:i:s')
        ]);
        return $code;
    }
    private static function GenerateCode()
    {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    private static function GetMessage($otp, $id)
    {
        $messages = [
            'Welcome to ExpoNex, your verification code is ' . $otp . ', if you did not request any code please ignore this message.',
        ];
        return $messages[$id];
    }
}
