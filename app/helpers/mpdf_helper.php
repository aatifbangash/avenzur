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
        // mPDF refuses WriteHTML() input longer than pcre.backtrack_limit (PHP default 1,000,000),
        // which long statements exceed. Raise it for this request only; never lower it.
        $limit = 50000000;
        if ((int) ini_get('pcre.backtrack_limit') < $limit) {
            ini_set('pcre.backtrack_limit', (string) $limit);
        }

        return array_merge(['tempDir' => mpdf_temp_dir()], $config);
    }
}
