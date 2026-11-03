<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['status' => RefundStatus::class, 'amount' => 'decimal:2', 'refunded_at' => 'datetime'];
    }
}
