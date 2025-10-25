<?php

namespace App\Exception;

use Throwable;

class ValidationException extends \Exception
{
    public function __construct(
        private readonly array $errors = [],
        private readonly array $inputs = [],
        string                 $message = "Validation exception",
        int                    $code = 422, ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }
}
