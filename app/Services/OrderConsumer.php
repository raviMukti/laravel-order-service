<?php

namespace App\Services;
use App\Models\Order;
use App\Services\Facades\OrderEvent;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class OrderConsumer
{
    public function consumePickup()
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASSWORD'), env('MQ_VHOST'));

        $channel = $connection->channel();

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            $data = json_decode(json_decode($msg->body, true));
            $order = Order::where("order_id", $data->order_id)->first();
            $order->status = "PICKED UP";
            $order->save();

            echo ' [x] Done', "\n";
        };

        $channel->queue_declare('status_pickup', false, true, false, false);
        $channel->basic_consume('status_pickup', '<order-service>', false, true, false, false, $callback);
        echo 'Waiting for new message on status_pickup', " \n";

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}