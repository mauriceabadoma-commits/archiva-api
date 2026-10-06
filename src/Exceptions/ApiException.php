<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception de base : porte toujours un code HTTP, ce qui permet au
 * contrôleur (api/cers.php, etc.) de rester très simple : il n'a qu'à
 * attraper ApiException et appeler json_error($e->getMessage(), $e->getStatusCode()).
 */
class ApiException extends Exception
{
    public function __construct(string $message, private int $statusCode)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
