<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('mpdf_temp_dir')) {
    function mpdf_temp_dir()
    {
        $dir = FCPATH . 'assets/uploads/mpdf_tmp';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        return $dir;
    }
}

if (!function_exists('mpdf_config')) {
    function mpdf_config(array $config = [])
    {
        return array_merge(['tempDir' => mpdf_temp_dir()], $config);
    }
}
