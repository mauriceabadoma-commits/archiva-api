<?php

namespace App\Exceptions;

/** Personne n'est connecté, ou les identifiants sont faux (401). */
class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Vous devez être connecté.')
    {
        parent::__construct($message, 401);
    }
}
