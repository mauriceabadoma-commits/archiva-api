<?php
require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/response.php';

const COOKIE_NAME = 'archiva_lang';
const ALLOWED_LANGUAGES = ['fr', 'en'];
const ONE_YEAR = 60 * 60 * 24 * 365;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Si aucun cookie n'existe encore, le français est la langue par défaut.
    $lang = $_COOKIE[COOKIE_NAME] ?? 'fr';
    json_response(['language' => $lang]);
}

if ($method === 'POST') {
    $body = get_json_body();
    $lang = trim($body['language'] ?? '');

    if (!in_array($lang, ALLOWED_LANGUAGES, true)) {
        json_error('Langue non supportée. Utilisez "fr" ou "en".', 422);
    }

    // Un cookie (pas une session) : la préférence doit survivre à la fermeture
    // du navigateur, contrairement à la connexion qui, elle, est temporaire.
    setcookie(COOKIE_NAME, $lang, [
        'expires' => time() + ONE_YEAR,
        'path' => '/',
        'samesite' => 'Lax',
    ]);

    json_response(['language' => $lang]);
}

json_error('Méthode non supportée.', 405);
