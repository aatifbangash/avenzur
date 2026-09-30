<?php

namespace App\Services;

class OrderEntityExtractor
{
    public function extractAll($payload)
    {
        return [
            'order' => $this->extractOrder($payload),
            'customer' => $this->extractCustomer($payload),
            'addresses' => $this->extractAddresses($payload),
            'items' => $this->extractOrderItems($payload)
        ];
    }

    public function extractOrder($payload)
    {
        return [
            'id' => $payload['id'] ?? null,
            'order_number' => $payload['order_number'] ?? null,
            'name' => $payload['name'] ?? null,
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'total_price' => $payload['total_price'] ?? null,
            'subtotal_price' => $payload['subtotal_price'] ?? null,
            'total_tax' => $payload['total_tax'] ?? null,
            'total_discounts' => $payload['total_discounts'] ?? null,
            'currency' => $payload['currency'] ?? null,
            'financial_status' => $payload['financial_status'] ?? null,
            'fulfillment_status' => $payload['fulfillment_status'] ?? null,
            'created_at' => $payload['created_at'] ?? null,
            'updated_at' => $payload['updated_at'] ?? null,
            'tags' => $payload['tags'] ?? null,
            'test' => $payload['test'] ?? false
        ];
    }

    public function extractCustomer($payload)
    {
        if (!isset($payload['customer'])) {
            return null;
        }

        $customer = $payload['customer'];
        
        return [
            'id' => $customer['id'] ?? null,
            'first_name' => $customer['first_name'] ?? null,
            'last_name' => $customer['last_name'] ?? null,
            'email' => $customer['email'] ?? null,
            'phone' => $customer['phone'] ?? null,
            'verified_email' => $customer['verified_email'] ?? false,
            'tax_exempt' => $customer['tax_exempt'] ?? false,
            'default_address' => $customer['default_address'] ?? null
        ];
    }

    public function extractAddresses($payload)
    {
        return [
            'billing' => $payload['billing_address'] ?? null,
            'shipping' => $payload['shipping_address'] ?? null
        ];
    }

    public function extractOrderItems($payload)
    {
        $items = [
            'line_items' => [],
            'shipping_lines' => []
        ];

        // Extract line items
        if (isset($payload['line_items']) && is_array($payload['line_items'])) {
            foreach ($payload['line_items'] as $item) {
                $items['line_items'][] = [
                    'id' => $item['id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'title' => $item['title'] ?? null,
                    'name' => $item['name'] ?? null,
                    'sku' => $item['sku'] ?? null,
                    'price' => $item['price'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'total_discount' => $item['total_discount'] ?? null,
                    'grams' => $item['grams'] ?? null,
                    'requires_shipping' => $item['requires_shipping'] ?? false
                ];
            }
        }

        // Extract shipping lines
        if (isset($payload['shipping_lines']) && is_array($payload['shipping_lines'])) {
            foreach ($payload['shipping_lines'] as $shipping) {
                $items['shipping_lines'][] = [
                    'id' => $shipping['id'] ?? null,
                    'title' => $shipping['title'] ?? null,
                    'price' => $shipping['price'] ?? null,
                    'code' => $shipping['code'] ?? null,
                    'source' => $shipping['source'] ?? null
                ];
            }
        }

        return $items;
    }
}