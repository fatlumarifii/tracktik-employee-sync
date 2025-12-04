<?php

namespace App\Exceptions;

use Exception;

class TrackTikApiException extends Exception
{
    public function __construct(string $message = 'TrackTik API error occurred', int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
