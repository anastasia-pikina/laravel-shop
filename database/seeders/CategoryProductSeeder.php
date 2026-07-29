<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryProductSeeder extends Seeder
{
    private array $childCategories = [
        'Электроника' => ['Смартфоны', 'Ноутбуки', 'Наушники', 'Планшеты'],
        'Одежда' => ['Верхняя одежда', 'Платья', 'Обувь', 'Аксессуары'],
        'Спорт' => ['Фитнес', 'Велоспорт', 'Плавание', 'Туризм'],
        'Книги' => ['Художественная литература', 'Деловая литература', 'Детские книги'],
        'Дом и сад' => ['Мебель', 'Посуда', 'Инструменты', 'Декор'],
    ];

    public function run(): void
    {
        foreach ($this->childCategories as $rootName => $children) {
            $root = ProductCategory::factory()->create([
                'name' => $rootName,
                'code' => Str::slug($rootName),
            ]);

            foreach ($children as $childName) {
                $child = ProductCategory::factory()
                    ->childOf($root)
                    ->create([
                        'name' => $childName,
                        'code' => Str::slug($childName),
                    ]);

                Product::factory(rand(3, 7))->forCategory($child)->create();
            }
        }
    }
}
