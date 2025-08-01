<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Inventory_model');
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function addSerialsBatch($data) {
        // Validate and check if $data is a non-empty array
        if (!empty($data) && is_array($data)) {
            // Extract serial numbers from the input data
            $serial_numbers = array_column($data, 'serial_number');
    
            // Check which serial numbers already exist in the database
            $this->db->select('serial_number');
            $this->db->from('sma_serial_numbers');
            $this->db->where_in('serial_number', $serial_numbers);
            $query = $this->db->get();
            $existing_serials = array_column($query->result_array(), 'serial_number');
    
            // Filter out records with existing serial numbers
            $filtered_data = array_filter($data, function ($item) use ($existing_serials) {
                return !in_array($item['serial_number'], $existing_serials);
            });
    
            // Check if there is any data left to insert
            if (!empty($filtered_data)) {
                // Insert the filtered batch of records
                if ($this->db->insert_batch('sma_serial_numbers', $filtered_data)) {
                    return true; // Return success
                } else {
                    // Debugging: Get the database error if insertion fails
                    $error = $this->db->error();
                    print_r($error);
                    return false;
                }
            }
        }
    
        // If $data is not valid or no new serials to insert, return an error
        return false;
    }    

    public function addPayment($payment = [], $customer_id = null)
    {
        if (isset($payment['sale_id']) && isset($payment['paid_by']) && isset($payment['amount'])) {
            $payment['pos_paid'] = $payment['amount'];
            $inv = $this->getInvoiceByID($payment['sale_id']);
            $paid = $inv->paid + $payment['amount'];
            if ($payment['paid_by'] == 'ppp') {
                $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                $result = $this->paypal($payment['amount'], $card_info, '', $payment['sale_id']);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date'] = $this->sma->fld($result['created_at']);
                    $payment['amount'] = $result['amount'];
                    $payment['currency'] = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    if (!empty($result['message'])) {
                        foreach ($result['message'] as $m) {
                            $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                        }
                    } else {
                        $msg[] = lang('paypal_empty_error');
                    }
                }
            } elseif ($payment['paid_by'] == 'stripe') {
                $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                $result = $this->stripe($payment['amount'], $card_info);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date'] = $this->sma->fld($result['created_at']);
                    $payment['amount'] = $result['amount'];
                    $payment['currency'] = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                }
            } elseif ($payment['paid_by'] == 'authorize') {
                $authorize_arr = ['x_card_num' => $payment['cc_no'], 'x_exp_date' => ($payment['cc_month'] . '/' . $payment['cc_year']), 'x_card_code' => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $inv->id, 'x_description' => 'Sale Ref ' . $inv->reference_no . ' and Payment Ref ' . $payment['reference_no']];
                list($first_name, $last_name) = explode(' ', $payment['cc_holder'], 2);
                $authorize_arr['x_first_name'] = $first_name;
                $authorize_arr['x_last_name'] = $last_name;
                $result = $this->authorize($authorize_arr);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['approval_code'] = $result['approval_code'];
                    $payment['date'] = $this->sma->fld($result['created_at']);
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                }
            } else {
                if ($payment['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($payment['cc_no']);
                    $this->db->update('gift_cards', ['balance' => ($gc->balance - $payment['amount'])], ['card_no' => $payment['cc_no']]);
                } elseif ($customer_id && $payment['paid_by'] == 'deposit') {
                    $customer = $this->site->getCompanyByID($customer_id);
                    $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $payment['amount'])], ['id' => $customer_id]);
                }
                unset($payment['cc_cvv2']);
                $this->db->insert('payments', $payment);
                $paid += $payment['amount'];
            }
            if (!isset($msg)) {
                if ($this->site->getReference('pay') == $data['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($payment['sale_id']);
                return ['status' => 1, 'msg' => ''];
            }
            return ['status' => 0, 'msg' => $msg];
        }
        return false;
    }

    public function addPrinter($data = [])
    {
        if ($this->db->insert('printers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($warehouse_quantity = $this->getProductQuantity($product_id, $warehouse_id)) {
            $new_quantity = $warehouse_quantity['quantity'] - $quantity;
            if ($this->updateQuantity($product_id, $warehouse_id, $new_quantity)) {
                $this->site->syncProductQty($product_id, $warehouse_id);
                return true;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, -$quantity)) {
                $this->site->syncProductQty($product_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function getUnprocessedSerials(){
        $this->db->select('sma_serial_numbers.*, sma_sales.warehouse_id, sma_warehouses.gln as pharmacy_gln, sma_warehouses.rasd_user, sma_warehouses.rasd_pass');
        $this->db->from('sma_serial_numbers');
        $this->db->join('sma_sales', 'sma_serial_numbers.sale_id = sma_sales.id');
        $this->db->join('sma_warehouses', 'sma_warehouses.id = sma_sales.warehouse_id');
        $this->db->where('sma_serial_numbers.date_created >=', date('Y-m-d H:i:s', strtotime('-4 days')));
        $this->db->where('sma_serial_numbers.is_pushed', 0);

        $notification_serials = $this->db->get();
        return $notification_serials;
        
    }

    public function addSale($data = [], $items = [], $payments = [], $sid = null)
    {
        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $data['sequence_code'] = $this->sequenceCode->generate('SL', 5);

        $cost = $this->site->costing($items);
        // $this->sma->print_arrays($cost);
        $this->db->trans_start();
        $data['reference_no'] = $this->site->getReference('pos');
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            foreach ($items as $item) {

                $item['sale_id'] = $sale_id;
                $real_cost = $item['real_cost'];
                unset($item['real_cost']);
                $this->db->insert('sale_items', $item);
                // Code for serials here
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];

                $this->db->select('sma_invoice_serials.*');
                $this->db->from('sma_invoice_serials');
                $this->db->join('sma_transfers', 'sma_invoice_serials.tid = sma_transfers.id');
                $this->db->where('sma_invoice_serials.gtin', $serials_gtin);
                $this->db->where('sma_invoice_serials.sid', 0);
                $this->db->where('sma_invoice_serials.rsid', 0);
                $this->db->where('sma_invoice_serials.pid !=', 0);
                $this->db->where('sma_invoice_serials.tid !=', 0);
                $this->db->where('sma_invoice_serials.batch_no =', $item['batch_no']);
                $this->db->where('sma_transfers.to_warehouse_id', $item['warehouse_id']);
                $this->db->where('sma_transfers.status', 'completed');
                $this->db->limit($serials_quantity);

                $notification_serials = $this->db->get();

                if ($notification_serials->num_rows() > 0) {
                    foreach (($notification_serials->result()) as $row) {
                        $this->db->update('sma_invoice_serials', ['sid' => $sale_id], ['serial_number' => $row->serial_number, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                    }
                }
                // Code for serials end here

                $rsd['OperationType'] = 'DISPATCH';
                $rsd['TransactionNumber'] = 0;
                $rsd['FromID'] = $sale_id;
                $rsd['ToID'] = 0;
                $rsd['GTIN'] = $item['product_code'];
                $rsd['BatchNumber'] = 0;
                $rsd['ExpiryDate'] = 0;
                $rsd['SerialNo'] = $item['serial_no'];
                $item_unit_quantity = $item['unit_quantity'];
                for ($k = 0; $k < $item_unit_quantity; $k++) {
                    $this->db->insert('sma_rsd', $rsd);
                }

                $sale_item_id = $this->db->insert_id();
                if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {

                     //handle inventory movement function
                $this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'pos', $item['quantity'], $item['warehouse_id'], $sale_id, $item['net_cost'], $item['expiry'], $item['net_unit_price'], $real_cost, $item['avz_item_code'], NULL, $data['customer_id'], $item['net_unit_price'], $data['date']);

                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                            $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $sale_id;
                                $ic['date'] = date('Y-m-d', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            if ($data['sale_status'] == 'completed') {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncQuantity($sale_id);
            if ($sid) {
                $this->deleteBill($sid);
            }
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            $this->site->updateReference('pos');
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Pos_model.php)');
        } else {
            $msg = [];
            if (!empty($payments)) {
                $paid = 0;
                foreach ($payments as $payment) {
                    if (!empty($payment) && isset($payment['amount']) && $payment['amount'] != 0) {
                        $payment['sale_id'] = $sale_id;
                        $payment['reference_no'] = $this->site->getReference('pay');
                        if ($payment['paid_by'] == 'ppp') {
                            $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                            $result = $this->paypal($payment['amount'], $card_info, '', $sale_id);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date'] = $this->sma->fld($result['created_at']);
                                $payment['amount'] = $result['amount'];
                                $payment['currency'] = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                if (!empty($result['message'])) {
                                    foreach ($result['message'] as $m) {
                                        $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                                    }
                                } else {
                                    $msg[] = lang('paypal_empty_error');
                                }
                            }
                        } elseif ($payment['paid_by'] == 'stripe') {
                            $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                            $result = $this->stripe($payment['amount'], $card_info);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date'] = $this->sma->fld($result['created_at']);
                                $payment['amount'] = $result['amount'];
                                $payment['currency'] = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                            }
                        } elseif ($payment['paid_by'] == 'authorize') {
                            $authorize_arr = ['x_card_num' => $payment['cc_no'], 'x_exp_date' => ($payment['cc_month'] . '/' . $payment['cc_year']), 'x_card_code' => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $sale_id, 'x_description' => 'Sale Ref ' . $data['reference_no'] . ' and Payment Ref ' . $payment['reference_no']];
                            list($first_name, $last_name) = explode(' ', $payment['cc_holder'], 2);
                            $authorize_arr['x_first_name'] = $first_name;
                            $authorize_arr['x_last_name'] = $last_name;
                            $result = $this->authorize($authorize_arr);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['approval_code'] = $result['approval_code'];
                                $payment['date'] = $this->sma->fld($result['created_at']);
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                            }
                        } else {
                            if ($payment['paid_by'] == 'gift_card') {
                                $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
                                unset($payment['gc_balance']);
                            } elseif ($payment['paid_by'] == 'deposit') {
                                $customer = $this->site->getCompanyByID($data['customer_id']);
                                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $payment['amount'])], ['id' => $customer->id]);
                            }
                            unset($payment['cc_cvv2']);
                            $this->db->insert('payments', $payment);
                            $this->site->updateReference('pay');
                            $paid += $payment['amount'];
                        }
                    }
                }
                $this->site->syncSalePayments($sale_id);
            }
            return ['sale_id' => $sale_id, 'message' => $msg];
        }
        return false;
    }

    public function authorize($authorize_data)
    {
        $this->load->library('authorize_net');
        // $authorize_data = array( 'x_card_num' => '4111111111111111', 'x_exp_date' => '12/20', 'x_card_code' => '123', 'x_amount' => '25', 'x_invoice_num' => '15454', 'x_description' => 'References');
        $this->authorize_net->setData($authorize_data);

        if ($this->authorize_net->authorizeAndCapture()) {
            $result = [
                'transaction_id' => $this->authorize_net->getTransactionId(),
                'approval_code' => $this->authorize_net->getApprovalCode(),
                'created_at' => date($this->dateFormats['php_ldate']),
            ];
            return $result;
        }
        return ['error' => 1, 'msg' => $this->authorize_net->getError()];
    }

    public function bills_count()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        return $this->db->count_all_results('suspended_bills');
    }

    public function closeRegister($rid, $user_id, $data)
    {
        if (!$rid) {
            $rid = $this->session->userdata('register_id');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        if ($data['transfer_opened_bills'] == -1) {
            $this->site->log('POS Bills', []);
            $this->db->delete('suspended_bills', ['created_by' => $user_id]);
        } elseif ($data['transfer_opened_bills'] != 0) {
            $this->db->update('suspended_bills', ['created_by' => $data['transfer_opened_bills']], ['created_by' => $user_id]);
        }
        if ($this->db->update('pos_register', $data, ['id' => $rid, 'user_id' => $user_id])) {
            return true;
        }
        return false;
    }

    public function deleteBill($id)
    {
        $this->site->log('POS Bill', ['model' => $this->getOpenBillByID($id), 'items' => $this->getSuspendedSaleItems($id)]);
        if ($this->db->delete('suspended_items', ['suspend_id' => $id]) && $this->db->delete('suspended_bills', ['id' => $id])) {
            return true;
        }

        return false;
    }

    public function deletePrinter($id)
    {
        if ($this->db->delete('printers', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function fetch_bills($limit, $start)
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'asc');
        $query = $this->db->get('suspended_bills');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = null, $brand_id = null)
    {
        $this->db->limit($limit, $start);
        if ($brand_id) {
            $this->db->where('brand', $brand_id);
        } elseif ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->where('hide_pos !=', 1);
        $this->db->order_by('name', 'asc');
        $query = $this->db->get('products');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function fetch_sales($limit, $start)
    {
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('sales');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'biller']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'customer']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getAllInvoiceItems($sale_id)
    {
        if ($this->pos_settings->item_order == 0) {
            $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, products.details as details, products.hsn_code as hsn_code, products.second_name as second_name')
                ->join('products', 'products.id=sale_items.product_id', 'left')
                ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
                ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
                ->group_by('sale_items.id')
                ->order_by('id', 'asc');
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details, products.hsn_code as hsn_code, products.second_name as second_name')
                ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
                ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
                ->join('products', 'products.id=sale_items.product_id', 'left')
                ->join('categories', 'categories.id=products.category_id', 'left')
                ->group_by('sale_items.id')
                ->order_by('categories.id', 'asc');
        }

        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPrinters()
    {
        $q = $this->db->get('printers');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->query('SELECT * FROM products ORDER BY id');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getAllSales()
    {
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getCosting()
    {
        $date = date('Y-m-d');
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', false)
            ->where('date', $date);

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getInvoicePayments($sale_id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return false;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('sale_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOpenBillByID($id)
    {
        $q = $this->db->get_where('suspended_bills', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getOpenRegisters()
    {
        $this->db->select('date, user_id, cash_in_hand, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' - ', " . $this->db->dbprefix('users') . '.email) as user', false)
            ->join('users', 'users.id=pos_register.user_id', 'left');
        $q = $this->db->get_where('pos_register', ['status' => 'open']);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPrinterByID($id)
    {
        $q = $this->db->get_where('printers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (!$this->Settings->overselling && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse], 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return false;
    }

    public function getProductQuantityWithNearestExpiry_old($product_id, $item_code, $warehouse)
    {
        $now = date('Y-m-d');  // Current date in the format 'YYYY-MM-DD'

        $this->db->select('batch_no');
        $this->db->from('invoice_serials');
        $this->db->where('gtin', $item_code);
        $this->db->where('tid >', 0);
        $this->db->where('sid =', 0);
        $q = $this->db->get();

        $batch_details = $q->result_array();

        if (!empty($batch_details)) {
            $batch_nos = array_column($batch_details, 'batch_no');

            $this->db->select('*');
            $this->db->from('warehouses_products');
            $this->db->where('product_id', $product_id);
            $this->db->where('warehouse_id', $warehouse);
            $this->db->where('quantity >', 0);
            $this->db->where('expiry >=', $now);  // Select products with expiry greater than or equal to the current date
            $this->db->where_in('batchno', $batch_nos); // Check for batch numbers in the serials
            $this->db->order_by('expiry', 'ASC'); // Order by expiry in ascending order
            $this->db->limit(1);
            $q = $this->db->get();

            if ($q->num_rows() > 0) {
                return $q->row_array();
            }
        }


        $this->db->select('*');
        $this->db->from('warehouses_products');
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_id', $warehouse);
        $this->db->where('quantity >', 0);
        $this->db->where('expiry >=', $now);  // Select products with expiry greater than or equal to the current date
        $this->db->order_by('expiry', 'ASC'); // Order by expiry in ascending order
        $this->db->limit(1);
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }

        return false;
    }
    public function getProductQuantityWithNearestExpiry($product_id, $item_code, $warehouse)
    {
        $now = date('Y-m-d');  // Current date in the format 'YYYY-MM-DD'

        $this->db->select('batch_no');
        $this->db->from('invoice_serials');
        $this->db->where('gtin', $item_code);
        $this->db->where('tid >', 0);
        $this->db->where('sid =', 0);
        $q = $this->db->get();

        $batch_details = $q->result_array();

        if (!empty($batch_details)) {
            $batch_nos = array_column($batch_details, 'batch_no');
            $this->db->select(' inv.product_id, inv.batch_number as batchno ,SUM(inv.quantity) as quantity, inv.location_id as warehouse_id, inv.expiry_date as expiry');
            $this->db->from('inventory_movements inv');
            $this->db->where('inv.location_id', $warehouse);
            $this->db->where('inv.product_id', $product_id);
            $this->db->where('inv.expiry_date >=', $now);  // Select products with expiry greater than or equal to the current date
            $this->db->where_in('inv.batch_number', $batch_nos);
            $this->db->group_by('inv.batch_number');
            $this->db->having('SUM(inv.quantity)>=0');
            $this->db->order_by('inv.expiry_date', 'ASC');
            $this->db->limit(1);
            $q = $this->db->get();
            //echo $this->db->last_query(); 
            if ($q->num_rows() > 0) {
                $rs = $q->row_array();
                //echo 'aa <pre>';print_r($rs); exit;
                return $rs;
            }
        }

        $this->db->select(' inv.product_id, inv.batch_number as batchno ,SUM(inv.quantity) as quantity, inv.location_id as warehouse_id, inv.expiry_date as expiry');
        $this->db->from('inventory_movements inv');
        $this->db->where('inv.location_id', $warehouse);
        $this->db->where('inv.product_id', $product_id);
        $this->db->where('inv.expiry_date >=', $now);  // Select products with expiry greater than or equal to the current date             
        $this->db->group_by('inv.batch_number');
        $this->db->having('SUM(inv.quantity)>=0');
        $this->db->order_by('inv.expiry_date', 'ASC');
        $this->db->limit(1);
        $q = $this->db->get();
        //echo $this->db->last_query(); 
        if ($q->num_rows() > 0) {
            $rs = $q->row_array(); //$q->row();
            //echo 'bb <pre>';print_r($rs); exit; 
            return $rs;
        }

        return false;
    }

    public function getProductsByCode($code)
    {
        $this->db->like('code', $code, 'both')->order_by('code');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterAuthorizeSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'authorize');
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCashRefunds($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date)->where('paid_by', 'cash');
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.returned) as returned')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCashSales($date, $user_id = null, $end_date=null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$end_date) {
            $end_date = date('Y-m-d H:i:s');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        // $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
        //     ->join('sales', 'sales.id=payments.sale_id', 'left')
        //     ->where('type', 'received')->where('date(payments.date) =', $date)->where('paid_by', 'cash');
        // $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        // $qu = $this->db->get_compiled_select('payments');
        // $q  = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        // if ($q->num_rows() > 0) {
        //     return $q->row();
        // }

    //    $sql = "
    //     SELECT 
    //         SUM( CASE
    //             WHEN MOD(amount, 1) > 0.50 THEN FLOOR(amount) + 1
    //             ELSE FLOOR(amount)
    //             END 
    //         ) AS total
    //     FROM 
    //         sma_payments payments
    //     LEFT JOIN 
    //         sma_sales sales ON sales.id = payments.sale_id
    //     WHERE 
    //         payments.type = 'received' 
    //         AND payments.paid_by = 'cash'
    //         AND DATE(payments.date) = '".trim($date)."'
    //         AND payments.created_by IN (".$user_id.")
    //    ;
    // ";

      $sql = "
        SELECT 
            SUM( CASE
                WHEN MOD(amount, 1) > 0.50 THEN FLOOR(amount) + 1
                ELSE FLOOR(amount)
                END 
            ) AS total,
             
               SUM(amount)
             AS total_with_halala
        FROM 
            sma_payments payments
        LEFT JOIN 
            sma_sales sales ON sales.id = payments.sale_id
        WHERE 
            payments.type = 'received' 
            AND payments.paid_by = 'cash'
            AND DATE(payments.date) >= '".trim($date)."'
            AND DATE(payments.date) <= '".trim($end_date)."'
            AND payments.created_by IN (".$user_id.")
       ;
    ";

        $q = $this->db->query($sql);
        $result = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $result = $row;
            }
            return $result;
        }

        return false;
    }

    public function getRegisterCCSales($date, $user_id = null, $end_date = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
         if (!$end_date) {
            $end_date = date('Y-m-d');
        }
        // $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
        //     ->join('sales', 'sales.id=payments.sale_id', 'left')
        //     ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'CC');
        // $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        // $qu = $this->db->get_compiled_select('payments');
        // $q = $this->db->select('SUM(sp.total_cc_slips) as total_cc_slips, SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        // if ($q->num_rows() > 0) {
        //     return $q->row();
        // }

        $sql = "
        SELECT 
            SUM( COALESCE(payments.amount, 0) ) AS total
        FROM 
            sma_payments payments
        LEFT JOIN 
            sma_sales sales ON sales.id = payments.sale_id
        WHERE 
            payments.type = 'received' 
            AND payments.paid_by = 'card'
            AND DATE(payments.date) >= '".trim($date)."'
            AND DATE(payments.date) <= '".trim($end_date)."'
            AND payments.created_by IN (".$user_id.")
    ";

        $q = $this->db->query($sql);
        $result = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $result = $row;
            }
            return $result;
        }

        return false;
    }

    public function getRegisterChSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'Cheque');

        $user_ids = explode(',', $user_id);
        $user_ids = array_map('trim', $user_ids);
        $this->db->where_in('payments.created_by', $user_ids);
        $this->db->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total_cheques) as total_cheques, SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterExpenses($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false)
            ->where('date >', $date);
        $this->db->where('created_by', $user_id);
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterGCSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'gift_card');
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterOtherSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'other');
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterPPPSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'ppp');
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterRefunds($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.returned) as returned')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterReturns($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
            ->where('date >', $date)
            ->where('returns.created_by', $user_id);

        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterReturnsNew($date, $user_id = null, $end_date = null){
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        $sql = "SELECT 
            SUM(sp.total) AS total, 
            SUM(sp.paid) AS paid,
            COUNT(sp.id) AS total_returns
        FROM (
            SELECT 
                COALESCE(returns.total, 0) AS total, 
                SUM(COALESCE(payments.amount, 0)) AS paid,
                returns.id
            FROM 
                sma_payments payments
            LEFT JOIN 
                sma_returns returns ON returns.id = payments.return_id
            WHERE 
                payments.type = 'completed' 
                AND DATE(payments.date) >= '".trim($date)."'  
                AND DATE(payments.date) <= '".trim($end_date)."' 
                AND payments.created_by IN (".$user_id.")
            GROUP BY 
                payments.return_id
        ) AS sp;
        ";
       
        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterSales($date, $user_id = null, $end_date = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }
        // $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
        //     ->join('sales', 'sales.id=payments.sale_id', 'left')
        //     ->where('type', 'received')->where(DATE(`payments.date`), $date);
        // $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');
        // $qu = $this->db->get_compiled_select('payments');
        // $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();

        $sql = "SELECT 
    SUM(sp.total) AS total, 
    SUM(sp.paid) AS paid,
    COUNT(sp.id) AS total_sales
FROM (
    SELECT 
        COALESCE(sales.grand_total, 0) AS total, 
        SUM(COALESCE(payments.amount, 0)) AS paid,
        sales.id
    FROM 
        sma_payments payments
    LEFT JOIN 
        sma_sales sales ON sales.id = payments.sale_id
    WHERE 
        payments.type = 'received' 
        AND DATE(payments.date) >= '".trim($date)."'  
        AND DATE(payments.date) <= '".trim($end_date)."'  
        AND payments.created_by IN (".$user_id.")
    GROUP BY 
        payments.sale_id
) AS sp;
";

$q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterStripeSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'stripe');
        $this->db->where('payments.created_by', $user_id)->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleItems($id)
    {
        $q = $this->db->get_where('sale_items', ['sale_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSuspendedSaleItems($id)
    {
        $q = $this->db->get_where('suspended_items', ['suspend_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSuspendedSales($user_id = null)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('suspended_bills', ['created_by' => $user_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getTodayAuthorizeSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'authorize')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayCashRefunds()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date)->where('paid_by', 'cash')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayCashSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'cash')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayCCSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'CC')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total_cc_slips) as total_cc_slips, SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayChSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'Cheque')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total_cheques) as total_cheques, SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayExpenses()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false)
            ->where('date >', $date);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayPPPSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'ppp')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayRefunds()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date)
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.returned) as returned')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayReturns()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
            ->where('date >', $date);

        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodaySales()
    {
        $sdate = date('Y-m-d 00:00:00');
        $edate = date('Y-m-d 23:59:59');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('sales.date >=', $sdate)->where('payments.date <=', $edate)
            ->group_by('sales.id');

        $qu = $this->db->get_compiled_select('sales');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayStripeSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COALESCE( grand_total, 0 ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'stripe')
            ->group_by('payments.sale_id');

        $qu = $this->db->get_compiled_select('payments');
        $q = $this->db->select('SUM(sp.total) as total, SUM(sp.paid) as paid')->from("({$qu}) sp")->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUsers()
    {
        $q = $this->db->get_where('users', ['company_id' => null]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWHProduct($code, $warehouse_id)
    {
        $this->db->select('products.*, warehouses_products.quantity, categories.id as category_id, categories.name as category_name')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->where('hide_pos !=', 1)
            ->group_by('products.id');
        $q = $this->db->get_where('products', ['products.code' => $code]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    /*public function getWHProductById($id, $warehouse_id)
    {
        $this->db->select('products.*, warehouses_products.quantity, categories.id as category_id, categories.name as category_name')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', ['products.id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }*/

    public function getWHProductById($id, $warehouse_id)
    {
        $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.customer_id,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code,
                            SUM(IFNULL(im.quantity, 0)) as total_quantity
                            ");
        $this->db->from('sma_inventory_movements im');
        $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
        $this->db->where('im.location_id', $warehouse_id);
        $this->db->where('im.product_id', $id);

        $this->db->group_by(['im.product_id', 'im.location_id']);
        $this->db->having('total_quantity !=', 0);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        return $query->result();
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
            return true;
        }
        return false;
    }

    public function openRegister($data)
    {
        if ($this->db->insert('pos_register', $data)) {
            return true;
        }
        return false;
    }

    public function paypal($amount = null, $card_info = [], $desc = '', $sale_id = null)
    {
        $this->load->admin_model('paypal_payments');
        //$card_info = array( "number" => "5522340006063638", "exp_month" => 2, "exp_year" => 2016, "cvc" => "456", 'type' => 'MasterCard' );
        //$amount = $amount ? $amount : 30.00;
        if ($amount && !empty($card_info)) {
            $data = $this->paypal_payments->Do_direct_payment($amount, $this->default_currency->code, $card_info, $desc, $sale_id);
            if (!isset($data['error'])) {
                $result = [
                    'transaction_id' => $data['TRANSACTIONID'],
                    'created_at' => date($this->dateFormats['php_ldate'], strtotime($data['TIMESTAMP'])),
                    'amount' => $data['AMT'],
                    'currency' => strtoupper($data['CURRENCYCODE']),
                ];
                return $result;
            }
            return $data;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = null, $brand_id = null)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        if ($brand_id) {
            $this->db->where('brand', $brand_id);
        }
        $this->db->where('hide_pos !=', 1);
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function registerData($user_id)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('pos_register', ['user_id' => $user_id, 'status' => 'open'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function sales_count()
    {
        return $this->db->count_all('sales');
    }

    public function stripe($amount = 0, $card_info = [], $desc = '')
    {
        $this->load->admin_model('stripe_payments');
        //$card_info = array( "number" => "4242424242424242", "exp_month" => 1, "exp_year" => 2016, "cvc" => "314" );
        //$amount = $amount ? $amount*100 : 3000;
        unset($card_info['type']);
        $amount = $amount * 100;
        if ($amount && !empty($card_info)) {
            $token_info = $this->stripe_payments->create_card_token($card_info);
            if (!isset($token_info['error'])) {
                $token = $token_info->id;
                $data = $this->stripe_payments->insert($token, $desc, $amount, $this->default_currency->code);
                if (!isset($data['error'])) {
                    $result = [
                        'transaction_id' => $data->id,
                        'created_at' => date($this->dateFormats['php_ldate'], $data->created),
                        'amount' => ($data->amount / 100),
                        'currency' => strtoupper($data->currency),
                    ];
                    return $result;
                }
                return $data;
            }
            return $token_info;
        }
        return false;
    }

    public function suspendSale($data = [], $items = [], $did = null)
    {
        $sData = [
            'count' => $data['total_items'],
            'biller_id' => $data['biller_id'],
            'customer_id' => $data['customer_id'],
            'warehouse_id' => $data['warehouse_id'],
            'customer' => $data['customer'],
            'date' => $data['date'],
            'suspend_note' => $data['suspend_note'],
            'total' => $data['grand_total'],
            'order_tax_id' => $data['order_tax_id'],
            'order_discount_id' => $data['order_discount_id'],
            'created_by' => $this->session->userdata('user_id'),
        ];

        if ($did) {
            if ($this->db->update('suspended_bills', $sData, ['id' => $did]) && $this->db->delete('suspended_items', ['suspend_id' => $did])) {
                $addOn = ['suspend_id' => $did];
                end($addOn);
                foreach ($items as &$var) {
                    $var = array_merge($addOn, $var);
                }
                if ($this->db->insert_batch('suspended_items', $items)) {
                    return true;
                }
            }
        } else {
            if ($this->db->insert('suspended_bills', $sData)) {
                $suspend_id = $this->db->insert_id();
                $addOn = ['suspend_id' => $suspend_id];
                end($addOn);
                foreach ($items as &$var) {
                    $var = array_merge($addOn, $var);
                }
                if ($this->db->insert_batch('suspended_items', $items)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function updatePrinter($id, $data = [])
    {
        if ($this->db->update('printers', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updateProductQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->addQuantity($product_id, $warehouse_id, $quantity)) {
            return true;
        }

        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->update('warehouses_products', ['quantity' => $quantity], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            return true;
        }
        return false;
    }

    public function updateSetting($data)
    {
        $this->db->where('pos_id', '1');
        if ($this->db->update('pos_settings', $data)) {
            return true;
        }
        return false;
    }

    public function getPaymentType($id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPaymentTypes($id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    // public function deleteBill($id)
    // {
    //     if ($this->db->delete('sma_suspended_bills', ['id' => $id])) {
    //         $this->db->delete('sma_suspended_items', ['suspend_id' => $id]);
    //         return true;
    //     }
    //     return false;
    // }

    public function getSalesByDateRange($selected_date, $sale_id='')
    {
        /**get grand discount */
        $where = '';
        if($this->session->userdata('warehouse_id') != 32) {
            $where .= ' AND s.warehouse_id = ' . $this->session->userdata('warehouse_id');
        }
        if($sale_id != '') {
            $where .= ' AND s.id = ' . $sale_id;
        }
       echo $sql = " SELECT
        id
          FROM  
        sma_sales s 
                            
        WHERE 
            DATE(s.date) = '" . trim($selected_date) . "' ".$where ;
        $q = $this->db->query($sql);
        //echo $this->db->last_query();
        $sales_id = array();
        //echo $q->num_rows();
        if ($q->num_rows() > 0) {
            foreach($q->result_array() as $row){
                $sales_id[] = $row['id'];
            }
          return $sales_id;  
        }
        
        
        return false;




    }


}
