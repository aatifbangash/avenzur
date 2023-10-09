<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
 *  ==============================================================================
 *  Author  : Mian Saleem
 *  Email   : saleem@tecdiary.com
 *  For     : Twilio SMS APIs
 *  ==============================================================================
 */

use Twilio\Rest\Client;

class Tec_twilio
{
    public function __construct($config)
    {
        //$sid          = $config['sid'];
        //$token        = $config['token'];
        print_r($config);exit;
        $sid = $this->config[$this->config['gateway']]['account_sid'];
        $token = $this->config[$this->config['gateway']]['auth_token'];
        $this->client = new Client($sid, $token);
    }

    public function send($to, $from, $body)
    {
        $this->client->messages->create($to, ['from' => $from, 'body' => $body]);
    }
}
