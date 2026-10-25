<?php

namespace App\Services\Database;

use PDO;

class CustomerRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function saveCustomer($customer)
    {
        if (!$customer) {
            return $this->getDefaultCustomerId();
        }

        $existingId = $this->findCustomerByEmail($customer['email']);
        
        if ($existingId) {
            $this->updateCustomer($existingId, $customer);
            return $existingId;
        }

        return $this->createCustomer($customer);
    }

    private function findCustomerByEmail($email)
    {
        $sql = "SELECT id FROM sma_users WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        return $result ? $result['id'] : null;
    }

    private function updateCustomer($customerId, $customer)
    {
        $sql = "UPDATE sma_users SET 
                first_name = ?,
                last_name = ?,
                phone = ?,
                mobile_verified = ?
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $customer['first_name'],
            $customer['last_name'],
            $customer['phone'],
            $customer['verified_email'] ? 1 : 0,
            $customerId
        ]);
    }

    private function createCustomer($customer)
    {
        $sql = "INSERT INTO sma_users (
                    username,
                    email,
                    first_name,
                    last_name,
                    phone,
                    ip_address,
                    password,
                    group_id,
                    mobile_verified,
                    created_on,
                    active,
                    view_right,
                    edit_right
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, 0, 0)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $customer['email'],
            $customer['email'],
            $customer['first_name'],
            $customer['last_name'],
            $customer['phone'],
            inet_pton('127.0.0.1'),
            password_hash(uniqid(), PASSWORD_DEFAULT),
            3,
            $customer['verified_email'] ? 1 : 0
        ]);

        return $this->pdo->lastInsertId();
    }

    private function getDefaultCustomerId()
    {
        return 1;
    }

    public function getCustomerById($customerId)
    {
        $sql = "SELECT * FROM sma_users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId]);
        
        return $stmt->fetch();
    }
}