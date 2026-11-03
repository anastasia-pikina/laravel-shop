<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public function allowedNext(string $method): array
    {
        if (! in_array($method, ['pickup', 'courier'], true)) {
            return [];
        }

        return match ($this) {
            self::Pending => [self::Preparing],
            self::Preparing => $method === 'pickup' ? [self::ReadyForPickup] : [self::InTransit],
            self::ReadyForPickup => $method === 'pickup' ? [self::Delivered] : [],
            self::InTransit => $method === 'courier' ? [self::Delivered, self::Failed] : [],
            default => [],
        };
    }

    public function canTransitionTo(self $next, string $method): bool
    {
        return in_array($next, $this->allowedNext($method), true);
    }
}
