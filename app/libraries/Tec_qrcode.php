<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 *  ==============================================================================
 *  Author  : Mian Saleem
 *  Email   : saleem@tecdiary.com
 *  For     : PHP QR Code
 *  Web     : http://phpqrcode.sourceforge.net
 *  License : open source (LGPL)
 *  ==============================================================================
 */

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Tec_qrcode
{
	public function __construct() { require_once 'vendor/autoload.php'; }

    public function generate($params = [])
    {
        return (new QRCode(new QROptions(['imageBase64' => false, 'outputType' => QRCode::OUTPUT_MARKUP_SVG])))->render($params['data']);
    }

    public function generate_new($params = [])
    {
        $data = $params['data'] ?? '';
        $asPng = $params['png'] ?? false; // 👈 allow PNG option

        if ($asPng) {
            // Generate PNG instead of SVG
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'imageBase64' => false,
            ]);
            return (new QRCode($options))->render($data);
        } else {
            // Default SVG (original behavior)
            $options = new QROptions([
                'imageBase64' => false,
                'outputType'  => QRCode::OUTPUT_MARKUP_SVG,
            ]);
            return (new QRCode($options))->render($data);
        }
    }
}
