<?php

use App\Config\Config;

if (!function_exists('env')) {
    /**
     * Get environment variable value
     */
    function env($key, $default = null)
    {
        return Config::getInstance()->get($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Get config instance
     */
    function config($key = null)
    {
        $config = Config::getInstance();
        
        if ($key === null) {
            return $config;
        }
        
        return $config->get($key);
    }
}