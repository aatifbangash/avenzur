<?php
/**
 * Cost Center Helper Functions
 * 
 * Provides formatting and calculation functions for cost center views
 */

if (!function_exists('format_currency')) {
    /**
     * Format amount as currency (SAR)
     */
    function format_currency($amount, $decimals = 2) {
        return number_format($amount, $decimals, '.', ',') . ' SAR';
    }
}

if (!function_exists('format_percentage')) {
    /**
     * Format percentage with symbol
     */
    function format_percentage($percentage, $decimals = 1) {
        return number_format($percentage, $decimals) . '%';
    }
}

if (!function_exists('get_margin_status')) {
    /**
     * Get status badge for profit margin
     * Returns: 'success' (>=35%), 'warning' (25-34%), 'danger' (<25%)
     */
    function get_margin_status($margin) {
        if ($margin >= 35) {
            return ['status' => 'success', 'text' => '✓ Healthy', 'class' => 'text-success'];
        } elseif ($margin >= 25) {
            return ['status' => 'warning', 'text' => '⚠ Monitor', 'class' => 'text-warning'];
        } else {
            return ['status' => 'danger', 'text' => '✗ Low', 'class' => 'text-danger'];
        }
    }
}

if (!function_exists('get_color_by_margin')) {
    /**
     * Get HTML color code by profit margin percentage
     */
    function get_color_by_margin($margin) {
        if ($margin >= 35) {
            return '#51CF66'; // Green
        } elseif ($margin >= 25) {
            return '#FFD93D'; // Yellow
        } else {
            return '#FF6B6B'; // Red
        }
    }
}

if (!function_exists('calculate_margin')) {
    /**
     * Calculate profit margin percentage
     */
    function calculate_margin($revenue, $cost) {
        if ($revenue == 0) return 0;
        return ($revenue - $cost) / $revenue * 100;
    }
}

if (!function_exists('calculate_cost_ratio')) {
    /**
     * Calculate cost ratio percentage
     */
    function calculate_cost_ratio($cost, $revenue) {
        if ($revenue == 0) return 0;
        return $cost / $revenue * 100;
    }
}

if (!function_exists('format_period')) {
    /**
     * Format period (YYYY-MM) to readable format
     */
    function format_period($period) {
        return date('M Y', strtotime($period . '-01'));
    }
}

if (!function_exists('get_chart_colors')) {
    /**
     * Get color palette for charts
     */
    function get_chart_colors() {
        return [
            'primary' => '#1E90FF',
            'danger' => '#FF6B6B',
            'success' => '#51CF66',
            'warning' => '#FFD93D',
            'info' => '#4ECDC4',
            'secondary' => '#95E1D3'
        ];
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Truncate text to specified length
     */
    function truncate_text($text, $length = 50) {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . '...';
        }
        return $text;
    }
}
