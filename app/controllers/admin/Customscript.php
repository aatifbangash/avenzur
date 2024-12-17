<?php
defined('BASEPATH') or exit('No direct script access allowed');
// error_reporting(-1);
// ini_set('display_errors', 1);
class Customscript extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('purchases_model');
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();

    }
    public function index()
    {
        echo "Please contact administrator for running this script!";exit;
        $count = $this->input->get('count');
        if($count == '') {
            echo "Pass count number" ;exit;
        }
        
        $file = FCPATH . 'files_nehawand/nahwand'.$count.'.csv';
        if (($handle = fopen($file, "r")) !== false) {
            $dataToSend = [];
            $header = fgetcsv($handle); // Read the first row as the header

            // Read each row in the CSV file
            $categoryArr = [
                111 => 17,
                121 => 18,
                131 => 19,
                141 => 20,
                142 => 21,
                143 => 22,
                161 => 23,
                151 => 16

            ];
            $rowCount = 1;
            $addedRowCount = 1;
            $batchSize = 501;
            $startCount = ($count * $batchSize) + ($count > 0 ? 1 : 0);
            
            $purchaseData = array();
            while (($row = fgetcsv($handle)) !== false) {
                $rowCount++;
               
                if ($addedRowCount < $batchSize) {
                    $addedRowCount++ ;

                    $item_code = $row[0];
                    $item_name = $row[1];
                    $item_batch_number = $row[2];
                    $item_ascon_code = $row[3];
                    $item_expiry_date = $row[4];
                    $item_qty = floatval($row[5]);
                    $item_sale_price = floatval($row[6]);
                    $item_total_sale_price = floatval($row[7]);
                    $item_purchase_price = floatval($row[8]);
                    $item_total_purchase_price = floatval($row[9]);
                    $item_cost_price = floatval($row[10]);
                    $item_total_cost_price = floatval($row[11]);
                    $item_before_vat = floatval($row[11]);
                    $vat_value = floatval($row[12]);
                    $item_total_vat = floatval($row[13]);
                    $item_total_after_vat = floatval($row[14]);

                    //get category id
                    $first_three_digit = substr($item_code, 0, 3);
                    $category_id = 0;
                    if (isset($categoryArr[$first_three_digit])) {
                        $category_id = $categoryArr[$first_three_digit];
                    }




                    // master data
                    $masterData[$item_code] = array(
                        'item_code' => $item_code,
                        'item_name' => $item_name,
                        'sale_price' => $item_sale_price,
                        'cost_price' => $item_cost_price,
                        'purchase_price' => $item_purchase_price,
                        'category_id' => $category_id,
                        'quantity' => $item_qty
                    );

                    $purchaseData[] = array(
                        'item_code' => $item_code,
                        'item_name' => $item_name,
                        'item_quantity' => $item_qty,
                        'sale_price' => $item_sale_price,
                        'cost_price' => $item_cost_price,
                        'purchase_price' => $item_purchase_price,
                        'ascon_code' => $item_ascon_code,
                        'item_cost_price' => $item_cost_price,
                        'item_total_cost' => $item_total_cost_price,
                        'item_sale_price' => $item_sale_price,
                        'item_total_sale_price' => $item_total_sale_price,
                        'item_purchase_price' => $item_purchase_price,
                        'item_total_purchase_price' => $item_total_purchase_price,
                        'item_batch_number' => $item_batch_number,
                        'item_expiry_date' => $item_expiry_date,
                        'item_vat_value' => $vat_value,
                        'item_before_vat' => $item_before_vat,
                        'item_total_vat' => $item_total_vat,
                        'item_total_after_vat' => $item_total_after_vat,
                        'tax_rate_id' => ($vat_value > 0 ? 5 : 0),
                        'tax' => ($vat_value > 0 ? '15.00%' : 0),

                    );

                } 
                // $dataToSend[] = array_combine($header, $row); // Map header to each row
            }
            fclose($handle);
            // $i = 1;
            // foreach( $masterData as $key => $data) {
            //     $stmt = $conn->prepare("INSERT INTO sma_products SET name = ?, code =? , cost = ?, price = ?, category_id = ?, quantity = ?, item_code = ?");
            //     $stmt->bind_param("sdddiis", $data['item_name'], $i, $data['cost_price'], $data['sale_price'], $data['category_id'], $data['quantity'], $data['item_code']);
            //     $stmt->execute();
            //     $i = $i+1;
            // }

            // echo "<pre>";
            // print_r($masterData);exit;
            $i = 1;
            $postData = array();
            $grand_total_purchase = 0;
            $grand_total_net_purchase = 0;
            $grand_total_sale = 0;
            $grand_total = 0;
            $total_item = 0;
            $total_vat = 0;
            $products = array();
           //echo "<pre>";
           //print_r($purchaseData);
            if ($purchaseData ) {
                foreach ($purchaseData as $key => $row) {
                    //print_r($row);
                    $item_code = $row['item_code'];
                    $sql = "SELECT * FROM sma_products WHERE item_code = ?";
                    $query = $this->db->query($sql, [$item_code]);
                     //$this->db->last_query();
                    $product_data = $query->row_array();
                     $product_id = isset($product_data['id']) ? $product_data['id'] : null;
                    if($product_id == null) {
                        echo 'nullproduct'.$item_code;
                    }

                    // $postData['product_id'] = $product_id;
                    // $postData['product'] = $product_id;
                    // $postData['product_name'] = $row['item_name'];
                    // $postData['product_option'] =false;
                    // $postData['part_no'] ='';
                    // $postData['totalbeforevat'][] = $row['item_total_cost'];
                    // $postData['main_net'][] = $row['item_total_cost'];
                    // $postData['item_first_discount'][] = 0;
                    // $postData['item_second_discount'][] = 0;
                    // $postData['item_vat_values'][] = 0;
                    // $postData['item_net_purchase'][] =  $row['item_total_purchase_price'];
                    // $postData['item_total_purchase'][] =  $row['item_total_purchase_price'];
                    // $postData['item_total_sale'][] =  $row['item_total_sale_price'];
                    // $postData['item_unit_cost'][] =  $row['item_cost_price'];
                    // $postData['warehouse_shelf'][] =  '';
                    // $postData['sale_price'][] = $row['item_sale_price'];
                    // $postData['unit_cost'][] = $row['item_cost_price'];
                    // $postData['real_unit_cost'][] = $row['item_purchase_price'];
                    // $postData['net_cost'][] = $row['item_cost_price'];
                    // $postData['batchno'][] = $row['item_batch_number'];
                    // $postData['expiry'][] = $row['item_expiry_date'];
                    // $postData['quantity_balance'][] = $row['item_quantity'];
                    // $postData['quantity'][] = $row['item_quantity'];
                    // $postData['product_unit'][] = null;
                    // $postData['product_base_quantity'][] =  $row['item_quantity'];
                    // $postData['bonus'][] =  0;
                    // $postData['dis1'][] =  0;
                    // $postData['dis2'][] =  0;
                    // $postData['product_tax'][] =  0;

                    // $postData['discount'] = '';
                    // $postData['shipping'] = '';
                    // $postData['payment_term'] = '';
                    // $postData['note'] = '';
                    // $postData['add_pruchase'] = 'submit';

                    $expiry_date = date('Y-m-d', strtotime($row['item_expiry_date']));
                   
                    $products[] = [
                        'product_id' => $product_id,
                        'product_code' => $item_code,
                        'product_name' => $row['item_name'],
                        'option_id' => '',
                        'net_unit_cost' => $row['item_cost_price'], //item_net_cost,
                        'unit_cost' => $row['item_cost_price'], //+ $item_tax),
                        'quantity' => $row['item_quantity'],
                        'product_unit_id' => '',
                        'product_unit_code' => '',
                        'unit_quantity' => $row['item_quantity'],
                        'quantity_balance' => $row['item_quantity'],
                        'quantity_received' => $row['item_quantity'],
                        'warehouse_id' => 32,
                        'item_tax' => $row['item_total_vat'],
                        'tax_rate_id' => $row['tax_rate_id'],
                        'tax' => $row['tax'],
                        'discount' => '',
                        'item_discount' => '',
                        'subtotal' => $row['item_total_cost'],
                        'expiry' => $expiry_date,
                        'real_unit_cost' => $row['item_purchase_price'],
                        'sale_price' => $row['item_sale_price'],
                        'date' => date('Y-m-d'),
                        'status' => 'received',
                        'supplier_part_no' => '',
                        'subtotal2' => '',
                        'batchno' => $row['item_batch_number'],
                        'serial_number' => 'Default',
                        'bonus' => 0,
                        'discount1' => '',
                        'discount2' => '',
                        'second_discount_value' => '',
                        'totalbeforevat' => $row['item_before_vat'],
                        'main_net' => $row['item_total_after_vat'],
                        'avz_item_code' => $row['ascon_code']
                    ];
                    

                    $grand_total_purchase += floatval($row['item_total_cost']); //floatval($row['item_quantity']) * floatval($row['item_purchase_price']) ;
                    $grand_total_net_purchase += floatval($row['item_total_cost']);
                    //floatval($row['item_quantity']) * floatval($row['item_cost_price']) ;
                    $grand_total_sale += floatval($row['item_total_sale_price']);//floatval($row['item_quantity']) * floatval($row['item_sale_price']) ; 
                    $grand_total += floatval($row['item_total_after_vat']);//floatval($row['item_quantity']) * floatval($row['item_cost_price']) ; 
                    $total_item += floatval($row['item_quantity']);
                    $total_vat += floatval($row['item_total_vat']);

                    $i = $i + 1;

                }

                $data = [
                    'reference_no' => '123456',
                    'supplier_id' => '686',
                    'supplier' => 'Internal Supplier',
                    'warehouse_id' => '32',
                    'note' => '',
                    'total' => $grand_total_purchase,
                    'total_net_purchase' => $grand_total_net_purchase,
                    'total_sale' => $grand_total_sale,
                    'product_discount' => '',
                    'order_discount_id' => '',
                    'order_discount' => '',
                    'total_discount' => '',
                    'product_tax' => '',
                    'order_tax_id' => '',
                    'order_tax' => '',
                    'total_tax' => $total_vat,
                    'shipping' => '',
                    'grand_total' => $grand_total,
                    'status' => 'received',
                    'created_by' => '9',
                    'payment_term' => '',
                    'due_date' => '',
                    'sequence_code' => $this->sequenceCode->generate('PR', 5),
                    'invoice_number' => $this->purchases_model->generateInvoiceNumber()
                ];

                // echo "<pre>";
                // print_r($data);
                 //print_r($products);
                // exit;
                /** 
                 * PLEASE CHECK THE VALID CSV FILES BEFORE
                 * ENABLE THIS FUNCTION
                 */
                //$this->purchases_model->addPurchase($data, $products, $attachments = '');
            } else {
                echo "no data found";
            }

        } else {
            echo "Unable to open the CSV file.";
        }

    }

    public function items_commission()
    {
        //echo "To add commission, Please contact administrator!";exit;
        $file = FCPATH . 'files_nehawand/items_commission.csv';
        if (($handle = fopen($file, "r")) !== false) {
            $dataToSend = [];
            $header = fgetcsv($handle); // Read the first row as the header

            // Read each row in the CSV file
            $rowCount = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowCount++;
                $item_code = trim($row[0]);
                $item_name = trim($row[1]);
                $commission_value = $row[2];
                $from_date = date('Y-m-d', strtotime($row[6]));
                $to_date = date('Y-m-d', strtotime($row[7]));
                $supplier_id = 686;
                $child_supplier_id = 686;
                $created_by = 1;
                $date_created = date('Y-m-d h:m:i');
              
                   
                $sql = "INSERT INTO sma_items_commission 
                                (item_code , item_name, commission_value, from_date , to_date , supplier_id , child_supplier_id, date_created, created_by)
                                 VALUES (?, ?, ?, ?, ?, ?,?, ?, ?)";
                
                $this->db->query($sql, [$item_code, $item_name, $commission_value, $from_date, $to_date, $supplier_id, $child_supplier_id, $date_created, $created_by]);
                
                
            }
            fclose($handle);
           

        } else {
            echo "Unable to open the CSV file.";
        }

    }
}
