<?php

namespace App\Enums;

/**
 * Delivery methods available for an order.
 */
enum DeliveryMethod: string
{
    case Courier = 'courier';
    case Pickup = 'pickup';

    /**
     * @return bool
     */
    public function isPickup(): bool
    {
        return $this === self::Pickup;
    }

    /**
     * @return bool
     */
    public function isCourier(): bool
    {
        return $this === self::Courier;
    }
}
