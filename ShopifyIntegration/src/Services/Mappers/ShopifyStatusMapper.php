<?php

namespace App\Services\Mappers;

class ShopifyStatusMapper
{
    /**
     * Map Shopify financial status to sale_status
     */
    public function mapSaleStatus($status)
    {
        $statusMap = [
            'paid' => 'completed',
            'pending' => 'pending',
            'authorized' => 'pending',
            'partially_paid' => 'pending',
            'refunded' => 'returned',
            'voided' => 'cancelled',
            'partially_refunded' => 'completed'
        ];

        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Map Shopify financial status to payment_status
     */
    public function mapPaymentStatus($status)
    {
        $statusMap = [
            'paid' => 'paid',
            'pending' => 'pending',
            'authorized' => 'pending',
            'partially_paid' => 'partial',
            'refunded' => 'paid',
            'voided' => 'due',
            'partially_refunded' => 'partial'
        ];

        return $statusMap[$status] ?? 'due';
    }

    /**
     * Reverse map: Get Shopify status from local status
     */
    public function reverseMapSaleStatus($localStatus)
    {
        $reverseMap = [
            'completed' => 'paid',
            'pending' => 'pending',
            'returned' => 'refunded',
            'cancelled' => 'voided'
        ];

        return $reverseMap[$localStatus] ?? null;
    }
}