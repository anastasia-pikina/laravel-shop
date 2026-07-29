<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    private static array $categoryNames = [
        'Электроника', 'Одежда', 'Спорт', 'Книги', 'Дом и сад',
        'Красота', 'Автотовары', 'Детям', 'Продукты', 'Зоотовары',
    ];

    public function definition(): array
    {
        $name = static::$categoryNames[array_rand(static::$categoryNames)];

        return [
            'name' => $name,
            'code' => Str::slug($name),
            'parent_category_id' => null,
        ];
    }

    public function childOf(ProductCategory $parent): static
    {
        return $this->state(fn () => ['parent_category_id' => $parent->id]);
    }
}
