<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class UserProductTest extends TestCase
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
        $actor = User::where('email', 'user@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        //2. hit API
        $response = $this->getJson('/api/user/products?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);
    }
}
