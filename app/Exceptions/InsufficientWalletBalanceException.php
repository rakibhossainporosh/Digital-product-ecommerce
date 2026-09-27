<?php

namespace App\Exceptions;

use Exception;

class InsufficientWalletBalanceException extends Exception
{
    public function __construct(
        public readonly float $currentBalance,
        public readonly float $requiredAmount,
        string $message = 'Insufficient wallet balance for this operation.'
    ) {
        parent::__construct($message);
    }
}
