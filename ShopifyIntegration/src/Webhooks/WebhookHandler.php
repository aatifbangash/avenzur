<?php

namespace App\Webhooks;

use App\Webhooks\Handlers\ProductCreateHandler;
use App\Webhooks\Handlers\OrderCreateHandler;

class WebhookHandler
{
    private $handlers = [];

    public function __construct()
    {
        // Register webhook handlers
        $this->handlers = [
            'products/create' => ProductCreateHandler::class,
            'products/update' => ProductCreateHandler::class, // Reuse same handler
            'orders/create' => OrderCreateHandler::class,
        ];
    }

    /**
     * Route webhook to appropriate handler
     *
     * @param string $topic Webhook topic (e.g., "products/create")
     * @param array $data Webhook payload
     * @return mixed Handler result
     */
    public function handle($topic, $data)
    {
        error_log("Handling webhook topic: $topic");

        // Check if handler exists for this topic
        if (!isset($this->handlers[$topic])) {
            error_log("No handler registered for topic: $topic");
            return ['status' => 'ignored', 'message' => "No handler for topic: $topic"];
        }

        // Get handler class
        $handlerClass = $this->handlers[$topic];

        // Check if handler class exists
        if (!class_exists($handlerClass)) {
            error_log("Handler class not found: $handlerClass");
            return ['status' => 'error', 'message' => "Handler class not found"];
        }

        // Instantiate and execute handler
        $handler = new $handlerClass();

        try {
            $result = $handler->handle($data);
            error_log("Webhook handled successfully for topic: $topic");
            return $result;
        } catch (\Exception $e) {
            error_log("Webhook handler error: " . $e->getMessage());
            throw $e;
        }
    }
}

