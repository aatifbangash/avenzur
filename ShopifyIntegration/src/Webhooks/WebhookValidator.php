<?php

namespace App\Webhooks;

class WebhookValidator
{
    /**
     * Verify Shopify webhook signature
     *
     * @param string $payload Raw POST body
     * @param string $hmac HMAC signature from header
     * @return bool
     */
    public function verify($payload, $hmac)
    {
        // Get secret from environment
        $secret = $_ENV['SHOPIFY_WEBHOOK_SECRET'] ?? getenv('SHOPIFY_WEBHOOK_SECRET') ?? '';

        if (empty($secret)) {
            error_log('Webhook validation skipped: No SHOPIFY_WEBHOOK_SECRET configured');
            return true; // Allow in development mode
        }

        // Calculate expected HMAC
        $calculated_hmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        // Compare signatures
        return hash_equals($calculated_hmac, $hmac);
    }
}

