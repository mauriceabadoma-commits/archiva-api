<?php
/**
 * Envoie une réponse JSON et arrête le script.
 * Toutes les routes de l'API passent par cette fonction : un seul endroit
 * définit le format des réponses (principe DRY appliqué au backend).
 */
function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Raccourci pour une erreur, avec le même format que les succès. */
function json_error(string $message, int $status = 400): void
{
    json_response(['error' => $message], $status);
}

/** Lit et décode le corps JSON d'une requête POST/PUT. */
function get_json_body(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
