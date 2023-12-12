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
        $this->db->select('pn.id, u.email, u.first_name, u.last_name, p.id as pid, p.name as pname, p.slug pslug, p.quantity, pn.is_notified');
        $this->db->from('sma_products p');
        $this->db->join('sma_products_notification pn', 'p.id = pn.product_id');
        $this->db->join('sma_users u', 'u.id = pn.user_id');
        $this->db->where('pn.is_notified', 0);
//        $this->db->where('p.quantity >', 0);

        $query = $this->db->get();

        $result = $query->result();
        if (!empty($result)) {
            $subject = "New Products Added to Stock";

            foreach ($result as $row) {

                $body = "<p>Hello {$row->first_name} {$row->last_name},</p>";
                $body .= "<p>We are excited to inform you that new products have been added to our stock. You can now visit our website and explore the latest additions to our inventory.</p>";
                $body .= "<p><strong>Product Information:</strong></p>";
                $body .= "<ul>";
                $body .= "<li><strong>Product Name:</strong> {$row->pname}</li>";
                $body .= "</ul>";
                $body .= "<p>Click the following link to view the products and make a purchase or add them to your cart: <a href='" . site_url("product/{$row->pslug}") . "'>View Product</a></p>";
                $body .= "<p>Thank you for choosing our services!</p>";
echo $body;
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
                        ->where(['id' => $row->id, 'is_notified' => 0])
                        ->update('products_notification');
                }
            }
        }
    }
}
