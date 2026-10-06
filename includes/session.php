<?php
/**
 * Démarre la session PHP (utilisée pour garder l'utilisateur connecté).
 *
 * "Lax" convient ici car, en développement, React et l'API sont vus comme
 * une seule origine grâce au proxy Vite (voir vite.config.js) : le cookie de
 * session n'a donc pas besoin d'être envoyé entre deux domaines différents.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'samesite' => 'Lax',
    ]);
    session_start();
}
