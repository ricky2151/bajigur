<?php

namespace Database\Factories;

use App\Models\DetailTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DetailTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $productsId = array();
        for($i = 1;$i<=30;$i++) {
            $productsId[] = $i;
        }
        
        //random unique number from 1 to 5. After unique number is fulfilled, then 
        //reset unique faker (see in catch), so it can start generate new unique number again and again.
        try {
            $uniqueIndex = $this->faker->unique()->numberBetween(1, 5);
        } catch (\OverflowException $e) {
            $uniqueIndex = $this->faker->unique(true)->numberBetween(1, 5);
        }

        //use generated number above to define id not just 1 to 5, but it can spread from 1+n until 10+n 
        $uniqueProductIdEachTransaction = $uniqueIndex + (5*mt_rand(1,2));


        return [
            'transaction_id' => 1,
            'product_id' => $uniqueProductIdEachTransaction,
            'qty' => mt_rand(1,5),
        ];
    }
}
