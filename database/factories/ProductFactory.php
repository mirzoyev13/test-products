<?php

namespace Database\Factories;


use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product =  [
            'name' => $this->faker->name,
            'price' => $this->faker->randomDigit(),
        ];

        return $product;
    }
}
