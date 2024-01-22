<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->admin_load('cron');
        $this->load->admin_model('cron_model');
        $this->Settings = $this->cron_model->getSettings();
    }

    public function index()
    {
        show_404();
    }

    public function run()
    {
        if ($m = $this->cron_model->run_cron()) {
            if ($this->input->is_cli_request()) {
                foreach ($m as $msg) {
                    echo $msg . "\n";
                }
            } else {
                echo '<!doctype html><html><head><title>Cron Job</title><style>p{background:#F5F5F5;border:1px solid #EEE; padding:15px;}</style></head><body>';
                echo '<p>' . lang('cron_finished') . '</p>';
                foreach ($m as $msg) {
                    echo '<p>' . $msg . '</p>';
                }
                echo '</body></html>';
            }
        }
    }

     public function products_notification()
    {
//        SET CRONJOB:- php index.php admin/cron products_notification
        $this->db->select('sn.id, sn.is_notified, sn.email, p.id as pid, p.name as pname, p.slug pslug, p.quantity');
        $this->db->from('sma_products p');
        $this->db->join('sma_out_of_stock_notify sn', 'p.id = sn.product_id');
        $this->db->where('sn.product_id >', 0);
        $this->db->where('sn.is_notified', 0);
        $this->db->where('p.quantity >', 0);

        $query = $this->db->get();

        $result = $query->result();
        print_r($result);
        if (!empty($result)) {
            $subject = "New Product Added to Stock";

            foreach ($result as $row) {

                $body = "<p>Hello,</p>";
                $body .= "<p>We are excited to inform you that new products have been added to our stock. You can now visit our website and explore the latest additions to our inventory.</p>";
                $body .= "<p><strong>Product Information:</strong></p>";
                $body .= "<ul>";
                $body .= "<li><strong>Product Name:</strong> {$row->pname}</li>";
                $body .= "</ul>";
                $body .= "<p>Click the following link to view the products and make a purchase or add them to your cart: <a href='" . site_url("product/{$row->pslug}") . "'>View Product</a></p>";
                $body .= "<p>Thank you for choosing our services!</p>";
             //echo $body;
                // Send the email notification
                $sent = true;
             $sent = $this->sma->send_email(
                   $row->email,
                   $subject,
                   $body,
                   "info@avenzur.com",
                   "Avenzur"
               );

                if($sent) {
                    $this->db
                        ->set('is_notified', 1)
                        ->set('date_updated', date('Y-m-d H:i:s'))
                        ->where(['id' => $row->id, 'is_notified' => 0])
                        ->update('out_of_stock_notify');
                }
            }
        }
    }

}
