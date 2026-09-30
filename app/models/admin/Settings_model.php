<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_newsletter_subscription($newletterEmail){
        $q = $this->db->get_where('sma_newsletter_subscribers', ['email' => $newletterEmail], 1);
        if ($q->num_rows() > 0) {
            return 'exists';
        }else{
            $data = array(
                'email' =>  $newletterEmail,
                'status' => 1,
                'date_created' => date('Y-m-d')
            );
            if ($this->db->insert('sma_newsletter_subscribers', $data)) {
                return 'added';
            }
            return 'failed';
        }
    }

    public function addBrand($data)
    {
        if ($this->db->insert('brands', $data)) {
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

    public function setLedgers($data){
        $this->db->where('setting_id', '1');
        if ($this->db->update('settings', $data)) {
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
        if ($this->db->insert('categories', $data)) {
            return true;
        }
        return false;
    }
    public function addSpeciality($data)
    {
        if ($this->db->insert('specialities', $data)) {
            return true;
        }
        return false;
    }
    public function addTopic($data)
    {
        if ($this->db->insert('topics', $data)) {
            return true;
        }
        return false;
    }

    public function addCurrency($data)
    {
        if ($this->db->insert('currencies', $data)) {
            return true;
        }
        return false;
    }

    public function addCustomerGroup($data)
    {
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
        if ($this->db->insert('expense_categories', $data)) {
            return true;
        }
        return false;
    }

    public function addGroup($data)
    {
        if ($this->db->insert('groups', $data)) {
            $gid = $this->db->insert_id();
            $this->db->insert('permissions', ['group_id' => $gid]);
            return $gid;
        }
        return false;
    }

    public function addPriceGroup($data)
    {
        if ($this->db->insert('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function addTaxRate($data)
    {
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function addUnit($data)
    {
        if ($this->db->insert('units', $data)) {
            return true;
        }
        return false;
    }

    public function addVariant($data)
    {
        if ($this->db->insert('variants', $data)) {
            return true;
        }
        return false;
    }

    public function addWarehouse($data)
    {
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
    public function deleteSpeciality($id)
    {
        if ($this->db->delete('specialities', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function deleteTopic($id)
    {
        if ($this->db->delete('topics', ['id' => $id])) {
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

    public function getGroupByName($name)
    {
        $this->db->where('name', $name);
        $query = $this->db->get('groups');
        return $query->row();
    }

    public function getUserByGroupId($id)
    {
        $q = $this->db->get_where('users', ['group_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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
        $q = $this->db->get_where('brands', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByCode($code)
    {
        $q = $this->db->get_where('categories', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByID($id)
    {
        $q = $this->db->get_where('categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSpecialityByID($id)
    {
        $q = $this->db->get_where('specialities', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSpecialityByCode($code)
    {
        $q = $this->db->get_where('specialities', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTopicByID($id)
    {
        $q = $this->db->get_where('topics', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTopicByCode($code)
    {
        $q = $this->db->get_where('topics', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }




    public function getCurrencyByID($id)
    {
        $q = $this->db->get_where('currencies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCustomerGroupByID($id)
    {
        $q = $this->db->get_where('customer_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDateFormats()
    {
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
        $this->db->where('id >', 4);
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
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getParentSpecialities()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $q = $this->db->get('specialities');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getParentTopics()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $q = $this->db->get('topics');
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
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getdirectPay()
    {
        $q = $this->db->get('directpay');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getaramex()
    {
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

    public function updateCategory($id, $data = [])
    {
        if ($this->db->update('categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateSpeciality($id, $data = [])
    {
        if ($this->db->update('specialities', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updateTopic($id, $data = [])
    {
        if ($this->db->update('topics', $data, ['id' => $id])) {
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
        $logo = ['logo2' => $photo];
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateLogo($photo)
    {
        $logo = ['logo' => $photo];
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updatePaypal($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return false;
    }

    public function updatePermissions($id, $data = [])
    {
        $this->ensure_permission_columns();
        $data = $this->mergeFinanceReportPosts($data);
        $data = $this->applyFinancePermissionDefaults($data);

        // Refresh field cache after possible ALTERs
        $prefixed = $this->db->dbprefix('permissions');
        if (isset($this->db->data_cache['field_names'][$prefixed])) {
            unset($this->db->data_cache['field_names'][$prefixed]);
        }
        if (isset($this->db->data_cache['field_names']['permissions'])) {
            unset($this->db->data_cache['field_names']['permissions']);
        }

        // Only write columns that actually exist (avoids whole UPDATE failing)
        $fields = $this->db->list_fields('permissions');
        $allowed = array_fill_keys($fields, true);
        unset($allowed['id'], $allowed['group_id']);

        $clean = [];
        foreach ($data as $k => $v) {
            if (!isset($allowed[$k])) {
                continue;
            }
            $clean[$k] = $v ? 1 : 0;
        }

        if (empty($clean)) {
            return false;
        }

        $ok = $this->db->update('permissions', $clean, ['group_id' => (int) $id]);
        if ($ok) {
            $this->db->update('users', [
                'show_price' => $clean['products-price'] ?? 0,
                'show_cost'  => $clean['products-cost'] ?? 0,
            ], ['group_id' => (int) $id]);
            return true;
        }
        return false;
    }

    /**
     * Read finance report checkboxes from raw POST (more reliable than input->post for these flags).
     */
    public function mergeFinanceReportPosts(array $data)
    {
        $keys = [
            'finance-report-gl-statement',
            'finance-report-trial-balance',
            'finance-report-general-ledger',
            'finance-report-vat',
            'finance-chart-accounts-module',
            'finance-chart-accounts',
            'finance-chart-accounts-add',
            'finance-chart-accounts-edit',
            'finance-chart-accounts-delete',
            'finance-chart-accounts-export',
            'finance-jv-module',
            'finance-jv',
            'finance-jv-add',
            'finance-jv-edit',
            'finance-jv-delete',
            'finance-jv-export',
            'finance-jv-templates-module',
            'finance-jv-templates',
            'finance-jv-templates-add',
            'finance-jv-templates-edit',
            'finance-jv-templates-delete',
            'finance-jv-templates-export',
        ];
        foreach ($keys as $key) {
            // Prefer raw POST so checkbox "1" is never missed
            if (array_key_exists($key, $_POST)) {
                $val = $_POST[$key];
                // hidden+checkbox can arrive as array; use last value
                if (is_array($val)) {
                    $val = end($val);
                }
                $data[$key] = ($val === '1' || $val === 1 || $val === true) ? 1 : 0;
            } elseif (!array_key_exists($key, $data)) {
                $data[$key] = 0;
            } else {
                $data[$key] = !empty($data[$key]) ? 1 : 0;
            }
        }
        // Parent reports flag
        if (array_key_exists('finance-view-reports', $_POST)) {
            $val = $_POST['finance-view-reports'];
            if (is_array($val)) {
                $val = end($val);
            }
            $data['finance-view-reports'] = ($val === '1' || $val === 1 || $val === true) ? 1 : 0;
        }
        return $data;
    }

    /**
     * Ensure newer permission columns exist on sma_permissions (then sync to user_permissions).
     */
    public function ensure_permission_columns()
    {
        if (!$this->db->table_exists('permissions')) {
            return false;
        }

        $permissions = $this->db->dbprefix('permissions');
        $required = [
            // Charts of Accounts
            'finance-chart-accounts-module',
            'finance-chart-accounts-add',
            'finance-chart-accounts-edit',
            'finance-chart-accounts-delete',
            'finance-chart-accounts-export',
            // JL Entries
            'finance-jv-module',
            'finance-jv-add',
            'finance-jv-edit',
            'finance-jv-delete',
            'finance-jv-export',
            // JV Templates
            'finance-jv-templates-module',
            'finance-jv-templates-add',
            'finance-jv-templates-edit',
            'finance-jv-templates-delete',
            'finance-jv-templates-export',
            // Finance reports (per report)
            'finance-report-gl-statement',
            'finance-report-trial-balance',
            'finance-report-general-ledger',
            'finance-report-vat',
            // Services — Rasd Notifications
            'rasd-notifications',
            'rasd-notifications-module',
            'rasd-notifications-add',
            'rasd-notifications-edit',
            'rasd-notifications-delete',
            // Inventory / Warehouse module parents
            'transfers-module',
            'inventory-reports',
            'sales-deliveries-module',
        ];

        $existing = $this->db->list_fields('permissions');
        $existing_map = array_fill_keys($existing, true);
        $added = false;

        foreach ($required as $field) {
            if (isset($existing_map[$field])) {
                continue;
            }
            // Use SHOW COLUMNS as source of truth (list_fields can be stale)
            $check = $this->db->query("SHOW COLUMNS FROM `{$permissions}` LIKE " . $this->db->escape($field));
            if ($check && $check->num_rows() > 0) {
                $existing_map[$field] = true;
                continue;
            }
            $this->db->query("ALTER TABLE `{$permissions}` ADD COLUMN `{$field}` TINYINT(1) NOT NULL DEFAULT 0");
            $existing_map[$field] = true;
            $added = true;
        }

        if ($added) {
            if (isset($this->db->data_cache['field_names'][$permissions])) {
                unset($this->db->data_cache['field_names'][$permissions]);
            }
            if (isset($this->db->data_cache['field_names']['permissions'])) {
                unset($this->db->data_cache['field_names']['permissions']);
            }
            // Backfill CRUD/export from existing view flags (first-time only; keeps current access)
            if ($this->db->field_exists('finance-chart-accounts', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `finance-chart-accounts-module` = IF(`finance-chart-accounts-module` = 0, `finance-chart-accounts`, `finance-chart-accounts-module`),
                    `finance-chart-accounts-add` = IF(`finance-chart-accounts-add` = 0, `finance-chart-accounts`, `finance-chart-accounts-add`),
                    `finance-chart-accounts-edit` = IF(`finance-chart-accounts-edit` = 0, `finance-chart-accounts`, `finance-chart-accounts-edit`),
                    `finance-chart-accounts-delete` = IF(`finance-chart-accounts-delete` = 0, `finance-chart-accounts`, `finance-chart-accounts-delete`),
                    `finance-chart-accounts-export` = IF(`finance-chart-accounts-export` = 0, `finance-chart-accounts`, `finance-chart-accounts-export`)
                ");
            }
            if ($this->db->field_exists('finance-jv', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `finance-jv-module` = IF(`finance-jv-module` = 0, `finance-jv`, `finance-jv-module`),
                    `finance-jv-add` = IF(`finance-jv-add` = 0, `finance-jv`, `finance-jv-add`),
                    `finance-jv-edit` = IF(`finance-jv-edit` = 0, `finance-jv`, `finance-jv-edit`),
                    `finance-jv-delete` = IF(`finance-jv-delete` = 0, `finance-jv`, `finance-jv-delete`),
                    `finance-jv-export` = IF(`finance-jv-export` = 0, `finance-jv`, `finance-jv-export`)
                ");
            }
            if ($this->db->field_exists('finance-jv-templates', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `finance-jv-templates-module` = IF(`finance-jv-templates-module` = 0, `finance-jv-templates`, `finance-jv-templates-module`),
                    `finance-jv-templates-add` = IF(`finance-jv-templates-add` = 0, `finance-jv-templates`, `finance-jv-templates-add`),
                    `finance-jv-templates-edit` = IF(`finance-jv-templates-edit` = 0, `finance-jv-templates`, `finance-jv-templates-edit`),
                    `finance-jv-templates-delete` = IF(`finance-jv-templates-delete` = 0, `finance-jv-templates`, `finance-jv-templates-delete`),
                    `finance-jv-templates-export` = IF(`finance-jv-templates-export` = 0, `finance-jv-templates`, `finance-jv-templates-export`)
                ");
            }
            // Split legacy finance-view-reports into per-report flags
            if ($this->db->field_exists('finance-view-reports', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `finance-report-gl-statement` = IF(`finance-report-gl-statement` = 0 AND `finance-view-reports` = 1, 1, `finance-report-gl-statement`),
                    `finance-report-trial-balance` = IF(`finance-report-trial-balance` = 0 AND `finance-view-reports` = 1, 1, `finance-report-trial-balance`),
                    `finance-report-general-ledger` = IF(`finance-report-general-ledger` = 0 AND `finance-view-reports` = 1, 1, `finance-report-general-ledger`),
                    `finance-report-vat` = IF(`finance-report-vat` = 0 AND `finance-view-reports` = 1, 1, `finance-report-vat`)
                ");
            }
            // Backfill Rasd CRUD from legacy rasd-notifications view flag
            if ($this->db->field_exists('rasd-notifications', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `rasd-notifications-module` = IF(`rasd-notifications-module` = 0, `rasd-notifications`, `rasd-notifications-module`),
                    `rasd-notifications-add` = IF(`rasd-notifications-add` = 0, `rasd-notifications`, `rasd-notifications-add`),
                    `rasd-notifications-edit` = IF(`rasd-notifications-edit` = 0, `rasd-notifications`, `rasd-notifications-edit`),
                    `rasd-notifications-delete` = IF(`rasd-notifications-delete` = 0, `rasd-notifications`, `rasd-notifications-delete`)
                ");
            }
            // Backfill module parents from existing view flags
            if ($this->db->field_exists('transfers-index', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `transfers-module` = IF(`transfers-module` = 0, `transfers-index`, `transfers-module`)
                ");
            }
            if ($this->db->field_exists('sales-deliveries', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET
                    `sales-deliveries-module` = IF(`sales-deliveries-module` = 0, `sales-deliveries`, `sales-deliveries-module`)
                ");
            }
            // Inventory reports parent from any existing inventory report flag
            if ($this->db->field_exists('inventory-reports', 'permissions')) {
                $this->db->query("UPDATE `{$permissions}` SET `inventory-reports` = 1
                    WHERE `inventory-reports` = 0 AND (
                        IFNULL(`report-stock`,0) = 1 OR IFNULL(`reports-item-movement`,0) = 1
                        OR IFNULL(`reports-revenue`,0) = 1 OR IFNULL(`reports-purchase`,0) = 1
                        OR IFNULL(`reports-transfer`,0) = 1 OR IFNULL(`reports-inventory-tb`,0) = 1
                    )
                ");
            }
        }

        $this->ensure_user_permissions_table();
        return true;
    }

    /**
     * Derive legacy finance-view / finance-view-reports from granular flags on save.
     */
    public function applyFinancePermissionDefaults(array $data)
    {
        $report_keys = [
            'finance-report-gl-statement',
            'finance-report-trial-balance',
            'finance-report-general-ledger',
            'finance-report-vat',
        ];

        // Parent and children are independent — never auto-select children from parent
        if (array_key_exists('finance-view-reports', $_POST)) {
            $parent_raw = $_POST['finance-view-reports'];
            if (is_array($parent_raw)) {
                $parent_raw = end($parent_raw);
            }
            $data['finance-view-reports'] = ($parent_raw === '1' || $parent_raw === 1 || $parent_raw === true) ? 1 : 0;
        } else {
            $data['finance-view-reports'] = !empty($data['finance-view-reports']) ? 1 : 0;
        }

        foreach ($report_keys as $k) {
            if (array_key_exists($k, $_POST)) {
                $val = $_POST[$k];
                if (is_array($val)) {
                    $val = end($val);
                }
                $data[$k] = ($val === '1' || $val === 1 || $val === true) ? 1 : 0;
            } else {
                $data[$k] = !empty($data[$k]) ? 1 : 0;
            }
        }

        $any_report = !empty($data['finance-view-reports']);
        foreach ($report_keys as $k) {
            if (!empty($data[$k])) {
                $any_report = true;
                break;
            }
        }

        $any_finance = $any_report
            || !empty($data['finance-chart-accounts'])
            || !empty($data['finance-chart-accounts-module'])
            || !empty($data['finance-chart-accounts-add'])
            || !empty($data['finance-chart-accounts-edit'])
            || !empty($data['finance-chart-accounts-delete'])
            || !empty($data['finance-chart-accounts-export'])
            || !empty($data['finance-jv'])
            || !empty($data['finance-jv-module'])
            || !empty($data['finance-jv-add'])
            || !empty($data['finance-jv-edit'])
            || !empty($data['finance-jv-delete'])
            || !empty($data['finance-jv-export'])
            || !empty($data['finance-jv-templates'])
            || !empty($data['finance-jv-templates-module'])
            || !empty($data['finance-jv-templates-add'])
            || !empty($data['finance-jv-templates-edit'])
            || !empty($data['finance-jv-templates-delete'])
            || !empty($data['finance-jv-templates-export']);

        $data['finance-view'] = $any_finance ? 1 : 0;
        return $data;
    }

    /**
     * Create sma_user_permissions if missing (clone of permissions, keyed by user_id).
     * Also adds any newer permission columns that exist on sma_permissions but not yet here.
     */
    public function ensure_user_permissions_table()
    {
        $permissions = $this->db->dbprefix('permissions');
        $user_permissions = $this->db->dbprefix('user_permissions');

        if (!$this->db->table_exists('user_permissions')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$user_permissions}` LIKE `{$permissions}`");

            if ($this->db->field_exists('group_id', 'user_permissions')) {
                $this->db->query("ALTER TABLE `{$user_permissions}` CHANGE `group_id` `user_id` INT(11) NOT NULL");
                $idx = $this->db->query("SHOW INDEX FROM `{$user_permissions}` WHERE Key_name = 'group_id'");
                if ($idx && $idx->num_rows() > 0) {
                    $this->db->query("ALTER TABLE `{$user_permissions}` DROP INDEX `group_id`");
                }
            }

            $uidx = $this->db->query("SHOW INDEX FROM `{$user_permissions}` WHERE Key_name = 'user_id'");
            if (!$uidx || $uidx->num_rows() === 0) {
                $this->db->query("ALTER TABLE `{$user_permissions}` ADD UNIQUE KEY `user_id` (`user_id`)");
            }
        }

        $this->sync_user_permissions_columns();

        return $this->db->table_exists('user_permissions');
    }

    /**
     * Copy missing columns from sma_permissions → sma_user_permissions
     * (table was cloned once; new GP columns like products-export need to be added later).
     */
    public function sync_user_permissions_columns()
    {
        if (!$this->db->table_exists('permissions') || !$this->db->table_exists('user_permissions')) {
            return false;
        }

        $permissions = $this->db->dbprefix('permissions');
        $user_permissions = $this->db->dbprefix('user_permissions');
        $added = false;

        $src = $this->db->query("SHOW FULL COLUMNS FROM `{$permissions}`");
        if (!$src || $src->num_rows() === 0) {
            return false;
        }

        $existing = $this->db->list_fields('user_permissions');
        $existing_map = array_fill_keys($existing, true);

        foreach ($src->result() as $col) {
            $field = $col->Field;
            if ($field === 'id' || $field === 'group_id' || $field === 'user_id') {
                continue;
            }
            if (isset($existing_map[$field])) {
                continue;
            }

            $null = (strtoupper($col->Null) === 'YES') ? 'NULL' : 'NOT NULL';
            $default = '';
            if ($col->Default !== null) {
                $default = ' DEFAULT ' . $this->db->escape($col->Default);
            } elseif (strtoupper($col->Null) === 'YES') {
                $default = ' DEFAULT NULL';
            } elseif (preg_match('/^(tinyint|int|smallint|mediumint|bigint|decimal|float|double)/i', $col->Type)) {
                $default = ' DEFAULT 0';
            }

            $extra = '';
            if (!empty($col->Comment)) {
                $extra .= ' COMMENT ' . $this->db->escape($col->Comment);
            }

            $sql = "ALTER TABLE `{$user_permissions}` ADD COLUMN `{$field}` {$col->Type} {$null}{$default}{$extra}";
            $this->db->query($sql);
            $existing_map[$field] = true;
            $added = true;
        }

        // CI caches list_fields(); clear so subsequent reads see new columns
        if ($added && isset($this->db->data_cache['field_names'][$user_permissions])) {
            unset($this->db->data_cache['field_names'][$user_permissions]);
        }
        if ($added && isset($this->db->data_cache['field_names']['user_permissions'])) {
            unset($this->db->data_cache['field_names']['user_permissions']);
        }

        return true;
    }

    public function getUserPermissions($user_id)
    {
        $this->ensure_user_permissions_table();
        $q = $this->db->get_where('user_permissions', ['user_id' => (int) $user_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function userHasCustomPermissions($user_id)
    {
        return (bool) $this->getUserPermissions($user_id);
    }

    /**
     * Build permission flag array from POST using current permission columns.
     * Only includes columns that exist on user_permissions (after sync).
     */
    public function permissionFlagsFromPost()
    {
        $this->ensure_permission_columns();
        $this->ensure_user_permissions_table();
        // Refresh field list after possible ALTERs
        if (isset($this->db->data_cache['field_names'])) {
            unset($this->db->data_cache['field_names'][$this->db->dbprefix('user_permissions')]);
            unset($this->db->data_cache['field_names']['user_permissions']);
        }
        $fields = $this->db->list_fields('user_permissions');
        $data = [];
        foreach ($fields as $field) {
            if ($field === 'id' || $field === 'group_id' || $field === 'user_id') {
                continue;
            }
            if (array_key_exists($field, $_POST)) {
                $val = $_POST[$field];
                if (is_array($val)) {
                    $val = end($val);
                }
                $data[$field] = ($val === '1' || $val === 1 || $val === true) ? 1 : 0;
            } else {
                $data[$field] = 0;
            }
        }
        $data = $this->mergeFinanceReportPosts($data);
        return $this->applyFinancePermissionDefaults($data);
    }

    public function updateUserPermissions($user_id, $data = [])
    {
        $this->ensure_user_permissions_table();
        $user_id = (int) $user_id;
        unset($data['id'], $data['group_id'], $data['user_id']);
        $data['user_id'] = $user_id;

        $existing = $this->getUserPermissions($user_id);
        if ($existing) {
            unset($data['user_id']);
            $ok = $this->db->update('user_permissions', $data, ['user_id' => $user_id]);
        } else {
            $ok = $this->db->insert('user_permissions', $data);
        }

        if ($ok) {
            $price = isset($data['products-price']) ? $data['products-price'] : ($existing->{'products-price'} ?? 0);
            $cost = isset($data['products-cost']) ? $data['products-cost'] : ($existing->{'products-cost'} ?? 0);
            // Re-read after upsert for accurate flags
            $row = $this->getUserPermissions($user_id);
            if ($row) {
                $this->db->update('users', [
                    'show_price' => !empty($row->{'products-price'}) ? 1 : 0,
                    'show_cost'  => !empty($row->{'products-cost'}) ? 1 : 0,
                ], ['id' => $user_id]);
            }
            return true;
        }
        return false;
    }

    public function deleteUserPermissions($user_id)
    {
        $this->ensure_user_permissions_table();
        return $this->db->delete('user_permissions', ['user_id' => (int) $user_id]);
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
        $this->db->where('setting_id', '1');
        if ($this->db->update('settings', $data)) {
            return true;
        }
        return false;
    }

    public function updateSkrill($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return false;
    }
     public function updatedirectPay($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('directpay', $data)) {
            return true;
        }
        return false;
    }

   public function updatearamex($data)
    {
        $this->db->where('id', '1');
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
    public function insertCountry($data){
        
        if ($this->db->insert('countries', $data)) {
            return true;
        }
        return false;
    
	}
public function getallCountry(){

        $query = $this->db->get('countries');
        return $query->result();

	}

    public function getCountries(){

        $query = $this->db->query('select * from sma_countries');
        return $query->result();

    }

    public function getCities($id){

        $query = $this->db->query('select id, name from sma_cities where city_id = ' . $id);
        return $query->result_array();

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
        public function insertWareCountry($data){
        
        $this->db->empty_table('warehouses_country');
        if ($this->db->insert_batch('warehouses_country', $data))
        {
            return true;
        }
        return false;
    
	}
	 public function get_countryId($country){
        
       
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
