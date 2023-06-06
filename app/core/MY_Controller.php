<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public $mDateArray = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->db->query('SET SESSION sql_mode = ""');
        $this->Settings = $this->site->get_setting();
        if ($sma_language = $this->input->cookie('sma_language', true)) {
            $this->config->set_item('language', $sma_language);
            $this->lang->admin_load('sma', $sma_language);
            $this->Settings->user_language = $sma_language;
        } else {
            $this->config->set_item('language', $this->Settings->language);
            $this->lang->admin_load('sma', $this->Settings->language);
            $this->Settings->user_language = $this->Settings->language;
        }
        if ($rtl_support = $this->input->cookie('sma_rtl_support', true)) {
            $this->Settings->user_rtl = $rtl_support;
        } else {
            $this->Settings->user_rtl = $this->Settings->rtl;
        }
        
        $this->theme = $this->Settings->theme . '/admin/views/';
        if (is_dir(VIEWPATH . $this->Settings->theme . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
            $this->data['assets'] = base_url() . 'themes/' . $this->Settings->theme . '/admin/assets/';
        } else {
            $this->data['assets'] = base_url() . 'themes/default/admin/assets/';
        }

        $this->data['Settings'] = $this->Settings;
        $this->loggedIn         = $this->sma->logged_in();

        if ($this->loggedIn) {
            $this->default_currency         = $this->site->getCurrencyByCode($this->Settings->default_currency);
            $this->data['default_currency'] = $this->default_currency;
            $this->Owner                    = $this->sma->in_group('owner') ? true : null;
            $this->data['Owner']            = $this->Owner;
            $this->Customer                 = $this->sma->in_group('customer') ? true : null;
            $this->data['Customer']         = $this->Customer;
            $this->Supplier                 = $this->sma->in_group('supplier') ? true : null;
            $this->data['Supplier']         = $this->Supplier;
            $this->Admin                    = $this->sma->in_group('admin') ? true : null;
            $this->data['Admin']            = $this->Admin;

            if ($sd = $this->site->getDateFormat($this->Settings->dateformat)) {
                $dateFormats = [
                    'js_sdate'    => $sd->js,
                    'php_sdate'   => $sd->php,
                    'mysq_sdate'  => $sd->sql,
                    'js_ldate'    => $sd->js . ' hh:ii',
                    'php_ldate'   => $sd->php . ' H:i',
                    'mysql_ldate' => $sd->sql . ' %H:%i',
                ];
            } else {
                $dateFormats = [
                    'js_sdate'    => 'mm-dd-yyyy',
                    'php_sdate'   => 'm-d-Y',
                    'mysq_sdate'  => '%m-%d-%Y',
                    'js_ldate'    => 'mm-dd-yyyy hh:ii:ss',
                    'php_ldate'   => 'm-d-Y H:i:s',
                    'mysql_ldate' => '%m-%d-%Y %T',
                ];
            }
            if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'Pos.php')) {
                define('POS', 1);
            } else {
                define('POS', 0);
            }
            if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'Shop.php')) {
                define('SHOP', 1);
            } else {
                define('SHOP', 0);
            }
            if (!$this->Owner && !$this->Admin) {
                $gp               = $this->site->checkPermissions();
                $this->GP         = !empty($gp) ? $gp[0] : false;
                $this->data['GP'] = !empty($gp) ? $gp[0] : false;
            } else {
                $this->data['GP'] = null;
            }
            $this->dateFormats         = $dateFormats;
            $this->data['dateFormats'] = $dateFormats;
            $this->load->language('calendar');
            //$this->default_currency = $this->Settings->currency_code;
            //$this->data['default_currency'] = $this->default_currency;
            $this->m                    = strtolower($this->router->fetch_class());
            $this->v                    = strtolower($this->router->fetch_method());
            $this->data['m']            = $this->m;
            $this->data['v']            = $this->v;
            $this->data['dt_lang']      = json_encode(lang('datatables_lang'));
            $this->data['dp_lang']      = json_encode(['days' => [lang('cal_sunday'), lang('cal_monday'), lang('cal_tuesday'), lang('cal_wednesday'), lang('cal_thursday'), lang('cal_friday'), lang('cal_saturday'), lang('cal_sunday')], 'daysShort' => [lang('cal_sun'), lang('cal_mon'), lang('cal_tue'), lang('cal_wed'), lang('cal_thu'), lang('cal_fri'), lang('cal_sat'), lang('cal_sun')], 'daysMin' => [lang('cal_su'), lang('cal_mo'), lang('cal_tu'), lang('cal_we'), lang('cal_th'), lang('cal_fr'), lang('cal_sa'), lang('cal_su')], 'months' => [lang('cal_january'), lang('cal_february'), lang('cal_march'), lang('cal_april'), lang('cal_may'), lang('cal_june'), lang('cal_july'), lang('cal_august'), lang('cal_september'), lang('cal_october'), lang('cal_november'), lang('cal_december')], 'monthsShort' => [lang('cal_jan'), lang('cal_feb'), lang('cal_mar'), lang('cal_apr'), lang('cal_may'), lang('cal_jun'), lang('cal_jul'), lang('cal_aug'), lang('cal_sep'), lang('cal_oct'), lang('cal_nov'), lang('cal_dec')], 'today' => lang('today'), 'suffix' => [], 'meridiem' => []]);
            $this->Settings->indian_gst = false;
            if ($this->Settings->invoice_view > 0) {
                $this->Settings->indian_gst = $this->Settings->invoice_view == 2 ? true : false;
                $this->Settings->format_gst = true;
                $this->load->library('gst');
            }

            /* Accounts Variable*/    
            $this->mAccountSettings = $this->db->get('accounts_settings')->row();
            $this->mAccountSettings->decimals_sep = '.';
            $this->mAccountSettings->thousands_sep = ',';

            $this->mSettings = $this->db->get('sma_accounts_settings_main')->row();
            $this->mDateArray = explode('|', $this->mAccountSettings->date_format);

            /* Ledger selection */
            $ledgers = new LedgerTree(); // initilize ledgers array - LedgerTree Lib
            $ledgers->Group = &$this->Group; // initilize selected ledger groups in ledgers array
            $ledgers->Ledger = &$this->Ledger; // initilize selected ledgers in ledgers array
            $ledgers->current_id = -1; // initilize current group id
            // set restriction_bankcash from entrytype
            //$ledgers->restriction_bankcash = $entrytype['restriction_bankcash'];
            $ledgers->build(0); // set ledger id to [NULL] and ledger name to [None] 
            $ledgers->toList($ledgers, -1); // create a list of ledgers array
            $this->ledger_options= $ledgers->ledgerList; // pass ledger list to view

            foreach ($this->ledger_options as $id => $ledger)
            {
                $this->LO[$id] = $ledger;
                if($id < 0 )
                {
                   $this->DIS[] = $id;
                }
            } 
            $this->data['LO'] = $this->LO;
            $this->data['DIS'] = $this->DIS;
            //var_dump($dis);

            $this->vat_on_purchase = 121;
            $this->data['vat_on_purchase'] = $this->vat_on_purchase; 

            $this->vat_on_sale = 122;
            $this->data['vat_on_sale'] = $this->vat_on_sale; 

            $this->bank_fund_cash = 4;
            $this->data['bank_fund_cash'] = $this->bank_fund_cash;

            $this->bank_fees = 109;
            $this->data['bank_fees'] = $this->bank_fees;

            $this->bank_checking_account = 1;
            $this->data['bank_checking_account'] = $this->bank_checking_account;
        }
    }

    public function page_construct($page, $meta = [], $data = [])
    {
        $meta['message'] = $data['message'] ?? $this->session->flashdata('message');
        $meta['error']   = $data['error']   ?? $this->session->flashdata('error');
        $meta['warning'] = $data['warning'] ?? $this->session->flashdata('warning');
        $this->session->unset_userdata('error');
        $this->session->unset_userdata('message');
        $this->session->unset_userdata('warning');
        $meta['info']                = $this->site->getNotifications();
        $meta['events']              = $this->site->getUpcomingEvents();
        $meta['ip_address']          = $this->input->ip_address();
        $meta['Owner']               = $data['Owner'];
        $meta['Admin']               = $data['Admin'];
        $meta['Supplier']            = $data['Supplier'];
        $meta['Customer']            = $data['Customer'];
        $meta['Settings']            = $data['Settings'];
        $meta['dateFormats']         = $data['dateFormats'];
        $meta['assets']              = $data['assets'];
        $meta['GP']                  = $data['GP'];
        $meta['qty_alert_num']       = $this->site->get_total_qty_alerts();
        $meta['exp_alert_num']       = $this->site->get_expiring_qty_alerts();
        $meta['shop_sale_alerts']    = SHOP ? $this->site->get_shop_sale_alerts() : 0;
        $meta['shop_payment_alerts'] = SHOP ? $this->site->get_shop_payment_alerts() : 0;
        $this->session->unset_userdata('error');
        $this->session->unset_userdata('message');
        $this->session->unset_userdata('warning');
        $this->load->view($this->theme . 'header', $meta);
        $this->load->view($this->theme . $page, $data);
        $this->load->view($this->theme . 'footer');
    }
}
