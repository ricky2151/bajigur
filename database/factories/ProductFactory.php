<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'price' => mt_rand(1,100) * 1000,
            'category_id' => mt_rand(1,3),
            'stock' => mt_rand(3,10),
            'points_earned' => mt_rand(0,10),
        ];
    }
}
