<?php

namespace App\Exceptions;

use App\Enums\CustomerStatus;
use Exception;

class CustomerAccountLockedException extends Exception
{
    public function __construct(
        public readonly CustomerStatus $status,
        string $message = 'This customer account is banned or suspended.'
    ) {
        parent::__construct($message);
    }
}
