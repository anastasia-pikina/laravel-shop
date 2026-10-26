<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime', 'released_at' => 'datetime'];
    }

    public function item(): BelongsTo { return $this->belongsTo(OrderItem::class, 'order_item_id'); }
}
