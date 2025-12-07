<?php

namespace App\Services\Database;

use PDO;

class AddressRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Save address to sma_addresses table
     */
    public function saveAddress($address, $companyId)
    {
        if (!$address) {
            return null;
        }

        $sql = "INSERT INTO sma_addresses (
                    company_id,
                    line1,
                    line2,
                    city,
                    postal_code,
                    state,
                    country,
                    phone,
                    first_name,
                    last_name,
                    is_default,
                    mobile_verified,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $companyId,
            $address['address1'] ?? '',
            $address['address2'] ?? null,
            $address['city'] ?? '',
            $address['zip'] ?? null,
            $address['province'] ?? '',
            $address['country'] ?? '',
            $address['phone'] ?? null,
            $address['first_name'] ?? null,
            $address['last_name'] ?? null
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Get address by ID
     */
    public function getAddressById($addressId)
    {
        $sql = "SELECT * FROM sma_addresses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$addressId]);
        
        return $stmt->fetch();
    }

    /**
     * Get all addresses for a company/customer
     */
    public function getAddressesByCompanyId($companyId)
    {
        $sql = "SELECT * FROM sma_addresses WHERE company_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$companyId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Update address
     */
    public function updateAddress($addressId, $address)
    {
        $sql = "UPDATE sma_addresses SET
                    line1 = ?,
                    line2 = ?,
                    city = ?,
                    postal_code = ?,
                    state = ?,
                    country = ?,
                    phone = ?,
                    first_name = ?,
                    last_name = ?,
                    updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $address['address1'] ?? '',
            $address['address2'] ?? null,
            $address['city'] ?? '',
            $address['zip'] ?? null,
            $address['province'] ?? '',
            $address['country'] ?? '',
            $address['phone'] ?? null,
            $address['first_name'] ?? null,
            $address['last_name'] ?? null,
            $addressId
        ]);

        return $stmt->rowCount();
    }
}