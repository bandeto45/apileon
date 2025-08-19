<?php

namespace Apileon\Validation;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(string $message, array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $field => $messages) {
            if (!empty($messages)) {
                return is_array($messages) ? $messages[0] : $messages;
            }
        }
        
        return null;
    }
}
