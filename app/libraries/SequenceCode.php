<?php

/**
 * Class will generats a new Sequence code by appling the passing prefix based on that prefix it will target the respective DB table and
 * retrives the latest stoered sequece code and will generates the new one and return
 */

/* Database Queries */
# ALTER TABLE `sma_companies` ADD `code` VARCHAR(255) NULL DEFAULT NULL;
# ALTER TABLE `sma_companies` CHANGE `code` `sequence_code` VARCHAR(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL;
# ALTER TABLE `sma_products` ADD `sequence_code` VARCHAR(255) NULL DEFAULT NULL;
# ALTER TABLE `sma_purchases` ADD `sequence_code` VARCHAR(255) NULL DEFAULT NULL;
# ALTER TABLE `sma_sales` ADD `sequence_code` VARCHAR(255) NULL AFTER `sale_invoice`;


//=== USAGE ===//
/**
 * Load Library inside the Controller Constructor e.g.
 * 
 * $this->load->library('SequenceCode');
 * $this->sequenceCode = new SequenceCode();
 * 
 * Call the function 
 * 
 * $this->sequenceCode->generate('CUS', 5);
 * 
 */

class SequenceCode
{
    public function __construct()
    {
        $this->_ci = &get_instance();
    }

    /**
     * Initializer
     */
    function SequenceCode()
    {
        return;
    }

    /**
     * $prefix = CUS, SUP, ITM, PRD, TAX etc
     * $sizeOfNumber is the Sequence code length, the algo will add the extra as zeros.
     * @return $newSequenceCode based on the $prefix
     */

    public function generate($prefix, $sizeOfNumber = 5)
    {
        $newSequenceCode = 00000;

        if (empty($prefix))
            return $newSequenceCode;

        $prefix = strtoupper(trim($prefix));

        switch ($prefix) {

                // Supplier Code
            case 'SUP':
                $this->_ci->db->select('MAX(sequence_code) as maxNumber');
                $this->_ci->db->where('group_name', 'supplier');
                $latestCode = $this->_ci->db->get('sma_companies')->row_array();
                break;

                // Customer Code
            case 'CUS':
                $this->_ci->db->select('MAX(sequence_code) as maxNumber');
                $this->_ci->db->where('group_name', 'customer');
                $latestCode = $this->_ci->db->get('sma_companies')->row_array();
                break;

                // Product Code
            case 'PRD':
                $this->_ci->db->select('MAX(sequence_code) as maxNumber');
                $latestCode = $this->_ci->db->get('sma_products')->row_array();
                break;

                // Purchases Code
            case 'PR':
                $this->_ci->db->select('MAX(sequence_code) as maxNumber');
                $latestCode = $this->_ci->db->get('sma_purchases')->row_array();
                break;

                // Sales Code
            case 'SL':
                $this->_ci->db->select('MAX(sequence_code) as maxNumber');
                $latestCode = $this->_ci->db->get('sma_sales')->row_array();
                break;

                // Transfer Code
            case 'TR':
                $this->_ci->db->select('MAX(sequence_code) as maxNumber');
                $latestCode = $this->_ci->db->get('sma_transfers')->row_array();
                break;

                // Department Code
            case 'DP':
                $this->_ci->db->select('MAX(code) as maxNumber');
                $latestCode = $this->_ci->db->get('sma_departments')->row_array();
                break;

                // Employee Code
            case 'EM':
                $this->_ci->db->select('MAX(code) as maxNumber');
                $latestCode = $this->_ci->db->get('sma_employees')->row_array();
                break;
        }

        if ($latestCode) {
            // Extract the numeric portion of the maximum code
            $maxNumber = intval(substr($latestCode['maxNumber'], strlen($prefix) + 1));

            // Generate the new sequenced code by incrementing the maximum number by 1 and padding it with zeros
            $newNumber = $maxNumber + 1;

            $paddedNumber = str_pad($newNumber, $sizeOfNumber, '0', STR_PAD_LEFT);

            $newSequenceCode = $prefix . '-' . $paddedNumber;
        }
        return $newSequenceCode;
    }
}
