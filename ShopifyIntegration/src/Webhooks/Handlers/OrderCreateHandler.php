<?php

namespace App\Webhooks\Handlers;

class OrderCreateHandler
{
    /**
     * Handle orders/create webhook
     * Placeholder for order creation logic
     */
    public function handle($data)
    {
        error_log("Order webhook received: " . json_encode(['id' => $data['id']]));

        return [
            'status' => 'success',
            'message' => 'Order webhook received (not implemented yet)',
            'order_id' => $data['id'] ?? null
        ];
    }
}

