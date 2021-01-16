<?php

namespace App\Exceptions;

use Exception;

class LoginFailedException extends Exception {

    

    public static function render($message) {
        
        if($message == "")
        {
            $message = "undefined error";
        }
        return response()->json([
            "error" => true,
            "message" => [
                $message
            ]
            ],401);
    }
}