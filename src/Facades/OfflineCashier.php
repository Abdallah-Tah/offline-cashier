<?php

namespace AMohamed\OfflineCashier\Facades;

use Illuminate\Support\Facades\Facade;

class OfflineCashier extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'offline-cashier';
    }
} 