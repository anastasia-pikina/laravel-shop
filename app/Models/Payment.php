<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasCatalogStatus;
use App\Support\EnumCatalog;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use ValueError;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A payment and its catalog-backed attributes.
 */
class Payment extends Model
{
    use HasCatalogStatus;

    protected $guarded = [];

    /**
     * @return string
     */
    protected static function statusEnumClass(): string
    {
        return PaymentStatus::class;
    }

    /**
     * @return array
     */
    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    /**
     * @return ?PaymentMethod
     * @throws LogicException
     */
    public function getMethodAttribute(): ?PaymentMethod
    {
        $id = $this->attributes['method_id'] ?? null;

        return $id === null ? null : app(EnumCatalog::class)->caseForId(PaymentMethod::class, (int) $id);
    }

    /**
     * @param PaymentMethod|string $method
     *
     * @return void
     * @throws LogicException
     * @throws ValueError
     */
    public function setMethodAttribute(PaymentMethod|string $method): void
    {
        $method = is_string($method) ? PaymentMethod::from($method) : $method;
        $this->attributes['method_id'] = app(EnumCatalog::class)->id($method);
    }

    /**
     * @return string
     * @throws LogicException
     */
    public function methodName(): string
    {
        return app(EnumCatalog::class)->name(PaymentMethod::class, (int) $this->attributes['method_id']);
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    /**
     * @return HasMany
     */
    public function refunds(): HasMany { return $this->hasMany(PaymentRefund::class); }
}
