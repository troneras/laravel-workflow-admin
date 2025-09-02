<?php

namespace Casino\LaravelAiOrchestrator\Exceptions;

use Exception;

class ExecutionException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}