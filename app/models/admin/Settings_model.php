<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addBrand($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('brands', $data)) {
            return true;
        }
        return false;
    }

    public function addCompany($data)
    {

        if ($this->db->insert('business', $data)) {
            $businessId = $this->db->insert_id();

            // add default groups for company.
            $this->db->query("INSERT INTO `sma_groups` (`name`, `description`, `business_id`)
            values ('owner', 'Owner', $businessId),
            ('admin', 'Administrator', $businessId),
            ('customer', 'Customer', $businessId),
            ('supplier', 'Supplier', $businessId)");

            // add default settings
            $totalSettingsRecords = $this->db->from("settings")->count_all_results();
            $defaultSettingsQuery = "INSERT INTO `sma_settings`
            (`setting_id`,
             `logo`,
             `logo2`,
             `site_name`,
             `language`,
             `default_warehouse`,
             `accounting_method`,
             `default_currency`,
             `default_tax_rate`,
             `rows_per_page`,
             `version`,
             `default_tax_rate2`,
             `dateformat`,
             `sales_prefix`,
             `quote_prefix`,
             `purchase_prefix`,
             `transfer_prefix`,
             `delivery_prefix`,
             `payment_prefix`,
             `return_prefix`,
             `returnp_prefix`,
             `expense_prefix`,
             `item_addition`,
             `theme`,
             `product_serial`,
             `default_discount`,
             `product_discount`,
             `discount_method`,
             `tax1`,
             `tax2`,
             `overselling`,
             `restrict_user`,
             `restrict_calendar`,
             `timezone`,
             `iwidth`,
             `iheight`,
             `twidth`,
             `theight`,
             `watermark`,
             `reg_ver`,
             `allow_reg`,
             `reg_notification`,
             `auto_reg`,
             `protocol`,
             `mailpath`,
             `smtp_host`,
             `smtp_user`,
             `smtp_pass`,
             `smtp_port`,
             `smtp_crypto`,
             `corn`,
             `customer_group`,
             `default_email`,
             `mmode`,
             `bc_fix`,
             `auto_detect_barcode`,
             `captcha`,
             `reference_format`,
             `racks`,
             `attributes`,
             `product_expiry`,
             `decimals`,
             `qty_decimals`,
             `decimals_sep`,
             `thousands_sep`,
             `invoice_view`,
             `default_biller`,
             `envato_username`,
             `purchase_code`,
             `rtl`,
             `each_spent`,
             `ca_point`,
             `each_sale`,
             `sa_point`,
             `update`,
             `sac`,
             `display_all_products`,
             `display_symbol`,
             `symbol`,
             `remove_expired`,
             `barcode_separator`,
             `set_focus`,
             `price_group`,
             `barcode_img`,
             `ppayment_prefix`,
             `disable_editing`,
             `qa_prefix`,
             `update_cost`,
             `apis`,
             `state`,
             `pdf_lib`,
             `use_code_for_slug`,
             `ws_barcode_type`,
             `ws_barcode_chars`,
             `flag_chars`,
             `item_code_start`,
             `item_code_chars`,
             `price_start`,
             `price_chars`,
             `price_divide_by`,
             `weight_start`,
             `weight_chars`,
             `weight_divide_by`,
             `ksa_qrcode`,
             `bank_fees`,
             `business_id`)
values ('" . ($totalSettingsRecords + 1) . "',
       'avenzur-logov2-024.png',
       'avenzur-logov2-0241.png',
       'Company name',
       'english',
       '0',
       '0',
       '',
       '1',
       '0',
       '',
       '1',
       '0',
       '0',
       'QUOTE',
       'PO',
       'TR',
       'DO',
       'IPAY',
       'SR',
       'PR',
       'EX',
       '0',
       'blue',
       '1',
       '1',
       '1',
       '1',
       '1',
       '1',
       '0',
       '1',
       '0',
       'Asia/Riyadh',
       '800',
       '800',
       '150',
       '150',
       '0',
       '0',
       '0',
       '0',
       NULL,
       'smtp',
       'app/libraries/sma.php',
       'mail.checkdev.xyz',
       'info@checkdev.xyz',
       '&P6b@UU&nyIo',
       '465',
       'ssl',
       NULL,
       '1',
       'eng.sheshtawy@gmail.com',
       '0',
       '4',
       '1',
       '0',
       '2',
       '1',
       '1',
       '1',
       '2',
       '2',
       '.',
       ',',
       '1',
       '524',
       '',
       '',
       '0',
       NULL,
       NULL,
       NULL,
       NULL,
       '0',
       '0',
       '1',
       '2',
       'SR',
       '0',
       '-',
       '1',
       '1',
       '0',
       'POP',
       '90',
       'QA',
       '1',
       '0',
       'AN',
       'dompdf',
       '1',
       'price',
       '0',
       '0',
       '0',
       '0',
       '0',
       '0',
       '0',
       '0',
       '0',
       '0',
       '1',
       '7.5',
       '$businessId'); ";

        $this->db->query($defaultSettingsQuery);
            return true;
        }
        return false;
    }

    public function deleteCompany($id)
    {
        if ($this->db->delete('business', ['id' => $id])) {
            $this->db->delete('groups', ['business_id' => $id]);
            $this->db->delete('settings', ['business_id' => $id]);
            return true;
        }
        return false;
    }

    public function addBrands($data)
    {
        if ($this->db->insert_batch('brands', $data)) {
            return true;
        }
        return false;
    }

    public function addCategories($categories, $subcategories)
    {
        $result = false;
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if (!is_int($category['parent_id'])) {
                    $category['parent_id'] = null;
                }
                $this->db->insert('categories', $category);
            }
            $result = true;
        }
        if (!empty($subcategories)) {
            foreach ($subcategories as $category) {
                if (is_int($category['parent_id'])) {
                    $this->db->insert('categories', $category);
                } else {
                    if ($pcategory = $this->getCategoryByCode($category['parent_id'])) {
                        $category['parent_id'] = $pcategory->id;
                        $this->db->insert('categories', $category);
                    }
                }
            }
            $result = true;
        }
        return $result;
    }

    public function addCategory($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('categories', $data)) {
            return true;
        }
        return false;
    }

    public function addCurrency($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('currencies', $data)) {
            return true;
        }
        return false;
    }

    public function addCustomerGroup($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategories($data)
    {
        if ($this->db->insert_batch('expense_categories', $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategory($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('expense_categories', $data)) {
            return true;
        }
        return false;
    }

    public function addGroup($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('groups', $data)) {
            $gid = $this->db->insert_id();
            $this->db->insert('permissions', ['group_id' => $gid]);
            return $gid;
        }
        return false;
    }

    public function addPriceGroup($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function addTaxRate($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function addUnit($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('units', $data)) {
            return true;
        }
        return false;
    }

    public function addVariant($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('variants', $data)) {
            return true;
        }
        return false;
    }

    public function addWarehouse($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function addShelf($data)
    {
        if ($this->db->insert_batch('warehouse_shelf', $data)) {
            return true;
        }
        return false;
    }

    public function brandHasProducts($brand_id)
    {
        $q = $this->db->get_where('products', ['brand' => $brand_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function checkGroupUsers($id)
    {
        $q = $this->db->get_where('users', ['group_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deleteBrand($id)
    {
        if ($this->db->delete('brands', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCategory($id)
    {
        if ($this->db->delete('categories', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCurrency($id)
    {
        if ($this->db->delete('currencies', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCustomerGroup($id)
    {
        if ($this->db->delete('customer_groups', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteExpenseCategory($id)
    {
        if ($this->db->delete('expense_categories', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteGroup($id)
    {
        if ($this->db->delete('groups', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteInvoiceType($id)
    {
        if ($this->db->delete('invoice_types', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePriceGroup($id)
    {
        if ($this->db->delete('price_groups', ['id' => $id]) && $this->db->delete('product_prices', ['price_group_id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteProductGroupPrice($product_id, $group_id)
    {
        if ($this->db->delete('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id])) {
            return true;
        }
        return false;
    }

    public function deleteTaxRate($id)
    {
        if ($this->db->delete('tax_rates', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteUnit($id)
    {
        if ($this->db->delete('units', ['id' => $id])) {
            $this->db->delete('units', ['base_unit' => $id]);
            return true;
        }
        return false;
    }

    public function deleteVariant($id)
    {
        if ($this->db->delete('variants', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteWarehouse($id)
    {
        if ($this->db->delete('warehouses', ['id' => $id]) && $this->db->delete('warehouses_products', ['warehouse_id' => $id])) {
            $this->db->delete('warehouses_products_variants', ['warehouse_id' => $id]);
            $this->db->update('purchase_items', ['quantity_balance' => 0], ['warehouse_id' => $id]);
            return true;
        }
        return false;
    }

    public function deletewarehouseShelf($id)
    {
        if ($this->db->delete('warehouse_shelf', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllCurrencies()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCustomerGroups()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPriceGroups()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('price_groups');
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
        return false;
    }

    public function getAllVariants()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllWarehouses()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBrandByName($name)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get_where('brands', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByCode($code)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get_where('categories', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByID($id)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get_where('categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCurrencyByID($id)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get_where('currencies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCustomerGroupByID($id)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get_where('customer_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDateFormats()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('date_format');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategoryByCode($code)
    {
        $q = $this->db->get_where('expense_categories', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where('expense_categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupByID($id)
    {
        $q = $this->db->get_where('groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', ['group_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupPrice($group_id, $product_id)
    {
        $q = $this->db->get_where('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroups()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where("business_id", $business_id);
        // $this->db->where('id >', 4);
        $ignore = array('owner', 'admin', 'customer', 'supplier');

        $this->db->where_not_in('name', $ignore);
        $q = $this->db->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getParentCategories()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('parent_id', null)->or_where('parent_id', 0)->where("business_id", $business_id);
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaypalSettings()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('paypal');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPriceGroupByID($id)
    {
        $q = $this->db->get_where('price_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductGroupPriceByPID($product_id, $group_id)
    {
        $pg = "(SELECT {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.product_id as product_id FROM {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('product_prices')}.price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price", false)
            // ->join('products', 'products.id=product_prices.product_id', 'left')
            ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSettings()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getdirectPay()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('directpay');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getaramex()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('aramex');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnitChildren($base_unit)
    {
        $this->db->where('base_unit', $base_unit);
        $q = $this->db->get('units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getVariantByID($id)
    {
        $q = $this->db->get_where('variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseByID($id)
    {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllShelf($id)
    {
        $q = $this->db->get_where('warehouse_shelf', ['warehouse_id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function GroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', ['group_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function hasExpenseCategoryRecord($id)
    {
        $this->db->where('category_id', $id);
        return $this->db->count_all_results('expenses');
    }

    public function setProductPriceForPriceGroup($product_id, $group_id, $price)
    {
        if ($this->getGroupPrice($group_id, $product_id)) {
            if ($this->db->update('product_prices', ['price' => $price], ['price_group_id' => $group_id, 'product_id' => $product_id])) {
                return true;
            }
        } else {
            if ($this->db->insert('product_prices', ['price' => $price, 'price_group_id' => $group_id, 'product_id' => $product_id])) {
                return true;
            }
        }
        return false;
    }

    public function updateBrand($id, $data = [])
    {
        if ($this->db->update('brands', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateCompany($id, $data = [])
    {
        if ($this->db->update('business', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateCategory($id, $data = [])
    {
        if ($this->db->update('categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateCurrency($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('currencies', $data)) {
            return true;
        }
        return false;
    }

    public function updateCustomerGroup($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateExpenseCategory($id, $data = [])
    {
        if ($this->db->update('expense_categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateGroup($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateGroupPrices($data = [])
    {
        foreach ($data as $row) {
            if ($this->getGroupPrice($row['price_group_id'], $row['product_id'])) {
                $this->db->update('product_prices', ['price' => $row['price']], ['product_id' => $row['product_id'], 'price_group_id' => $row['price_group_id']]);
            } else {
                $this->db->insert('product_prices', $row);
            }
        }
        return true;
    }

    public function updateLoginLogo($photo)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $logo = ['logo2' => $photo];
        if ($this->db->update('settings', $logo, ['business_id' => $business_id])) {
            return true;
        }
        return false;
    }

    public function updateLogo($photo)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $logo = ['logo' => $photo];
        if ($this->db->update('settings', $logo, ['business_id' => $business_id])) {
            return true;
        }
        return false;
    }

    public function updatePaypal($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return false;
    }

    public function updatePermissions($id, $data = [])
    {
        if ($this->db->update('permissions', $data, ['group_id' => $id]) && $this->db->update('users', ['show_price' => $data['products-price'], 'show_cost' => $data['products-cost']], ['group_id' => $id])) {
            return true;
        }
        return false;
    }

    public function updatePriceGroup($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateSetting($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        if ($this->db->update('settings', $data)) {
            return true;
        }
        return false;
    }

    public function updateSkrill($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return false;
    }
    public function updatedirectPay($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        if ($this->db->update('directpay', $data)) {
            return true;
        }
        return false;
    }

    public function updatearamex($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        if ($this->db->update('aramex', $data)) {
            return true;
        }
        return false;
    }

    public function updateTaxRate($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function updateUnit($id, $data = [])
    {
        if ($this->db->update('units', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateVariant($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('variants', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouse($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('warehouses', $data)) {
            return true;
        }
        return false;
    }
    public function insertCountry($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data['business_id'] = $business_id;
        if ($this->db->insert('countries', $data)) {
            return true;
        }
        return false;
    }
    public function getallCountry()
    {

        //TIP:- added
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where("business_id", $business_id);
        $query = $this->db->get('countries');
        return $query->result();
    }

    public function deleteCountry($id)
    {
        if ($this->db->delete('countries', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateCountry($id, $data)
    {
        if ($this->db->update('countries', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getCountryByID($id)
    {
        $q = $this->db->get_where('countries', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function insertWareCountry($data)
    {

        $this->db->empty_table('warehouses_country');
        if ($this->db->insert_batch('warehouses_country', $data)) {
            return true;
        }
        return false;
    }
    public function get_countryId($country)
    {


        $this->db->where('warehouses_country', $country);
        $this->db->limit(1);
        $query = $this->db->get($this->country_id);

        if ($query->num_rows() == 1) {
            return TRUE;
        }

        return FALSE;
    }

    public function checkCountryDeletion($id)
    {
        $this->db->Like('cf1', $id);
        $query = $this->db->get('products');

        if ($query->num_rows() > 0) {
            return false;
        }

        $this->db->where('country_id', $id);
        $query = $this->db->get('warehouses_country');

        if ($query->num_rows() > 0) {
            return false;
        }

        $this->db->where('country', $id);
        $query = $this->db->get('warehouses');

        if ($query->num_rows() > 0) {
            return false;
        }

        return true;
    }
}
