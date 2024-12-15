<?php

namespace AMohamed\OfflineCashier\Tests;

use AMohamed\OfflineCashier\Traits\HasSubscriptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasSubscriptions;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \AMohamed\OfflineCashier\Tests\Database\Factories\UserFactory::new();
    }
}