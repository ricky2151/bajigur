<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\StoreUser;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\LoginFailedException;

class UserController extends Controller
{
    private $user;
    public function __construct(User $user)
    {
        //superadmin can update/delete admin/member data
        //admin can update/delete member data
        $this->middleware('RoleSuperadmin')->only(['updateAdminData']);
        $this->middleware('RoleUser')->only(['updateMeAsUser']);

        $this->user = $user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->user->orderBy('id', 'desc')->get();

        $data = $data->map(function ($data) { 
            $data = Arr::add($data, 'role_name', $data['role']['name']);
            return Arr::except($data, ['role']);
        });
        
        return response()->json(['error' => false, 'data'=>$data]);
    }

    public function store(StoreUser $request) {
        $currentRole = Auth::user()->role_id;
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        if(!$data['role_id'])
        {
            $data['role_id'] = 1;
        } else {
            if ($currentRole == 2 && $data['role_id'] != 1) {
                throw new LoginFailedException("You Are Not Admin !");
            } else if ($currentRole == 3 && ($data['role_id'] != 2) && $data['role_id'] != 1) {
                throw new LoginFailedException("You Are Not Admin !");
            }
        }

        $user = new User;
        $user = $user->create($data);
        $token = auth('api')->fromUser($user);

        return response()->json([
            'error' => false,
            'access_token' => $token, 
            'user' => $user
        ]);
    }

    public function update(UpdateUser $request, User $user) {

        $currentRole = Auth::user()->role_id;
        $data = $request->validated();
        if(isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        
        //check role 
        //all user should not update role !
        if(isset($data['role_id'])) {
            unset($data['role_id']);
        }

        
        if ($currentRole == 2 && $user->role_id != 1) {
            throw new LoginFailedException("You Are Not Admin !");
        } else if ($currentRole == 3 && ($user->role_id != 2) && $user->role_id != 1) {
            throw new LoginFailedException("You Are Not Admin !");
        }
        

        $user->update($data);
        
        return response()->json([
            "error" => false,
            "message" => "user successfully updated !"
        ]);
    }

    public function updateMe(UpdateUser $request) {
        $data = $request->validated();
        $user = Auth::user();

        if(isset($data['role_id'])) {
            unset($data['role_id']);
        }
        
        $user->update($data);
        
        
        return response()->json([
            "error" => false,
            "message" => "user successfully updated !"
        ]);
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if($user->role_id == 2 || $user->role_id == 1)
        {
            $user->delete();

            return response()->json([
                "error" => false,
                "message" => "user successfully deleted !"
            ]);
        }
        else {
            return response()->json([
                "error" => true,
                "message" => "you cannot delete superadmin !"
            ]);
        }
        
    }

    public function historyTransaction(User $user) {
        $data = $user->transactions->map(function ($item) {
            return Arr::add($item, 'detail_transactions', $item['detail_transactions']->map(function($detail) {
                return Arr::add($detail, 'product_name', $detail['product']['name']);
            }));
        });
        
        return response()->json(['error' => false, 'data'=>$data]);
    }
}
