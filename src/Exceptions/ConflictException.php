<?php

namespace App\Exceptions;

/** La ressource existe déjà (409) — ex. email déjà utilisé. */
class ConflictException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 409);
    }
}
