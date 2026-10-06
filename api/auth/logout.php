<?php
require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/response.php';

$_SESSION = [];
session_destroy();

json_response(['message' => 'Déconnecté.']);
