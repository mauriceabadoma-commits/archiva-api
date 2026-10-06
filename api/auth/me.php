<?php
require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/response.php';

if (empty($_SESSION['user_id'])) {
    json_response(['authenticated' => false]);
}

json_response([
    'authenticated' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
    ],
]);
