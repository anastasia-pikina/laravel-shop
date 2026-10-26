<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDelivery extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['handed_over_at' => 'datetime', 'delivered_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function pickupPoint(): BelongsTo { return $this->belongsTo(PickupPoint::class); }
}
