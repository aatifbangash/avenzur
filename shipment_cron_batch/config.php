<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'demo');

// Aramex SOAP WSDL
define('ARAMEX_WSDL', 'shipping.wsdl');

// Aramex client credentials
define('ARAMEX_ACCOUNT_NUMBER', '71449672');
define('ARAMEX_ACCOUNT_PIN', '107806');
define('ARAMEX_USERNAME', 'agilkar@avenzur.com');
define('ARAMEX_PASSWORD', 'Adnan@1234');
define('ARAMEX_VERSION', 'v1.0');
define('ARAMEX_ACCOUNT_ENTITY', 'RUH');
define('ARAMEX_ACCOUNT_COUNTRY_CODE', 'SA');

// Cron settings - BATCH MODE
define('MAX_ORDERS_PER_RUN', 50); // Process up to 50 orders per batch
