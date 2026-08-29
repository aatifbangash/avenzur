<?php
require_once 'config.php';

class AramexClient {
    private $soapClient;

    public function __construct() {
        $this->soapClient = new SoapClient(ARAMEX_WSDL, ['trace' => 1]);
    }

    public function createShipment($order) {
        $params = $this->buildShipmentParams($order);
        // print_r($params["Shipments"]);exit;

        try {
            $response = $this->soapClient->CreateShipments($params);
            echo "SOAP Request:\n" . $this->soapClient->__getLastRequest() . "\n";
            echo "SOAP Response:\n" . $this->soapClient->__getLastResponse() . "\n";
            // print_r($response);exit;
            if (isset($response->Shipments->ProcessedShipment->ID)) {
                return $response->Shipments->ProcessedShipment->ID; // Tracking number
            }
            return false;
        } catch (SoapFault $fault) {
            error_log("Aramex SOAP Error (Order {$order['id']}): " . $fault->faultstring);
            return false;
        }
    }

    private function buildShipmentParams($order) {
        return [
            'Shipments' => [
                'Shipment' => [
                    'Shipper' => [
                        'Reference1' => 'Ref ' . $order['id'],
                        'AccountNumber' => ARAMEX_ACCOUNT_NUMBER,
                        'PartyAddress' => [
                            'Line1' => 'Al Zayed Street 2492, Al Mishael 14328, Riyadh, Saudi Arabia',
                            'City' => 'Riyadh',
                            'CountryCode' => 'SA'
                        ],
                        'Contact' => [
                            'PersonName' => 'Warehouse Manager',
                            'CompanyName' => 'Avenzur Pharmacy',
                            'CellPhone' => '00966540369101',
                            'PhoneNumber1' => '00966540369101',
                            'EmailAddress' => 'warehouse@company.com'
                        ]
                    ],
                    'Consignee' => [
                        'Reference1' => 'Order ' . $order['id'],
                        'PartyAddress' => [
                            'Line1' => $order['customer_address'],
                            'City' => $order['customer_city'],
                            'CountryCode' => !empty($order['customer_country']) ? $order['customer_country'] : 'SA'
                        ],
                        'Contact' => [
                            'PersonName' => $order['customer_name'],
                            'CompanyName' =>$order['customer_name'],
                            'PhoneNumber1' => $order['customer_phone'],
                            'CellPhone' => $order['customer_phone'],
                            'EmailAddress' => $order['customer_email']
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
                        'DescriptionOfGoods' => $order['products'],
                        'GoodsOriginCountry' => 'SA',
                        'Items' => [[
                            'PackageType' => 'Box',
                            'Quantity' => 1,
                            'Weight' => ['Value' => 0.5, 'Unit' => 'Kg'],
                            'Comments' => $order['products']
                        ]]
                    ]
                ]
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
            'Transaction' => ['Reference1' => $order['id']],
            'LabelInfo' => ['ReportID' => 9201, 'ReportType' => 'URL']
        ];
    }
}
