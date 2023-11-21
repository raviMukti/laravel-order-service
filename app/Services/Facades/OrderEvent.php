<?php

namespace App\Services\Facades;
use Illuminate\Support\Facades\Facade;

class OrderEvent extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'App\Services\OrderEvent';
    }
}