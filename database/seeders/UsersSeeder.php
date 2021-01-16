<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\DetailTransaction;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create user with role user
        $userId = User::factory()->times(1)->create([
            'email' => 'user@gmail.com',
            'name' => 'user',
            'role_id' => 1
        ])[0]->id;
        
        //create 3 transaction
        //each transaction have 5 detail transaction
        Transaction::factory()->times(3)->create([
            'user_id' => $userId,
        ])->each(function($transaction) {
            $totalTransaction = 0;
            $detailTransaction = DetailTransaction::factory()->times(5)->create([
                'transaction_id' => $transaction->id,
            ])->each(function($detailTransaction) use(&$totalTransaction) {
                $productPrice = Product::find($detailTransaction->product_id)->price;
                $totalTransaction += $detailTransaction->qty * $productPrice;
            });
            $transaction->total = $totalTransaction;
            
            $transaction->save();
        });

        //create user with role admin
        User::factory()->times(1)->create([
            'email' => 'admin@gmail.com',
            'name' => 'admin',
            'role_id' => 2
        ]);
        //create user with role superadmin
        User::factory()->times(1)->create([
            'email' => 'superadmin@gmail.com',
            'name' => 'superadmin',
            'role_id' => 3
        ]);
        //create 30 user with role guard
        User::factory()->times(30)->create([
            'role_id' => 1
        ]);
        
    }
}
