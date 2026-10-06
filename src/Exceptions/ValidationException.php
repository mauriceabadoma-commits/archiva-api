<?php

namespace App\Exceptions;

/** Donnée manquante ou mal formée (422). */
class ValidationException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}
