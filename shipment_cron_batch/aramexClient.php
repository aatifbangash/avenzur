<?php
require_once 'config.php';

class AramexClient {
    private $soapClient;

    public function __construct() {
        $this->soapClient = new SoapClient(ARAMEX_WSDL, ['trace' => 1]);
    }

    /**
     * Create multiple shipments in a single batch API call
     * @param array $orders Array of order data
     * @return array Array of results with tracking numbers
     */
    public function createShipmentsBatch($orders) {
        $params = $this->buildBatchShipmentParams($orders);
        // print_r($params["Shipments"]);exit;

        try {
            $response = $this->soapClient->CreateShipments($params);
            
            echo "SOAP Request:\n" . $this->soapClient->__getLastRequest() . "\n";
            echo "SOAP Response:\n" . $this->soapClient->__getLastResponse() . "\n";
            
            $results = [];
            
            // Handle single shipment response
            if (isset($response->Shipments->ProcessedShipment->ID)) {
                $results[] = [
                    'order_id' => $orders[0]['id'],
                    'tracking_number' => $response->Shipments->ProcessedShipment->ID,
                    'success' => true,
                    'reference' => $response->Shipments->ProcessedShipment->Reference1 ?? null
                ];
            }
            // Handle multiple shipments response
            elseif (isset($response->Shipments->ProcessedShipment) && is_array($response->Shipments->ProcessedShipment)) {
                foreach ($response->Shipments->ProcessedShipment as $index => $shipment) {
                    $results[] = [
                        'order_id' => $orders[$index]['id'],
                        'tracking_number' => $shipment->ID ?? null,
                        'success' => isset($shipment->ID),
                        'reference' => $shipment->Reference1 ?? null,
                        'error' => $shipment->Notifications->Notification->Message ?? null
                    ];
                }
            }
            
            return $results;
            
        } catch (SoapFault $fault) {
            error_log("Aramex SOAP Batch Error: " . $fault->faultstring);
            
            // Return error for all orders
            $results = [];
            foreach ($orders as $order) {
                $results[] = [
                    'order_id' => $order['id'],
                    'tracking_number' => null,
                    'success' => false,
                    'error' => $fault->faultstring
                ];
            }
            return $results;
        }
    }

    /**
     * Build SOAP parameters for batch shipment creation
     */
    private function buildBatchShipmentParams($orders) {
        $shipments = [];
        
        foreach ($orders as $order) {
            $shipments[] = [
                'Shipper' => [
                    'Reference1' => 'Ref ' . $order['id'],
                    'AccountNumber' => ARAMEX_ACCOUNT_NUMBER,
                    'PartyAddress' => [
                        'Line1' => 'Al Zayed Street 2492, Al Mishael 14328',
                        'City' => 'Riyadh',
                        'CountryCode' => 'SA'
                    ],
                    'Contact' => [
                        'PersonName' => 'Warehouse Manager',
                        'CompanyName' => 'Avenzur Pharmacy',
                        'CellPhone' => '00966540369101',
                        'PhoneNumber1' => '00966540369101',
                        'EmailAddress' => 'warehouse@avenzur.com'
                    ]
                ],
                'Consignee' => [
                    'Reference1' => 'Order ' . $order['id'],
                    'PartyAddress' => [
                        'Line1' => $order['customer_address'] ?: 'Address Not Provided',
                        'City' => $order['customer_city'] ?: 'Riyadh',
                        'CountryCode' => $order['customer_country'] ?: 'SA'
                    ],
                    'Contact' => [
                        'PersonName' => $order['customer_name'] ?: 'Customer',
                        'CompanyName' => $order['customer_name'] ?: 'Individual Customer',
                        'PhoneNumber1' => $order['customer_phone'] ?: '0500000000',
                        'CellPhone' => $order['customer_phone'] ?: '0500000000',
                        'EmailAddress' => $order['customer_email'] ?: 'noreply@avenzur.com'
                    ]
                ],
                'Reference1' => 'Shpt ' . $order['id'],
                'ShippingDateTime' => time(),
                'Details' => [
                    'Dimensions' => [
                        'Length' => 10, 'Width' => 10, 'Height' => 10, 'Unit' => 'cm'
                    ],
                    'ActualWeight' => ['Value' => 0.5, 'Unit' => 'Kg'],
                    'ProductGroup' => 'EXP',
                    'ProductType' => 'PDX',
                    'PaymentType' => 'P',
                    'NumberOfPieces' => 1,
                    'DescriptionOfGoods' => $order['products'] ?: 'General Goods',
                    'GoodsOriginCountry' => 'SA',
                    'Items' => [[
                        'PackageType' => 'Box',
                        'Quantity' => 1,
                        'Weight' => ['Value' => 0.5, 'Unit' => 'Kg'],
                        'Comments' => $order['products'] ?: 'General Goods'
                    ]]
                ]
            ];
        }
        
        return [
            'Shipments' => [
                'Shipment' => count($shipments) === 1 ? $shipments[0] : $shipments
            ],
            'ClientInfo' => [
                'AccountCountryCode' => ARAMEX_ACCOUNT_COUNTRY_CODE,
                'AccountEntity' => ARAMEX_ACCOUNT_ENTITY,
                'AccountNumber' => ARAMEX_ACCOUNT_NUMBER,
                'AccountPin' => ARAMEX_ACCOUNT_PIN,
                'UserName' => ARAMEX_USERNAME,
                'Password' => ARAMEX_PASSWORD,
                'Version' => ARAMEX_VERSION
            ],
            'Transaction' => ['Reference1' => 'Batch_' . date('YmdHis')],
            'LabelInfo' => ['ReportID' => 9201, 'ReportType' => 'URL']
        ];
    }
}
