<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Models\Concerns\HasCatalogStatus;
use App\Support\EnumCatalog;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use ValueError;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Delivery details and catalog-backed attributes.
 */
class OrderDelivery extends Model
{
    use HasCatalogStatus;

    protected $guarded = [];

    /**
     * @return string
     */
    protected static function statusEnumClass(): string
    {
        return DeliveryStatus::class;
    }

    /**
     * @return array
     */
    protected function casts(): array
    {
        return ['handed_over_at' => 'datetime', 'delivered_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    /**
     * @return ?DeliveryMethod
     * @throws LogicException
     */
    public function getMethodAttribute(): ?DeliveryMethod
    {
        $id = $this->attributes['method_id'] ?? null;

        return $id === null ? null : app(EnumCatalog::class)->caseForId(DeliveryMethod::class, (int) $id);
    }

    /**
     * @param DeliveryMethod|string $method
     *
     * @return void
     * @throws LogicException
     * @throws ValueError
     */
    public function setMethodAttribute(DeliveryMethod|string $method): void
    {
        $method = is_string($method) ? DeliveryMethod::from($method) : $method;
        $this->attributes['method_id'] = app(EnumCatalog::class)->id($method);
    }

    /**
     * @return string
     * @throws LogicException
     */
    public function methodName(): string
    {
        return app(EnumCatalog::class)->name(DeliveryMethod::class, (int) $this->attributes['method_id']);
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    /**
     * @return BelongsTo
     */
    public function pickupPoint(): BelongsTo { return $this->belongsTo(PickupPoint::class); }
}
