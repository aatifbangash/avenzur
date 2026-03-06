<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Purchase Order Upload Model
 *
 * Handles database operations for purchase order uploads from Excel files.
 */
class Purchase_order_upload_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('purchase_order_model');
        $this->load->admin_model('products_model');
    }

    /**
     * Save reviewed PO rows into DB
     * @param array $payload
     * @return array
     */
    public function save_reviewed_po($payload = [])
    {
        if (!is_array($payload) || empty($payload)) {
            return [
                'success' => false,
                'error'   => 'Invalid payload received.'
            ];
        }

        $date         = !empty($payload['date']) ? $payload['date'] : date('Y-m-d H:i:s');
        $warehouse_id = !empty($payload['warehouse_id']) ? (int)$payload['warehouse_id'] : 0;
        $supplier_id  = !empty($payload['supplier_id']) ? (int)$payload['supplier_id'] : 0;

        if (!$warehouse_id || !$supplier_id) {
            return [
                'success' => false,
                'error'   => 'Warehouse and supplier are required.'
            ];
        }

        // Extract only row items (numeric keys only)
        $rows = [];
        foreach ($payload as $key => $value) {
            if (is_int($key) || ctype_digit((string)$key)) {
                if (is_array($value)) {
                    $rows[] = $value;
                }
            }
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'error'   => 'No reviewed rows found to save.'
            ];
        }

        $this->load->admin_model('purchase_order_model');
        $this->load->admin_model('products_model');

        $products = [];
        $missing_products = [];

        $grand_purchase_total   = 0;
        $grand_total_before_vat = 0;
        $grand_total_vat        = 0;
        $grand_total_sale       = 0;
        $grand_total_purchase   = 0;
        $grand_total_discount   = 0;

        foreach ($rows as $row) {

            // skip invalid/empty rows
            $item_code = trim((string)($row['code'] ?? ''));
            $item_name = trim((string)($row['name'] ?? ''));
            $qty       = (float)($row['quantity'] ?? 0);

            if (!$item_code || !$item_name || !$qty) {
                continue;
            }

            $varient_name   = trim((string)($row['variant'] ?? ''));
            $batch_no       = trim((string)($row['batch_no'] ?? ''));
            $expiry_date    = !empty($row['expiry_date']) ? $row['expiry_date'] : null;
            $sale_price     = (float)($row['sale_price'] ?? 0);
            $purchase_price = (float)($row['purchase_price'] ?? 0);
            $cost_price     = (float)($row['cost_price'] ?? 0);
            $tax_percent    = (float)($row['vat_percent'] ?? 0);

            $dis1_percent   = (float)($row['discount1'] ?? 0);
            $dis1_value     = (float)($row['discount1_value'] ?? 0);
            $dis2_percent   = (float)($row['discount2'] ?? 0);
            $dis2_value     = (float)($row['discount2_value'] ?? 0);
            $dis3_percent   = (float)($row['discount3'] ?? 0);
            $dis3_value     = (float)($row['discount3_value'] ?? 0);

            $details        = trim((string)($row['details'] ?? ''));
            $details_ar     = trim((string)($row['details_ar'] ?? ''));
            $image_link     = trim((string)($row['image'] ?? ''));
            $brand_name     = trim((string)($row['brand_name'] ?? ''));

            // fallback logic
            if ($purchase_price <= 0) {
                $purchase_price = $cost_price;
            }
            if ($cost_price <= 0) {
                $cost_price = $purchase_price;
            }

            $product = $this->purchase_order_model->getProductByCode($item_code);
            $product_id = $product ? $product->id : null;
            $parts        = explode("\n", trim($item_name));
            $english_name = trim($parts[0] ?? '');
            $arabic_name  = trim($parts[1] ?? '');
            if (!$product_id) {

                if ($image_link == '') {
                    return [
                        'success' => false,
                        'error'   => 'Image link missing for new product: ' . $item_code
                    ];
                }

                // tax mapping
                if ($tax_percent == 15) {
                    $tax_rate_id = 5;
                } else {
                    $tax_rate_id = 1;
                    $tax_percent = 0;
                }

                $parent_id = null;
                $parts        = explode("\n", trim($item_name));
                $english_name = trim($parts[0] ?? '');
                $arabic_name  = trim($parts[1] ?? '');
                
                
                $product_data = [
                    'code'           => $item_code,
                    'name'           => $english_name,
                    'name_ar'           => $arabic_name,
                    'category_id'    => null,
                    'cost'           => $purchase_price,
                    'price'          => $sale_price,
                    'tax_rate'       => $tax_rate_id,
                    'image'          => $image_link,
                    'type'           => 'standard',
                    'unit'           => 'unit',
                    'alert_quantity' => 0,
                    'track_quantity' => 1,
                    'details'        => $details,
                    'details_ar'     => $details_ar,
                    'variant'        => $varient_name,
                    'parent_id'      => $parent_id,
                    'brand_name'     => $brand_name,
                ];

                $add_result = $this->products_model->addProductSimplified($product_data);

                if ($add_result) {
                    $product = $this->purchase_order_model->getProductByCode($item_code);
                    $product_id = $product ? $product->id : null;
                }

            } else {

                // update image/details for existing product
                $this->purchase_order_model->updateProductImage($product_id, $image_link, $details);

                // optional brand update for existing product
                if (!empty($brand_name)) {
                    $this->db->where('id', $product_id)->update('products', ['brand' => $brand_name]);
                }

                if (($product->image == '' || $product->image == null) && $image_link == '') {
                    return [
                        'success' => false,
                        'error'   => 'Image link missing for existing product: ' . $item_code
                    ];
                }

                $tax_rate_id = $product->tax_rate;
                $tax_percent = ((int)$product->tax_rate === 5) ? 15 : 0;
            }

            if (!$product_id) {
                $missing_products[] = $item_code;
                continue;
            }

            $vat_value       = ($cost_price * $qty * $tax_percent) / 100;
            $main_net        = ($cost_price * $qty) + $vat_value;
            $totalbeforevat  = $cost_price * $qty;
            $totalsale       = $sale_price * $qty;
            $subtotal        = $purchase_price * $qty;

            $grand_purchase_total   += $subtotal;
            $grand_total_before_vat += $totalbeforevat;
            $grand_total_vat        += $vat_value;
            $grand_total_sale       += $totalsale;
            $grand_total_purchase   += $main_net;
            $grand_total_discount   += ($dis1_value + $dis2_value + $dis3_value);

            $products[] = [
                'product_id'            => $product_id,
                'product_code'          => $item_code,
                'product_name'          => $item_name,
                'batchno'               => $batch_no,
                'expiry'                => $expiry_date,
                'net_unit_cost'         => $cost_price,
                'quantity'              => $qty,
                'actual_quantity'       => $qty,
                'unit_quantity'         => $qty,
                'warehouse_id'          => $warehouse_id,
                'tax_rate_id'           => $tax_rate_id,
                'tax'                   => $tax_percent,
                'discount'              => $dis1_percent,
                'item_discount'         => $dis1_value,
                'sale_price'            => $sale_price,
                'unit_cost'             => $purchase_price,
                'item_tax'              => $vat_value,
                'subtotal'              => $subtotal,
                'quantity_balance'      => 0,
                'quantity_received'     => 0,
                'discount1'             => $dis1_percent,
                'discount2'             => $dis2_percent,
                'discount3'             => $dis3_percent,
                'totalbeforevat'        => $totalbeforevat,
                'main_net'              => $main_net,
                'second_discount_value' => $dis2_value,
                'third_discount_value'  => $dis3_value,
                'date'                  => $date,
            ];
        }

        if (!empty($missing_products)) {
            return [
                'success' => false,
                'error'   => 'Could not create the following products: ' . implode(', ', $missing_products)
            ];
        }

        $supplier = $this->site->getCompanyByID($supplier_id);

        $data = [
            'date'               => $date,
            'warehouse_id'       => $warehouse_id,
            'supplier_id'        => $supplier_id,
            'supplier'           => $supplier ? $supplier->name : '',
            'note'               => 'Created via Excel upload',
            'total'              => $grand_purchase_total,
            'total_net_purchase' => $grand_total_before_vat,
            'total_sale'         => $grand_total_sale,
            'total_discount'     => $grand_total_discount,
            'total_tax'          => $grand_total_vat,
            'grand_total'        => $grand_total_purchase,
            'status'             => 'pending',
            'created_by'         => $this->session->userdata('user_id'),
            'updated_by'         => $this->session->userdata('user_id'),
            'updated_at'         => date('Y-m-d H:i:s')
        ];
        // To check duplicate purchase order (same supplier, warehouse, and grand total)
        $existing = $this->db
            ->where('supplier_id', $supplier_id)
            ->where('warehouse_id', $warehouse_id)
            ->where('grand_total', $grand_total_purchase)
            ->order_by('id', 'DESC')
            ->get('purchase_orders', 1)
            ->row();

        if ($existing) {
            return [
                'success' => false,
                'error' => 'Duplicate purchase order detected.'
            ];
        }
        $result = $this->purchase_order_model->addPurchaseFromExcel($data, $products);

        if ($result && isset($result['success']) && $result['success']) {
            return [
                'success'      => true,
                'purchase_id'  => $result['purchase_id'],
                'reference_no' => $data['reference_no'] ?? ''
            ];
        }

        return [
            'success' => false,
            'error'   => !empty($result['error']) ? $result['error'] : 'Failed to create purchase order from reviewed rows.'
        ];
    }
}