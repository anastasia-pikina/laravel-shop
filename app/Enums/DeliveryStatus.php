<?php

namespace App\Enums;

/**
 * Delivery states and their allowed transitions.
 */
enum DeliveryStatus: string
{
    // Доставка создана, подготовка ещё не началась.
    case Pending = 'pending';

    // Заказ готовят к передаче покупателю или курьеру.
    case Preparing = 'preparing';

    // Заказ ждёт покупателя в точке самовывоза.
    case ReadyForPickup = 'ready_for_pickup';

    // Заказ передан курьеру и находится в пути.
    case InTransit = 'in_transit';

    // Покупатель получил заказ; для завершения заказа также нужна оплата.
    case Delivered = 'delivered';

    // Курьерская доставка не состоялась.
    case Failed = 'failed';

    // Товар после неудачной доставки вернулся на склад, остаток восстановлен.
    case Returned = 'returned';

    // Заказ отменён, доставка прекращена, активный резерв освобождён.
    case Cancelled = 'cancelled';

    /**
     * @param DeliveryMethod $method
     *
     * @return list<self>
     */
    public function allowedNext(DeliveryMethod $method): array
    {
        return match ($this) {
            self::Pending => [self::Preparing],
            self::Preparing => $method->isPickup() ? [self::ReadyForPickup] : [self::InTransit],
            self::ReadyForPickup => $method->isPickup() ? [self::Delivered] : [],
            self::InTransit => $method->isCourier() ? [self::Delivered, self::Failed] : [],
            default => [],
        };
    }

    /**
     * @param self $next
     * @param DeliveryMethod $method
     *
     * @return bool
     */
    public function canTransitionTo(self $next, DeliveryMethod $method): bool
    {
        return in_array($next, $this->allowedNext($method), true);
    }
}
