<?php

namespace App\Exceptions;

use Exception;

class ModelNotExistException extends Exception {
    public static function render(string $message) {
        return response()->json([
            'error' => true,
            'message' => [
                $message
            ]
        ], 422);
    }
}