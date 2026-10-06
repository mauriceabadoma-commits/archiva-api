<?php

namespace App\Exceptions;

/** La ressource demandée n'existe pas (404). */
class NotFoundException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 404);
    }
}
