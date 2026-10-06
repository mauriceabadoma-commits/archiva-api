<?php

namespace App\Support;

/**
 * Fonctions de validation « pures » : elles ne font que calculer un résultat
 * à partir de leurs arguments, sans toucher à la base de données, à la
 * session ni au réseau. C'est exactement le genre de code le plus facile
 * (et le plus rentable) à couvrir par des tests unitaires.
 */
class Validator
{
    public static function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isValidPassword(string $password, int $minLength = 6): bool
    {
        return strlen($password) >= $minLength;
    }
}
