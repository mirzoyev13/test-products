<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'customer' => $this->faker->name,
            'warehouse_id' => Warehouse::factory(),
            'status' => $this->faker->randomElement(['active', 'completed', 'canceled']),
            'created_at' => $this->faker->dateTimeThisYear,
            'completed_at' => $this->faker->optional()->dateTimeThisYear,
        ];
    }
}


