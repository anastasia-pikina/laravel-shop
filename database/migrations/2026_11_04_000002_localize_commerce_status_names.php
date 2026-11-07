<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAMES = [
        'order_statuses' => [
            'awaiting_payment' => 'Ожидает оплаты',
            'confirmed' => 'Подтверждён',
            'processing' => 'В обработке',
            'completed' => 'Завершён',
            'cancelled' => 'Отменён',
        ],
        'delivery_statuses' => [
            'pending' => 'Ожидает подготовки',
            'preparing' => 'Готовится',
            'ready_for_pickup' => 'Готов к самовывозу',
            'in_transit' => 'В пути',
            'delivered' => 'Доставлен',
            'failed' => 'Доставка не удалась',
            'returned' => 'Возвращён на склад',
            'cancelled' => 'Отменена',
        ],
        'payment_statuses' => [
            'pending' => 'Ожидает оплаты',
            'succeeded' => 'Оплачен',
            'failed' => 'Оплата не прошла',
        ],
        'refund_statuses' => [
            'pending' => 'Ожидает возврата денег',
        ],
        'reservation_statuses' => [
            'active' => 'Активен',
            'released' => 'Освобождён',
            'consumed' => 'Использован',
        ],
    ];

    /**
     * @return void
     */
    public function up(): void
    {
        foreach (self::NAMES as $table => $names) {
            foreach ($names as $code => $name) {
                DB::table($table)->where('code', $code)->update(['name' => $name, 'updated_at' => now()]);
            }
        }
    }

    /**
     * @return void
     */
    public function down(): void
    {
        foreach (self::NAMES as $table => $names) {
            foreach ($names as $code => $name) {
                DB::table($table)->where('code', $code)->where('name', $name)
                    ->update(['name' => $code, 'updated_at' => now()]);
            }
        }
    }
};
