<?php
/**
 * CORS (Cross-Origin Resource Sharing).
 *
 * En développement, le frontend React (port 5173) passe par le proxy Vite
 * (voir vite.config.js), donc le navigateur ne voit qu'une seule origine et
 * ces en-têtes ne sont pas strictement nécessaires. On les ajoute quand même,
 * pour que l'API reste utilisable si vous l'appelez directement (Postman,
 * ou un appel fetch sans passer par le proxy).
 */
$allowedOrigin = 'http://localhost:5173';

header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Credentials: true'); // nécessaire pour transmettre le cookie de session
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Le navigateur envoie une requête OPTIONS ("preflight") avant certains
// appels. On y répond tout de suite, sans exécuter le reste du script.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
