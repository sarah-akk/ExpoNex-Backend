<?php

namespace App\Http\Controllers;

use App\Mail\EmailNotification;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    static public function SendSMS($phone, $message)
    {
        try {
            // Http::acceptJson()->withHeader('Authorization', env("SMS_API_TOKEN"))->post(, [
            //     'to' => $phone,
            //     'message' => $message
            // ]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
    static public function SendLetter($data, $email)
    {
        try {
            // Mail::to($email)->send(new EmailNotification($data));
        } catch (\Exception $e) {
            return false;
        }

        Notification::create();

        return true;
    }
}
