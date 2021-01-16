<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUser;
use App\Http\Requests\ValidateLogin;
use App\Exceptions\LoginFailedException;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login', 'registerAsUser']]);
    }

   
    public function registerAsUser(StoreUser $request) {
        
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $data['role_id'] = 1;
        $user = new User;
        $user = $user->create($data);
        $token = auth('api')->fromUser($user);

        return response()->json([
            'error' => false,
            'access_token' => $token, 
            'user' => $user
        ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(ValidateLogin $request)
    {
        $data = $request->validated();
        $token = "";
        
        if (! $token = auth()->attempt($data)) {
            throw new LoginFailedException("Wrong Credentials !");
        }

        $user = Auth::user();
        $response = [
            'error' => false,
            'authenticate' => true,
            'access_token' => $token,
            'user' => $user,
            'message' => 'Login Success',
        ];

        return response()->json($response);

        
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if(Auth::check())
        {
            // echo "authcheck : " . Auth::check() ." \n";
            // print_r(Auth::user());
            // echo "\n====\n";
            $response = [
                'error' => false,
                'data' => [
                    'user' => Auth::user()
                ]
            ];
    
            return response()->json($response);
        }
        else
        {
            if(JWTAuth::parseToken()->authenticate())
            {
                //something wrong with JWT !
                //if auth::check is false, then it should not enter here !
                throw new LoginFailedException("There is problem in authentication server !");
            }
        }
        
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['error' => false, 'message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }   

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    
}
