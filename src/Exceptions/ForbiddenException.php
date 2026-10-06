<?php

namespace App\Exceptions;

/** Connecté, mais pas le droit d'agir sur cette ressource (403). */
class ForbiddenException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 403);
    }
}
