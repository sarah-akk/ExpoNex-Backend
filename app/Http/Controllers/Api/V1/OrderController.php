<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function GetMyOrder()
    {
        $user = request()->user();

        $data = OrderDetailsResource::collection(
            $user->
                orders()->
                paginate(40)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Orders have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function Create()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['required', 'numeric', 'exists:products,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'numeric', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $amount = 0;

        foreach (request()->products as $key => $value) {
            $pro = Product::find($value);
            if ($pro->quantity < request()->quantity[$key])
                return response([
                    'status' => 'failed',
                    'errors' => 'The quantity for product\'s id: ' . $value . ' is invalide'
                ], 400);
            $amount += request()->quantity[$key] * $pro->price;
        }

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
            'order_number' => '#' . bin2hex(random_bytes(4)) . random_int(0, 9) . random_int(0, 9),
            'amount' => $amount
        ]);

        Transaction::create([
            'wallet_id' => $user->wallet->id,
            'order_id' => $order->id
        ]);

        foreach (request()->products as $key => $value) {
            Cart::create([
                'product_id' => $value,
                'order_id' => $order->id,
                'quantity' => request()->quantity[$key]
            ]);
        }

        if ($user->phone_number) {
            NotificationController::SendSMS($user->phone_number, "You have a new order, number: $order->order_number, status: processing.");
        }

        return response([
            'status' => 'success',
            'message' => 'Order has been created successfully.'
        ], 200);
    }

    public function Cancel()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'order_id' => ['required', 'numeric', 'exists:orders,id'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $order = Order::find(request()->order_id);

        if ($order->status !== 'processing')
            return response([
                'status' => 'failed',
                'errors' => 'Can not cancel an order not in processing state.'
            ], 400);

        $user->wallet->balance += $order->amount;
        $user->wallet->touch();
        $user->wallet->save();

        $user->wallet->transactions()->where('order_id', $order->id)->delete();

        $order->status = 'cancelled';
        $order->touch();
        $order->save();

        if ($user->phone_number) {
            NotificationController::SendSMS(
                $user->phone_number,
                "Your order number: $order->order_number has been cancelled."
            );
        }

        return response([
            'status' => 'success',
            'message' => 'Order has been cancelled successfully.'
        ], 200);
    }

    //TODO needs alot of checking
    public function ChangeState()
    {
        $validator = Validator::make(request()->all(), [
            'order_id' => ['required', 'numeric', 'exists:orders,id'],
            'state' => ['required', 'string', 'in:processing,delivered,complated,cancelled']
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $order = Order::find(request()->order_id);

        $order->status = request()->state;
        $order->touch();
        $order->save();

        if ($order->user->phone_number) {
            NotificationController::SendSMS(
                $order->user->phone_number,
                "Your order's state has been changed to *$order->status*"
            );
        }

        return response([
            'status' => 'success',
            'message' => 'Order has been changed successfully.'
        ], 200);
    }
    public function Get()
    {
        $data = OrderResource::collection(
            Order::
                paginate(40)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Companies have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function GetOrder()
    {
        $order = Order::find(request()->order_id);

        if (!$order)
            return response([
                'status' => 'failed',
                'error' => 'Order not found.'
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Orders have been fetched successfully.',
            'data' => OrderDetailsResource::make($order)
        ], 200);
    }
}
