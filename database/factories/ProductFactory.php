<?php

// database/factories/ProductFactory.php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.Str::lower(Str::random(6))),
            'price' => $this->faker->randomFloat(2, 10, 2000),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
