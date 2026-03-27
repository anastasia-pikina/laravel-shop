<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'code',
        'parent_category_id',
    ];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_category_id');
    }
}
