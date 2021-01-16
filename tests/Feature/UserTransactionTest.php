<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class UserTransactionTest extends TestCase
{
    protected function getHeaders($token) {
        return  [
            'Accept'        => 'application/json',
            'AUTHORIZATION' => 'Bearer ' . $token
        ];
    }

    protected function setUp(): void {
        parent::setUp();        

        //1. clear database and migrate
        Artisan::call('migrate:fresh --seed');
    }

    //======= API my history transaction ========

    /**
     * Feature test my history transaction
     * @return void
     */
    public function testMyHistoryTransaction()
    {
        //1. make token
        $actor = User::where('email', 'user@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        //2. hit API
        $response = $this->getJson('/api/user/transactions/my_history_transaction?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);
    }

    /**
     * Feature test store transaction
     * @dataProvider storeTransactionProvider
     * @return void
     */
    public function testStoreTransaction($data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', 'user@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;

        //2. hit API
        $response = $this->postJson('/api/user/transactions?token='.$token, $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function storeTransactionProvider()
    {
        //body
        $validBody = [
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 1
                ],
                [
                    'product_id' => 2,
                    'qty' => 1
                ],
            ]
            
        ];

        $missingAttributeBody = [ //missing qty
            'items' => [
                [
                    'product_id' => 1,
                ],
                [
                    'product_id' => 2,
                    'qty' => 1
                ],
            ]
            
        ];

        $invalidAttributeQtyBody = [
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 9999999 //this is invalid because no one product have qty 999999
                ],
                [
                    'product_id' => 2,
                    'qty' => 1
                ],
            ]
            
        ];

        $invalidAttributeProductIdBody = [
            'items' => [
                [
                    'product_id' => 999999, //this is invalid because no one product have product id 999999
                    'qty' => 1
                ],
                [
                    'product_id' => 2,
                    'qty' => 1
                ],
            ]
            
        ];

        //success response
        $successResponse = [
            "error" => false,
        ];


        //error response
        $missingAttributeErrorMessage = [
            "message" => "The given data was invalid.",
            "errors" => [
                "items.0.qty" => [
                    "The items.0.qty field is required."
                ]
            ]
        ];

        $invalidQtyErrorMessage = [
            "error" => true,
        ];
        
        $invalidProductIdErrorMessage = [
            "message" => "The given data was invalid.",
            "errors" => [
                "items.0.product_id" => [
                    "The selected items.0.product_id is invalid."
                ]
            ]
        ];
        

        //[email, password, status code, response message]
        return [
            'when insert with valid data, then return correct response' => [$validBody, 200, $successResponse],
            'when insert with missing attribute data, then return correct error' => [$missingAttributeBody, 422, $missingAttributeErrorMessage],
            'when insert with invalid attribute qty data, then return correct error' => [$invalidAttributeQtyBody, 422, $invalidQtyErrorMessage],
            'when insert with invalid attribute product id data, then return correct error' => [$invalidAttributeProductIdBody, 422, $invalidProductIdErrorMessage],
        ];
    }
}
