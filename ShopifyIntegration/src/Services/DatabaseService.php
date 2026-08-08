<?php

namespace App\Services;

use PDO;
use PDOException;
use Exception;
use App\Config\Config;
use App\Services\Database\CustomerRepository;
use App\Services\Database\AddressRepository;
use App\Services\Database\SaleRepository;
use App\Services\Database\SaleItemRepository;

class DatabaseService
{
    private $pdo;
    private $customerRepo;
    private $addressRepo;
    private $saleRepo;
    private $saleItemRepo;

    public function __construct()
    {
        $this->pdo = $this->createConnection();
        $this->initializeRepositories();
    }

    private function createConnection()
    {
        try {
            $config = Config::getInstance();
            
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=utf8mb4",
                $config->get('DB_HOST', 'localhost'),
                $config->get('DB_DATABASE', 'avnzor')
            );

            return new PDO(
                $dsn,
                $config->get('DB_USERNAME', 'root'),
                $config->get('DB_PASSWORD', ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function initializeRepositories()
    {
        $this->customerRepo = new CustomerRepository($this->pdo);
        $this->addressRepo = new AddressRepository($this->pdo);
        $this->saleRepo = new SaleRepository($this->pdo);
        $this->saleItemRepo = new SaleItemRepository($this->pdo);
    }

    public function saveCompleteOrder($entities)
    {
        try {
            $this->pdo->beginTransaction();

            $customerId = $this->customerRepo->saveCustomer($entities['customer']);

            $billingAddressId = null;
            $shippingAddressId = null;
            
            if ($entities['addresses']['billing']) {
                $billingAddressId = $this->addressRepo->saveAddress(
                    $entities['addresses']['billing'], 
                    $customerId
                );
            }
            
            if ($entities['addresses']['shipping']) {
                $shippingAddressId = $this->addressRepo->saveAddress(
                    $entities['addresses']['shipping'], 
                    $customerId
                );
            }

            $saleId = $this->saleRepo->saveSale(
                $entities['order'], 
                $customerId, 
                $shippingAddressId
            );

            foreach ($entities['items']['line_items'] as $item) {
                $this->saleItemRepo->saveSaleItem($item, $saleId);
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'sale_id' => $saleId,
                'customer_id' => $customerId,
                'billing_address_id' => $billingAddressId,
                'shipping_address_id' => $shippingAddressId
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to save order: " . $e->getMessage());
        }
    }

    public function orderExists($shopifyOrderId)
    {
        return $this->saleRepo->orderExists($shopifyOrderId);
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}