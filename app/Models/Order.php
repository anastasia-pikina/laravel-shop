<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Concerns\HasCatalogStatus;
use App\Support\EnumCatalog;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use ValueError;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An order and its catalog-backed attributes.
 */
class Order extends Model
{
    use HasCatalogStatus;

    protected $guarded = [];

    /**
     * @return string
     */
    protected static function statusEnumClass(): string
    {
        return OrderStatus::class;
    }

    /**
     * @return array
     */
    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime', 'expires_at' => 'datetime',
            'cancelled_at' => 'datetime', 'completed_at' => 'datetime',
            'subtotal' => 'decimal:2', 'delivery_amount' => 'decimal:2', 'total' => 'decimal:2',
        ];
    }

    /**
     * @return ?PaymentMethod
     * @throws LogicException
     */
    public function getPaymentMethodAttribute(): ?PaymentMethod
    {
        $id = $this->attributes['payment_method_id'] ?? null;

        return $id === null ? null : app(EnumCatalog::class)->caseForId(PaymentMethod::class, (int) $id);
    }

    /**
     * @param PaymentMethod|string $method
     *
     * @return void
     * @throws LogicException
     * @throws ValueError
     */
    public function setPaymentMethodAttribute(PaymentMethod|string $method): void
    {
        $method = is_string($method) ? PaymentMethod::from($method) : $method;
        $this->attributes['payment_method_id'] = app(EnumCatalog::class)->id($method);
    }

    /**
     * @return string
     * @throws LogicException
     */
    public function paymentMethodName(): string
    {
        return app(EnumCatalog::class)->name(PaymentMethod::class, (int) $this->attributes['payment_method_id']);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    /**
     * @return HasMany
     */
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    /**
     * @return HasOne
     */
    public function delivery(): HasOne { return $this->hasOne(OrderDelivery::class); }
    /**
     * @return HasMany
     */
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
