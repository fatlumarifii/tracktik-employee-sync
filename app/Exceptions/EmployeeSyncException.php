<?php

namespace App\Exceptions;

use Exception;

class EmployeeSyncException extends Exception
{
    public function __construct(string $message = 'Employee sync failed', int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
