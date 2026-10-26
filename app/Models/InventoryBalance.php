<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $guarded = [];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
