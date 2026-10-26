<?php

return [
    // Courier checkout stays unavailable until its price is configured.
    'courier_delivery_amount' => env('COURIER_DELIVERY_AMOUNT'),
    'pickup_delivery_amount' => env('PICKUP_DELIVERY_AMOUNT', '0.00'),
    'online_reservation_minutes' => 30,
    'online_enabled' => env('SHOP_ONLINE_PAYMENT_ENABLED', false),
];
