<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Concerns\HasCatalogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stock reservation with a catalog-backed status.
 */
class StockReservation extends Model
{
    use HasCatalogStatus;

    protected $guarded = [];

    /**
     * @return string
     */
    protected static function statusEnumClass(): string
    {
        return ReservationStatus::class;
    }

    /**
     * @return array
     */
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime', 'released_at' => 'datetime'];
    }

    /**
     * @return BelongsTo
     */
    public function item(): BelongsTo { return $this->belongsTo(OrderItem::class, 'order_item_id'); }
}
