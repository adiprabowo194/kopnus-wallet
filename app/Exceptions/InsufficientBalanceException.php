<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientBalanceException extends Exception
{


    public function render($request)
    {
        return response()->json([
            'status'  => 'error',
            'code'    => 403,
            'message' => 'Saldo tidak mencukupi biaya transaksi'
        ], 403);
    }
}
