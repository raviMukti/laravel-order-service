<?php

namespace App\Services;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OrderEvent
{
    public function publish($order, $exchangeName, $exchangeType = "", $queue, $routingKey = "")
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASSWORD'));
        $channel = $connection->channel();
        $channel->exchange_declare($exchangeName, $exchangeType, true, false, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, $exchangeName, $routingKey);
        $payload = new AMQPMessage(json_encode($order));
        $channel->basic_publish($payload, $exchangeName, $routingKey);
        echo ' [x] Sent '.json_encode($order).' to ' . $exchangeName . '.\n';
        $channel->close();
        $connection->close();
    }
    
}