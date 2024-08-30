<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PictureController;
use App\Http\Resources\TicketResource;
use App\Models\Exhibition;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function Buy()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'exhibition_id' => ['required', 'numeric', 'exists:exhibitions,id'],
            'type' => ['required', 'string', 'in:in_place,virtually,prime'],
            'quantity' => ['required', 'numeric', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $expo = Exhibition::
            where('status', 'active')->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found or was complated.',
            ], 404);

        $amount = 0;
        if (request()->type === 'in_place')
            $amount += $expo->ticketManager->in_place_price * request()->quantity;
        if (request()->type === 'virtually')
            $amount += $expo->ticketManager->in_virtual_price * request()->quantity;
        if (request()->type === 'prime')
            $amount += $expo->ticketManager->prime_price * request()->quantity;

        if ($user->wallet->balance < $amount)
            return response([
                'status' => 'failed',
                'errors' => 'There is no enough balance in your wallet. Amount required is: ' . $amount
            ], 400);

        $user->wallet->balance -= $amount;
        $user->wallet->touch();
        $user->wallet->save();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => '#' . bin2hex(random_bytes(9)) . random_int(0, 9) . random_int(0, 9),
            'amount' => $amount
        ]);

        TicketItems::create([
            'order_id' => $order->id,
            'ticket_id' => $expo->ticketManager->id,
            'type' => request()->type,
            'quantity' => request()->quantity,
        ]);

        if ($user->phone_number) {
            NotificationController::SendSMS(
                $user->phone_number,
                "You have been ordered a ticket to visit exhibition: " . $expo->name
            );
        }

        return response([
            'status' => 'success',
            'message' => 'Order has been created successfully.'
        ], 200);
    }
    public function GetTicket()
    {
        $expo = Exhibition::
            where(function ($query) {
                $query->
                    where('status', 'complated')->
                    orWhere('status', 'active');
            })->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => TicketResource::make($expo->ticketManager),
        ], 200);
    }
    public static function Create($atters)
    {
        $ticket = Ticket::create([
            'exhibition_id' => $atters['exhibition_id'],
            'in_place' => $atters['ticket_in_place'],
            'available_in_place' => $atters['ticket_in_place'],
            'in_place_price' => $atters['ticket_in_place_price'],
            'in_virtual_price' => $atters['ticket_in_virtual_price'],
            'prime' => $atters['ticket_prime'],
            'available_prime' => $atters['ticket_prime'],
            'prime_price' => $atters['ticket_prime_price'],
            'barcode' => $atters['ticket_barcode'],
            'title' => $atters['ticket_title'],
            'description' => $atters['ticket_description'],
            'side_style' => $atters['ticket_side_type'] === 'color' ? $atters['ticket_side_style'] : null,
            'main_style' => $atters['ticket_main_type'] === 'color' ? $atters['ticket_main_style'] : null,
        ]);

        if ($atters['ticket_side_type'] === 'picture')
            TicketController::CreatePicture($atters['expo_name'], $atters['ticket_side_style'], $ticket, 'ticketSideStyle.webp');

        if ($atters['ticket_main_type'] === 'picture')
            TicketController::CreatePicture($atters['expo_name'], $atters['ticket_main_style'], $ticket, 'ticketMainStyle.webp');
    }

    public static function CreatePicture($expoName, $file, $model, $name)
    {
        $index = $name === 'ticketSideStyle.webp' ? 5 : 6;
        $path = 'storage/exhibition/' . $expoName . '/' . $name;
        \chmod(public_path(
            'storage/exhibition/' . $expoName
        ), 0755);
        $image = PictureController::ResizeAndDecode($file);
        $image->save(public_path($path));
        PictureController::Create($model, $path, $index);
    }
}