<?php

namespace App\Exceptions;

use Exception;

class MemberInactiveException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'status'  => 'error',
            'code'    => 403,
            'message' => 'Member tidak aktif.',
        ], 403);
    }
}
