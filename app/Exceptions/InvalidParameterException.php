<?php

namespace App\Exceptions;

use Exception;

class InvalidParameterException extends Exception {
    public static function render(string $error) {
        return response()->json([
            'error' => true,
            'message' => json_decode($error)
        ], 422);
    }
}