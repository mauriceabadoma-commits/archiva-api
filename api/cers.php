<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth_guard.php';

use App\Services\CerService;
use App\Exceptions\ApiException;

$pdo = getPDO();
$service = new CerService($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Le contrôleur ne fait plus que : lire la requête, appeler le service,
// répondre. Toute la logique (validation, ownership...) vit dans CerService,
// où elle peut être testée sans passer par une vraie requête HTTP.
try {
    switch ($method) {
        case 'GET':
            if ($id) {
                json_response($service->find($id));
                break;
            }

            $filters = [
                'search' => trim($_GET['search'] ?? ''),
                'level' => trim($_GET['level'] ?? ''),
            ];
            if (($_GET['mine'] ?? '') === '1') {
                $user = require_login();
                $filters['user_id'] = $user['id'];
            }
            json_response($service->list($filters));
            break;

        case 'POST':
            $user = require_login();
            $newId = $service->create($user['id'], $user['name'], get_json_body());
            json_response(['id' => $newId], 201);
            break;

        case 'PUT':
            if (!$id) {
                json_error('Identifiant manquant.', 400);
            }
            $user = require_login();
            $service->update($id, $user['id'], get_json_body());
            json_response(['message' => 'CER mis à jour.']);
            break;

        case 'DELETE':
            if (!$id) {
                json_error('Identifiant manquant.', 400);
            }
            $user = require_login();
            $service->delete($id, $user['id']);
            json_response(['message' => 'CER supprimé.']);
            break;

        default:
            json_error('Méthode non supportée.', 405);
    }
} catch (ApiException $e) {
    json_error($e->getMessage(), $e->getStatusCode());
}
