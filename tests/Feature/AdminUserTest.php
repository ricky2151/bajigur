<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tests\TestCase;

class AdminUserTest extends TestCase
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
        $response = $this->getJson('/api/admin/users?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);
    }

    //======= API Insert ========
    /**
     * Feature test insert member account
     * @dataProvider insertMemberAccountProvider
     * @return void
     */
    public function testInsertMemberAccount($data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;

        //2. hit API
        $response = $this->postJson('/api/admin/users?token='.$token, $body);


        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function insertMemberAccountProvider()
    {
        //body
        $validBody = [
            "name" => "ricky sams", 
            "email" => "samuelricky7171@gmail.com",
            "gender" => "M",
            "dob" => "19-01-1998",
            "password" => "password",
            "role_id" => 1
        ];

        $missingAttributeBody = [ //missing password
            "name" => "ricky sams", 
            "email" => "samuelricky7171@gmail.com",
            "gender" => "M",
            "dob" => "19-01-1998",
            "role_id" => 1
        ];

        $invalidAttributeBody = [
            "name" => "ricky sams", 
            "email" => "samuelricky7171@gmail.com",
            "gender" => "M",
            "dob" => "19-01-1998",
            "password" => "password",
            "role_id" => 2 //invalid, because admin cannot insert admin account
        ];

        //success response
        $successResponse = [
            "error" => false,
        ];


        //error response
        $missingAttributeErrorMessage = [
            "error" => true,
            "message" => [
                "password" => [
                    "The password field is required."
                ]
            ]
        ];

        $invalidErrorMessage = [
            "error" => true,
            "message" => [
                "You Are Not Admin !"
            ]
        ];

        //[email, password, status code, response message]
        return [
            'when insert with valid data member, then return correct response' => [$validBody, 200, $successResponse],
            'when insert with missing attribute data member, then return correct error' => [$missingAttributeBody, 422, $missingAttributeErrorMessage],
            'when insert with invalid data member, then return correct error' => [$invalidAttributeBody, 401, $invalidErrorMessage],
        ];
    }

    /**
     * Feature test insert admin account
     * @dataProvider insertAdminAccountProvider
     * @return void
     */
    public function testInsertAdminAccount($data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', 'superadmin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;

        //2. hit API
        $response = $this->postJson('/api/admin/users?token='.$token, $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function insertAdminAccountProvider()
    {
        //body
        $validBody = [
            "name" => "ricky sams", 
            "email" => "samuelricky7171@gmail.com",
            "gender" => "M",
            "dob" => "19-01-1998",
            "password" => "password",
            "role_id" => 2
        ];

        $missingAttributeBody = [ //missing password
            "name" => "ricky sams", 
            "email" => "samuelricky7171@gmail.com",
            "gender" => "M",
            "dob" => "19-01-1998",
            "role_id" => 2
        ];

        $invalidAttributeBody = [
            "name" => "ricky sams", 
            "email" => "samuelricky7171@gmail.com",
            "gender" => "M",
            "dob" => "19-01-1998",
            "password" => "password",
            "role_id" => 3 //invalid, because superadmin cannot insert superadmin account
        ];

        //success response
        $successResponse = [
            "error" => false,
        ];


        //error response
        $missingAttributeErrorMessage = [
            "error" => true,
            "message" => [
                "password" => [
                    "The password field is required."
                ]
            ]
        ];

        $invalidErrorMessage = [
            "error" => true,
            "message" => [
                "You Are Not Admin !"
            ]
        ];

        //[email, password, status code, response message]
        return [
            'when insert with valid data admin, then return correct response' => [$validBody, 200, $successResponse],
            'when insert with missing attribute data admin, then return correct error' => [$missingAttributeBody, 422, $missingAttributeErrorMessage],
            'when insert with invalid data admin, then return correct error' => [$invalidAttributeBody, 401, $invalidErrorMessage],
        ];
    }



    //======= API update ========
    /**
     * Feature test update
     * @dataProvider updateProvider
     * @return void
     */
    public function testUpdate($loginAsEmail, $idUser, $data, $statusExpected, $jsonExpected)
    {
        //1. make token & prepare body
        $actor = User::where('email', $loginAsEmail)->first();
        $token = JWTAuth::fromUser($actor);
        $body = $data;
        $body['_method'] = 'patch';

        //2. hit API
        $response = $this->postJson('/api/admin/users/' . $idUser . '?token='.$token, $body);

        //3. assert response
        $response
            ->assertStatus($statusExpected)
            ->assertJson($jsonExpected);
    }

    public function updateProvider()
    {
        //login as email
        $loginAsEmailMember = "user@gmail.com";
        $loginAsEmailAdmin = "admin@gmail.com";
        $loginAsEmailSuperadmin = "superadmin@gmail.com";

        //user to update
        $idUserMember = 1;
        $idUserAdmin = 2;
        $idUserSuperadmin = 3;

        //body
        $validBody = [
            "name" => "user name updated",
        ];        

        $invalidBody = [
            "gender" => "WKWK",
        ];

        //success response
        $successResponse = [
            "error" => false,
        ];

        $unauthorizedErrorMessage = [
            "error" => true,
            "message" => [
                "You Are Not Admin !"
            ]
        ];

        $invalidAttributeErrorMessage = [
            "message" => "The given data was invalid.",
            "errors" => [
                "gender" => [
                    "The selected gender is invalid."
                ]
            ]
                ];



       

        //[body, status code, response message]
        return [
            'when login as member and update member valid data, then return correct error' => [$loginAsEmailMember, $idUserMember, $validBody, 401, $unauthorizedErrorMessage],
            'when login as member and update admin valid data, then return correct error' => [$loginAsEmailMember, $idUserAdmin, $validBody, 401, $unauthorizedErrorMessage],
            'when login as member and update superadmin valid data, then return correct error' => [$loginAsEmailMember, $idUserSuperadmin, $validBody, 401, $unauthorizedErrorMessage],

            'when login as admin and update user valid data, then return correct response' => [$loginAsEmailAdmin, $idUserMember, $validBody, 200, $successResponse],
            'when login as admin and update user invalid data, then return correct error' => [$loginAsEmailAdmin, $idUserMember, $invalidBody, 422, $invalidAttributeErrorMessage],
            'when login as admin and update admin valid data, then return correct error' => [$loginAsEmailAdmin, $idUserAdmin, $validBody, 401, $unauthorizedErrorMessage],
            'when login as admin and update superadmin valid data, then return correct error' => [$loginAsEmailAdmin, $idUserSuperadmin, $validBody, 401, $unauthorizedErrorMessage],

            'when login as superadmin and update user valid data, then return correct response' => [$loginAsEmailSuperadmin, $idUserMember, $validBody, 200, $successResponse],
            'when login as superadmin and update admin valid data, then return correct response' => [$loginAsEmailSuperadmin, $idUserAdmin, $validBody, 200, $successResponse],
            'when login as superadmin and update superadmin valid data, then return correct error' => [$loginAsEmailSuperadmin, $idUserSuperadmin, $validBody, 401, $unauthorizedErrorMessage],
            
        ];
    }


    //======= API get history transaction ========

    /**
     * Feature test get history transaction
     * @return void
     */
    public function testGetHistoryTransaction()
    {
        //1. make token
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);
        //2. hit API
        $response = $this->getJson('/api/admin/users/history_transactions/1?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);
    }

    //======= API delete ========
    /**
     * Feature test admin delete member
     * @return void
     */
    public function testAdminDeleteMember()
    {
        //1. make token & prepare body
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);

        //2. hit API
        $response = $this->deleteJson('/api/admin/users/1?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false
            ]);
    }

    /**
     * Feature test admin delete admin
     * @return void
     */
    public function testAdminDeleteAdmin()
    {
        //1. make token & prepare body
        $actor = User::where('email', 'admin@gmail.com')->first();
        $token = JWTAuth::fromUser($actor);

        //2. hit API
        $response = $this->deleteJson('/api/admin/users/20?token='.$token);

        //3. assert response
        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false
            ]);
    }
}
