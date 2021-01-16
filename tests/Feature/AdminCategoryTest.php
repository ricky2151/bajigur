<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
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

    //======= API get ========

    /**
     * Feature test get
     * @return void
     */
    public function testGet()
    {
        //1. make token
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        //2. hit API
        $response = $this->getJson('/api/admin/categories?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);
    }

    //======= API Insert ========
    /**
     * Feature test insert
     * @dataProvider insertProvider
     * @return void
     */
    public function testInsert($data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;

        //2. hit API
        $response = $this->postJson('/api/admin/categories?token='.$token, $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function insertProvider()
    {
        //body
        $validBody = [
            "name" => "kategori satu",
        ];

        $missingAttributeBody = [

        ];

        //success response
        $successResponse = [
            "error" => false,
        ];


        //error response
        $missingAttributeErrorMessage = [
            "message" => "The given data was invalid.",
            "errors" => [
                "name" => [
                    "The name field is required."
                ]
            ]
        ];

        //[email, password, status code, response message]
        return [
            'when insert with valid data, then return correct response' => [$validBody, 200, $successResponse],
            'when insert with missing attribute data, then return correct error' => [$missingAttributeBody, 422, $missingAttributeErrorMessage],
        ];
    }



    //======= API update ========
    /**
     * Feature test update
     * @dataProvider updateProvider
     * @return void
     */
    public function testUpdate($data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;
        $body['_method'] = 'patch';

        //2. hit API
        $response = $this->postJson('/api/admin/categories/1?token='.$token, $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function updateProvider()
    {
        //body
        $validBody = [
            "name" => "kategori dua",
        ];

        

        //success response
        $successResponse = [
            "error" => false,
        ];


       

        //[body, status code, response message]
        return [
            'when update with valid data, then return correct response' => [$validBody, 200, $successResponse],
        ];
    }

    //======= API delete ========
    /**
     * Feature test delete
     * @return void
     */
    public function testDelete()
    {
        //1. make token & prepare body
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);

        //2. hit API
        $response = $this->deleteJson('/api/admin/categories/1?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false
            ]);
    }

    

    
}
