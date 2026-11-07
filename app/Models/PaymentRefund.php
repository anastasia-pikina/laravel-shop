<?php

namespace App\Models;

use App\Enums\RefundStatus;
use App\Models\Concerns\HasCatalogStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * A payment refund with a catalog-backed status.
 */
class PaymentRefund extends Model
{
    use HasCatalogStatus;

    protected $guarded = [];

    /**
     * @return string
     */
    protected static function statusEnumClass(): string
    {
        return RefundStatus::class;
    }

    /**
     * @return array
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'refunded_at' => 'datetime'];
    }
}
