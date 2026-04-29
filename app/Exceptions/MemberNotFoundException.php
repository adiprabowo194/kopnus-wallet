<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class MemberNotFoundException extends Exception
{
    public function __construct(string $message = "Member tidak ditemukan")
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'code'    => 400,
            'message' => $this->getMessage(),
        ], 400);
    }
}
