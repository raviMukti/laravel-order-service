<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Facades\OrderEvent;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $params = $request->all();

        $data = [
            "email" => $params["email"],
            "food_name" => $params["food_name"],
            "quantity"=> $params["quantity"],
            "status"=> "PENDING",
        ];

        $order = Order::create($data);

        OrderEvent::publish($order->toJson(), "OrderExchange", "direct", "order_new", "order.new");

        return response()->json($order->order_id, 201);
    }

    public function getAllOrders()
    {
        $orders = Order::orderBy("created_at","desc")->get();
        return response()->json($orders,200);
    }

    public function confirmOrder(int $id)
    {
        $order = Order::where("order_id", $id)->first();
        if ($order)
        {
            $order->status = "CONFIRMED";
            $order->save();
            
            OrderEvent::publish($order->toJson(), "StatusExchange", "direct", "status_confirm", "status.confirm");
            OrderEvent::publish($order->toJson(), "NotifyExchange", "fanout", "notify_confirm");

            return response()->json($order->order_id, 200);
        }
        else
        {
            return response()->json(["message" => "Order not found"], 404);
        }
    }

}
