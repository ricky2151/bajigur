<?php

namespace Tests\Feature;


use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
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

    //======= API Login ========

    /**
     * Feature test login
     * @dataProvider LoginProvider
     * @return void
     */
    public function testLogin($email, $password, $statusExpected, $jsonExpected)
    {
        //1. make body request
        $body = ['email' => $email, 'password' => $password];

        //2. hit API
        $response = $this->postJson('/api/auth/login', $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function loginProvider()
    {
        //account
        $invalidPassword = "xxxxxxx";
        $validUserEmail = "user@gmail.com";
        $validAdminEmail = "admin@gmail.com";
        $validSuperadminEmail = "superadmin@gmail.com";
        $validPassword = "password";

        //error messages
        $authenticationErrorMessage = [
            "error" => true,
            "message" => ["Wrong Credentials !"]
        ];
        $loginSuccessMessage = [
            "error" => false,
            "authenticate" => true,
        ];
        //[email, password, status code, response message]
        return [
            'when email is valid and password is invalid, then return correct error' => [$validUserEmail,$invalidPassword, 401, $authenticationErrorMessage],
            'when login with valid user credential, then return correct data' => [$validUserEmail, $validPassword, 200, $loginSuccessMessage],
            'when login with valid admin credential, then return correct data' => [$validAdminEmail, $validPassword, 200, $loginSuccessMessage],
            'when login with valid superadmin credential, then return correct data' => [$validSuperadminEmail, $validPassword, 200, $loginSuccessMessage],
        ];
    }

    //======= API me ========

    /**
     * Feature test me with invalid token provider
     * @dataProvider meWithInvalidTokenProvider
     * @return void
     */
    public function testMeWithInvalidToken($token, $statusExpected, $jsonExpected)
    {
        //1. hit API
        $response = $this->postJson('/api/auth/me', [], $this->getHeaders($token));

        //2. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function meWithInvalidTokenProvider()
    {
        //error messages
        $tokenNotProvide = [
            "error" => true,
            "message" => [
                "token" => [
                    "Token not provided"
                ]
            ]
        ];

        $tokenInvalid = [
            "error" => true,
            "message" => [
                "token" => [
                    "Invalid Token"
                ]
            ]
        ];
        
        
        //[token, status code, response message]
        return [
            'when token is undfined, then return correct error' => [null, 400, $tokenNotProvide],
            'when token is empty, then return correct error' => ['', 400, $tokenNotProvide],
            'when token is invalid, then return correct error' => ['wkwkwkwk', 400, $tokenInvalid],
        ];
    }

    /**
     * Feature test me with valid token provider
     * @dataProvider meWithValidTokenProvider
     * @return void
     */
    public function testMeWithValidToken($email, $statusExpected, $jsonExpected)
    {
        $actor = User::where('email', $email)->first();
        //1. hit API
        $token = JWTAuth::fromUser($actor);
        $response = $this->postJson('/api/auth/me?token='.$token);
        

        //2. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJsonStructure($jsonExpected);
    }

    public function meWithValidTokenProvider()
    {
        //actor
        $userEmail = 'user@gmail.com';
        $adminEmail = 'admin@gmail.com';
        $superadminEmail = 'superadmin@gmail.com';
        //success response
        $successResponse = [
            "data" => [
                "user" => [
                    "id",
                    "name",
                    "email"
                ]
            ]
        ];
    
        //[email, status code, response message]
        return [
            'when use valid user token, then return correct response' => [$userEmail, 200, $successResponse],
            'when use valid admin token, then return correct response' => [$adminEmail, 200, $successResponse],
            'when use valid superadmin token, then return correct response' => [$superadminEmail, 200, $successResponse],
        ];
    }

    //======= API Logout ========

    /**
     * Feature test logout with invalid token provider
     * @dataProvider logoutWithInvalidTokenProvider
     * @return void
     */
    public function testLogoutWithInvalidToken($token, $statusExpected, $jsonExpected)
    {
        //1. hit API
        $response = $this->postJson('/api/auth/logout', [], $this->getHeaders($token));

        //2. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function logoutWithInvalidTokenProvider()
    {
        //error messages
        $tokenNotProvide = [
            "error" => true,
            "message" => [
                "token" => [
                    "Token not provided"
                ]
            ]
        ];

        $tokenInvalid = [
            "error" => true,
            "message" => [
                "token" => [
                    "Invalid Token"
                ]
            ]
        ];
        
        
        //[token, status code, response message]
        return [
            'when token is undfined, then return correct error' => [null, 400, $tokenNotProvide],
            'when token is empty, then return correct error' => ['', 400, $tokenNotProvide],
            'when token is invalid, then return correct error' => ['wkwkwkwk', 400, $tokenInvalid],
        ];
    }

    /**
     * Feature test logout with valid token provider
     * @return void
     */
    public function testLogoutWithValidToken()
    {
        //1. prepare body to login as user
        $body = [
            'email' => 'user@gmail.com',
            'password' => 'password'
        ];

        //2. hit login API 
        $response = $this->postJson('/api/auth/login', $body);

        //3. get token
        $token = $response->getData()->access_token;

        //4. hit logout API
        $response = $this->postJson('/api/auth/logout?token='.$token);

        //5. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                "error" => false,
            ]);
    }

    //======= API Register ========
    /**
     * Feature test register
     * @dataProvider registerProvider
     * @return void
     */
    public function testRegister($data, $statusExpected, $jsonExpected)
    {
        //1. make body request
        $body = $data;

        //2. hit API
        $response = $this->postJson('/api/auth/register', $body);


        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function registerProvider()
    {
        //body
        $validBody = [
            "name" => "yonatan kristanto",
            "email" => "yonatan.kristanto@gmail.com",
            "gender" => "M",
            "dob" => "04-09-1998",
            "password" => "password"
        ];

        $missingAttributeBody = [ //no name attribute
            "email" => "yonatan.kristanto2@gmail.com",
            "gender" => "M",
            "dob" => "04-09-1998",
            "password" => "password"
        ];

        $invalidAttributeBody = [
            "name" => "yonatan kristanto",
            "email" => "yonatan.kristanto3@gmail.com",
            "gender" => "MF", //<= invalid
            "dob" => "04-09-1998",
            "password" => "password"
        ];

        $duplicateDataBody = [
            "name" => "user",
            "email" => "user@gmail.com", //<= duplicate
            "gender" => "F",
            "dob" => "03-05-1998",
            "password" => "password"
        ];

        //success response
        $successResponse = [
            "error" => false,
        ];


        //error response
        $missingAttributeErrorMessage = [
            "error" => true,
            "message" => [
                "name" => [
                    "The name field is required."
                ],
            ]
        ];

        $invalidAttributeErrorMessage = [
            "error" => true,
            "message" => [
                "gender" => [
                    "The selected gender is invalid."
                ]
            ]
        ];

        $duplicateDataErrorMessage = [
            "error" => true,
            "message" => [
                "email" => [
                    "The email has already been taken."
                ]
            ]
        ];

        //[email, password, status code, response message]
        return [
            'when register with valid data, then return correct response' => [$validBody, 200, $successResponse],
            'when register with missing attribute data, then return correct error' => [$missingAttributeBody, 422, $missingAttributeErrorMessage],
            'when register with invalid attribute data, then return correct error' => [$invalidAttributeBody, 422, $invalidAttributeErrorMessage],
            'when register with duplicate data, then return correct error' => [$duplicateDataBody, 422, $duplicateDataErrorMessage],
        ];
    }    

}
