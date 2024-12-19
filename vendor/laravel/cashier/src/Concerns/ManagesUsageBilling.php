<?php

namespace Laravel\Cashier\Concerns;

use Illuminate\Support\Collection;
use Stripe\Billing\MeterEvent;

trait ManagesUsageBilling
{
    /**
     * Get all of the defined billing meters.
     *
     * @param  array  $options
     * @param  array  $requestOptions
     * @return \Illuminate\Support\Collection
     */
    public function meters(array $options = [], array $requestOptions = []): Collection
    {
        return new Collection($this->stripe()->billing->meters->all($options, $requestOptions)->data);
    }

    /**
     * Report usage for a metered product.
     *
     * @param  string  $meter
     * @param  int  $quantity
     * @param  string|null  $price
     * @param  array  $options
     * @param  array  $requestOptions
     * @return \Stripe\Billing\MeterEvent
     */
    public function reportMeterEvent(
        string $meter,
        int $quantity = 1,
        array $options = [],
        array $requestOptions = []
    ): MeterEvent {
        $this->assertCustomerExists();

        return $this->stripe()->billing->meterEvents->create([
            'event_name' => $meter,
            'payload' => [
                'stripe_customer_id' => $this->stripeId(),
                'value' => $quantity,
            ],
            ...$options,
        ], $requestOptions);
    }

    /**
     * Get the usage records for a meter using its ID.
     *
     * @param  string  $meterId
     * @param  array  $options
     * @param  array  $requestOptions
     * @return \Illuminate\Support\Collection
     */
    public function meterEventSummaries(string $meterId, int $startTime = 1, ?int $endTime = null, array $options = [], array $requestOptions = []): Collection
    {
        $this->assertCustomerExists();

        if (! isset($endTime)) {
            $endTime = time();
        }

        return new Collection($this->stripe()->billing->meters->allEventSummaries(
            $meterId,
            [
                'customer' => $this->stripeId(),
                'start_time' => $startTime,
                'end_time' => $endTime,
                ...$options,
            ],
            $requestOptions
        )->data);
    }
}
