<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/ImageController.php';

header('Content-Type: application/json');

$controller = new ImageController();

try {
    $response = $controller->upload($_POST, $_FILES);
} catch (Throwable $e) {
    // Basic fallback in case BaseController-level handling fails for some reason
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected error: ' . $e->getMessage(),
        'code'    => 500,
    ]);
    exit;
}

http_response_code($response['code'] ?? 200);
echo json_encode($response);
