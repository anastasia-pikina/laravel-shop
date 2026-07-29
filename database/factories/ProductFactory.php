<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(rand(2, 4), true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 100, 100000),
            'category_id' => ProductCategory::inRandomOrder()->first()->id,
        ];
    }

    public function forCategory(ProductCategory $category): static
    {
        return $this->state(fn () => ['category_id' => $category->id]);
    }
}
