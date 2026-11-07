<?php

namespace App\Enums;

/**
 * Payment methods available for an order.
 */
enum PaymentMethod: string
{
    case Online = 'online';
    case OnDelivery = 'on_delivery';
}
