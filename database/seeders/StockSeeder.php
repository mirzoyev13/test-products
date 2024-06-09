<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stock::factory(3)->create();
    }
}
