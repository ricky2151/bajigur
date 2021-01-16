<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Exceptions\InvalidParameterException; 
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Exceptions\ModelNotExistException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        
        if ($exception instanceof UnauthorizedHttpException) {

            $preException = $exception->getPrevious();
        
            if ($preException instanceof TokenInvalidException) {
                return response()->json(['error' => true,'message' => ["token"=>["Invalid Token"]]],400);
            }
            elseif ($preException instanceof TokenExpiredException) {
                return response()->json(['error' => true, 'message' => ["token"=>["Token is Expired"]]],400);
            }
            elseif ($preException instanceof TokenBlacklistedException) {
                return response()->json(['error' => true, 'message' => ["token"=>['Token is Blacklist']]],400);
            }
            
        
           if ($exception->getMessage() === 'Token not provided') {
               return response()->json(['error' => true, 'message' => ["token"=>['Token not provided']]], 400);
           }
           elseif ($exception->getMessage() === 'User not found'){
                return response()->json(['error' => true, 'message' => ["user"=>['User not found']]], 400);
           }
        }
        else if($exception instanceof LoginFailedException){
            return LoginFailedException::render($exception->getMessage());
        }
        elseif($exception instanceof InvalidParameterException){
            return InvalidParameterException::render($exception->getMessage());
        }
        elseif($exception instanceof ModelNotExistException){
            return ModelNotExistException::render($exception->getMessage());
        }
        elseif($exception instanceof ModelNotFoundException) {
            return ModelNotExistException::render("Data Not Found");
        }
        return parent::render($request, $exception);
    }

}
