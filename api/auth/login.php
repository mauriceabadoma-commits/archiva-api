<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/response.php';

use App\Services\AuthService;
use App\Exceptions\ApiException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Méthode non supportée.', 405);
}

$body = get_json_body();
$service = new AuthService(getPDO());

try {
    $user = $service->login($body['email'] ?? '', (string) ($body['password'] ?? ''));

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    json_response($user);
} catch (ApiException $e) {
    json_error($e->getMessage(), $e->getStatusCode());
}
