<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function allowsDeliveryUpdate(): bool
    {
        return in_array($this, [self::Confirmed, self::Processing], true);
    }

    public function allowsCancellation(): bool
    {
        return in_array($this, [self::AwaitingPayment, self::Confirmed, self::Processing], true);
    }
}
