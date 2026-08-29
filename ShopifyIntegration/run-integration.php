<?php
/**
 * Shopify Integration - Manual Order Processing
 * 
 * This script receives order JSON data and processes it.
 * You can call this from anywhere by sending a POST request with order JSON.
 * 
 * Usage: POST to https://yourdomain.com/ShopifyIntegration/runShopifyIntegration.php
 * Content-Type: application/json
 * Body: Shopify order JSON
 */

// Load the application
require_once __DIR__ . '/autoload.php';

use App\Services\OrderEntityExtractor;
use App\Services\DatabaseService;

// Set JSON content type for response
header('Content-Type: application/json');

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/webhook-errors.log');

/**
 * Log activity
 */
function logActivity($message, $level = 'INFO') {
    $logFile = __DIR__ . '/storage/logs/orders-' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse([
            'success' => false,
            'error' => 'Only POST requests are allowed'
        ], 405);
    }
    
    // Get the raw POST data
    $payload = file_get_contents('php://input');
    
    if (empty($payload)) {
        logActivity('Empty payload received', 'ERROR');
        sendResponse([
            'success' => false,
            'error' => 'Empty payload'
        ], 400);
    }
    
    // Log the incoming request
    logActivity("Order processing request received");
    
    // Parse JSON payload
    $orderData = json_decode($payload, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logActivity("JSON parsing failed: " . json_last_error_msg(), 'ERROR');
        sendResponse([
            'success' => false,
            'error' => 'Invalid JSON: ' . json_last_error_msg()
        ], 400);
    }
    
    // Check if order already exists
    $db = new DatabaseService();
    $orderId = $orderData['id'] ?? null;
    
    if ($orderId && $db->orderExists($orderId)) {
        logActivity("Order already exists: $orderId", 'WARNING');
        sendResponse([
            'success' => false,
            'error' => 'Order already exists',
            'order_id' => $orderId
        ], 409);
    }
    
    // Extract entities from order data
    $extractor = new OrderEntityExtractor();
    $entities = $extractor->extractAll($orderData);
    
    logActivity("Entities extracted for order: " . ($orderData['name'] ?? 'unknown'));
    
    // Save to database
    $result = $db->saveCompleteOrder($entities);
    
    // Log success
    $orderName = $orderData['name'] ?? 'unknown';
    logActivity("Order processed successfully: $orderName (Sale ID: {$result['sale_id']})", 'SUCCESS');
    
    // Send success response
    sendResponse([
        'success' => true,
        'message' => 'Order processed successfully',
        'order_name' => $orderName,
        'order_id' => $orderId,
        'result' => [
            'sale_id' => $result['sale_id'],
            'customer_id' => $result['customer_id'],
            'billing_address_id' => $result['billing_address_id'],
            'shipping_address_id' => $result['shipping_address_id']
        ]
    ], 200);
    
} catch (Exception $e) {
    // Log the error
    logActivity("Error processing order: " . $e->getMessage(), 'ERROR');
    logActivity("Stack trace: " . $e->getTraceAsString(), 'ERROR');
    
    // Send error response
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 500);
}