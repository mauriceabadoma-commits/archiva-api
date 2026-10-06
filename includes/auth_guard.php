<?php
require_once __DIR__ . '/response.php';

/**
 * Bloque la requête avec une erreur 401 si personne n'est connecté.
 * Sinon, retourne les informations de l'utilisateur courant (depuis la session).
 */
function require_login(): array
{
    if (empty($_SESSION['user_id'])) {
        json_error('Vous devez être connecté pour effectuer cette action.', 401);
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
    ];
}
