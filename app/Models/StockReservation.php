<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['status' => ReservationStatus::class, 'expires_at' => 'datetime', 'consumed_at' => 'datetime', 'released_at' => 'datetime'];
    }

    public function item(): BelongsTo { return $this->belongsTo(OrderItem::class, 'order_item_id'); }
}
