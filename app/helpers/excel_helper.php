<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('create_excel')) {
    function create_excel($excel, $filename)
    {
        // Discard any output (notices, warnings, whitespace) that accumulated
        // before this point — such content corrupts the binary Excel stream.
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
