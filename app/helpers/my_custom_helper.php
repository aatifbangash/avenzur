<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Add admin_form_open
if (!function_exists('dd')) {
    function dd($data)
    {
        echo '<Pre>';print_r($data);exit;
    }
}
