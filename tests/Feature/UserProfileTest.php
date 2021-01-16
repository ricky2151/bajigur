<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class UserProfileTest extends TestCase
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

    //======= API update me ========
    /**
     * Feature test update me
     * @dataProvider updateMeProvider
     * @return void
     */
    public function testUpdateMe($data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', 'user@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;
        $body['_method'] = 'patch';

        //2. hit API
        $response = $this->postJson('/api/user/users/update_me?token='.$token, $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function updateMeProvider()
    {
        //body
        $validBody = [
            "name" => "ricky ganteng",
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
}
