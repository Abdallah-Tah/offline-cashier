<?php

namespace AMohamed\OfflineCashier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Payment
 * 
 * Represents a payment record in the system.
 *
 * @property int $subscription_id The ID of the associated subscription
 * @property float $amount The payment amount
 * @property string $payment_method The method used for payment (e.g. credit card, cash)
 * @property string $status The payment status (e.g. pending, completed)
 * @property string $reference_number A unique reference number for the payment
 * @property string|null $stripe_payment_id The Stripe Payment ID if processed through Stripe
 * @property \DateTime $paid_at The date and time when the payment was made
 * @property string|null $notes Additional notes about the payment
 */
class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'subscription_id',
        'amount',
        'payment_method',
        'status',
        'reference_number',
        'stripe_payment_id',
        'paid_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the subscription associated with this payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('offline-cashier.models.subscription'));
    }
} 