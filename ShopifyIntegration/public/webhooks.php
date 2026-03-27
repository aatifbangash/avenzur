<?php
require_once __DIR__ . '/../autoload.php';

use App\Webhooks\WebhookValidator;
use App\Webhooks\WebhookHandler;

// Get webhook data
$payload = file_get_contents('php://input');
$hmac = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
$topic = $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? '';
$shopDomain = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '';

// Log the incoming webhook
error_log("Webhook received: Topic=$topic, Shop=$shopDomain");

// Validate webhook
$validator = new WebhookValidator();
if (!$validator->verify($payload, $hmac)) {
    http_response_code(401);
    error_log("Webhook validation failed");
    exit('Unauthorized');
}

// Parse payload
$data = json_decode($payload, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    error_log("Invalid JSON payload");
    exit('Invalid JSON');
}

// Route to appropriate handler
try {
    $handler = new WebhookHandler();
    $result = $handler->handle($topic, $data);
    
    http_response_code(200);
    echo json_encode(['success' => true, 'result' => $result]);
    
} catch (Exception $e) {
    error_log("Webhook handler error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}