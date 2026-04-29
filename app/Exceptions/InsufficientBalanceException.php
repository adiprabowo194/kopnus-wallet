<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientBalanceException extends Exception
{
    public function __construct(
        string $message = 'Saldo tidak mencukupi biaya transaksi'
    ) {
        parent::__construct($message);
    }
}
