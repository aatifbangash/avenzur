<?php
/**
 * Simple Autoloader for Shopify Integration
 * Replaces Composer's vendor/autoload.php
 */

spl_autoload_register(function ($class) {
    // Convert namespace to file path
    // App\Services\DatabaseService -> src/Services/DatabaseService.php

    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';

    // Check if the class uses the App namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Load helper functions if they exist
$helpers = __DIR__ . '/src/helpers.php';
if (file_exists($helpers)) {
    require_once $helpers;
}

