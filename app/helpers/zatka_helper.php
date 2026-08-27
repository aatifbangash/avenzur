<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Zatka Invoice Helper Functions
 * 
 * Helper functions for Zatka-compliant invoice generation
 * including formatting, calculations, and QR code generation
 */

if (!function_exists('format_zatka_currency')) {
    /**
     * Format currency for Zatka invoices (SAR with 2 decimals)
     * 
     * @param float $value The value to format
     * @return string Formatted currency string
     */
    function format_zatka_currency($value)
    {
        return number_format((float)$value, 2, '.', '');
    }
}

if (!function_exists('format_zatka_date')) {
    /**
     * Format date to Saudi format (d/m/Y H:i)
     * 
     * @param string $date Date string
     * @return string Formatted date
     */
    function format_zatka_date($date)
    {
        return date('Y/m/d h:i A', strtotime($date));
    }
}

if (!function_exists('format_hijri_date')) {
    /**
     * Convert Gregorian date to Hijri calendar
     * 
     * @param string $gregorian_date Gregorian date
     * @return string Hijri date
     */
    function format_hijri_date($gregorian_date)
    {
        // Simple approximation - for production use proper Hijri library
        $timestamp = strtotime($gregorian_date);
        $hijri_year = (int)((date('Y', $timestamp) - 622) * 1.030684);
        $hijri_month = (int)(date('m', $timestamp) * 1.030684);
        $hijri_day = date('d', $timestamp);
        
        return sprintf('%04d/%02d/%02d', $hijri_year, $hijri_month, $hijri_day);
    }
}

if (!function_exists('calculate_zatka_discount')) {
    /**
     * Calculate cascading discounts (Discount 1 then Discount 2)
     * 
     * @param float $original Original amount
     * @param float $discount_1_pct Discount 1 percentage
     * @param float $discount_2_pct Discount 2 percentage
     * @return array Array with discount amounts and net
     */
    function calculate_zatka_discount($original, $discount_1_pct, $discount_2_pct)
    {
        $discount_1_amount = $original * ($discount_1_pct / 100);
        $after_discount_1 = $original - $discount_1_amount;
        $discount_2_amount = $after_discount_1 * ($discount_2_pct / 100);
        $total_discount = $discount_1_amount + $discount_2_amount;
        $net = $original - $total_discount;
        
        return [
            'discount_1_amount' => $discount_1_amount,
            'discount_2_amount' => $discount_2_amount,
            'total_discount' => $total_discount,
            'net_after_discount' => $net
        ];
    }
}

if (!function_exists('calculate_zatka_tax')) {
    /**
     * Calculate tax amount
     * 
     * @param float $amount Amount before tax
     * @param float $tax_rate Tax rate percentage
     * @return float Tax amount
     */
    function calculate_zatka_tax($amount, $tax_rate)
    {
        return $amount * ($tax_rate / 100);
    }
}

if (!function_exists('validate_saudi_tax_id')) {
    /**
     * Validate Saudi Arabia tax ID format (15 digits)
     * 
     * @param string $tax_id Tax ID to validate
     * @return bool True if valid
     */
    function validate_saudi_tax_id($tax_id)
    {
        // Remove any spaces or dashes
        $tax_id = preg_replace('/[^0-9]/', '', $tax_id);
        
        // Saudi tax ID must be exactly 15 digits
        return strlen($tax_id) === 15 && ctype_digit($tax_id);
    }
}

if (!function_exists('generate_zatka_qr_code')) {
    /**
     * Generate Zatka-compliant QR code data using TLV (Tag-Length-Value) format
     * 
     * According to ZATCA specifications, the QR code must contain:
     * Tag 1: Seller name (UTF-8)
     * Tag 2: VAT registration number
     * Tag 3: Timestamp (ISO 8601 format)
     * Tag 4: Invoice total (including VAT)
     * Tag 5: VAT amount
     * 
     * @param array $data Invoice data array
     * @return string Base64 encoded QR code data
     */
    function generate_zatka_qr_code($data)
    {
        // TLV encoding function
        $encode_tlv = function($tag, $value) {
            $tag_hex = chr($tag);
            $length_hex = chr(strlen($value));
            return $tag_hex . $length_hex . $value;
        };
        
        // Build TLV structure
        $tlv_data = '';
        
        // Tag 1: Seller Name
        $tlv_data .= $encode_tlv(1, $data['seller_name']);
        
        // Tag 2: VAT Registration Number
        $tlv_data .= $encode_tlv(2, $data['vat_no']);
        
        // Tag 3: Timestamp (ISO 8601 format)
        $timestamp = date('Y-m-d\TH:i:s\Z', strtotime($data['date']));
        $tlv_data .= $encode_tlv(3, $timestamp);
        
        // Tag 4: Invoice Total (including VAT)
        $tlv_data .= $encode_tlv(4, number_format($data['grand_total'], 2, '.', ''));
        
        // Tag 5: VAT Amount
        $tlv_data .= $encode_tlv(5, number_format($data['total_tax'], 2, '.', ''));
        
        // Base64 encode the TLV data
        return base64_encode($tlv_data);
    }
}

if (!function_exists('format_zatka_phone')) {
    /**
     * Format phone number for display
     * 
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    function format_zatka_phone($phone)
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If starts with 966, add +
        if (substr($phone, 0, 3) === '966' && substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
}

if (!function_exists('generate_zatka_invoice_notes')) {
    /**
     * Generate invoice notes in Arabic (amount in words)
     * 
     * @param float $amount Amount to convert
     * @return string Amount in Arabic words
     */
    function generate_zatka_invoice_notes($amount)
    {
        // This is a simplified version - for production use proper Arabic number-to-words library
        $riyal = floor($amount);
        $halalas = round(($amount - $riyal) * 100);
        
        // Basic conversion (would need proper Arabic number conversion)
        $note = "فقط " . number_format($riyal, 0, '', '') . " ريال سعودي";
        
        if ($halalas > 0) {
            $note .= " و" . $halalas . " هللة";
        }
        
        return $note;
    }
}
