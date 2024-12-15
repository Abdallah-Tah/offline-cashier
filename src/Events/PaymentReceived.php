<?php

namespace AMohamed\OfflineCashier\Events;

use AMohamed\OfflineCashier\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment)
    {
    }
} 