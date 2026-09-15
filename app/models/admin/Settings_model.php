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
            return $this->db->insert_id();
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
        if (!$this->usesNormalizedPermissions()) {
            return false;
        }

        return $this->getNormalizedGroupPermissionsObject($id);
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
        if (!$this->usesNormalizedPermissions()) {
            return false;
        }

        $permissions = $this->getNormalizedGroupPermissionsArray($id);

        return !empty($permissions) ? [$permissions] : false;
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
        if (!$this->usesNormalizedPermissions()) {
            return false;
        }

        $this->db->trans_start();

        $this->syncNormalizedGroupPermissions($id, $data);

        $this->db->update('users', [
            'show_price' => !empty($data['products-price']) ? 1 : 0,
            'show_cost'  => !empty($data['products-cost']) ? 1 : 0,
        ], ['group_id' => $id]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function normalizedPermissionsAvailable()
    {
        return $this->usesNormalizedPermissions();
    }

    public function getPermissionCatalog()
    {
        if (!$this->usesNormalizedPermissions()) {
            return [];
        }

        $definitions = $this->db
            ->select('id, permission_key, name, module')
            ->order_by('module', 'asc')
            ->order_by('permission_key', 'asc')
            ->get('permission_definitions')
            ->result_array();

        return is_array($definitions) ? $definitions : [];
    }

    private function usesNormalizedPermissions()
    {
        return $this->db->table_exists('permission_definitions') && $this->db->table_exists('permission_assignments');
    }

    private function getNormalizedGroupPermissionsObject($groupId)
    {
        $permissions = $this->getNormalizedGroupPermissionsArray($groupId);
        if (empty($permissions)) {
            return false;
        }

        $object = new stdClass();
        foreach ($permissions as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }

    private function getNormalizedGroupPermissionsArray($groupId)
    {
        $definitions = $this->db
            ->select('id, permission_key')
            ->order_by('id', 'asc')
            ->get('permission_definitions')
            ->result_array();

        if (empty($definitions)) {
            return [];
        }

        $permissions = ['group_id' => (int) $groupId];
        $definitionIds = [];
        foreach ($definitions as $definition) {
            $permissions[$definition['permission_key']] = 0;
            $definitionIds[(int) $definition['id']] = $definition['permission_key'];
        }

        $assignments = $this->db
            ->select('permission_id, access')
            ->where('group_id', (int) $groupId)
            ->where('user_id IS NULL', null, false)
            ->get('permission_assignments')
            ->result_array();

        foreach ($assignments as $assignment) {
            $permissionId = (int) $assignment['permission_id'];
            if (isset($definitionIds[$permissionId])) {
                $permissions[$definitionIds[$permissionId]] = (int) $assignment['access'];
            }
        }

        return $permissions;
    }

    private function syncNormalizedGroupPermissions($groupId, $data = [])
    {
        if (empty($data)) {
            return;
        }

        $keys = array_keys($data);
        $this->ensurePermissionDefinitions($keys);

        $definitions = $this->db
            ->select('id, permission_key')
            ->where_in('permission_key', $keys)
            ->get('permission_definitions')
            ->result_array();

        $definitionMap = [];
        foreach ($definitions as $definition) {
            $definitionMap[$definition['permission_key']] = (int) $definition['id'];
        }

        if (!empty($definitionMap)) {
            $this->db
                ->where('group_id', (int) $groupId)
                ->where('user_id IS NULL', null, false)
                ->where_in('permission_id', array_values($definitionMap))
                ->delete('permission_assignments');
        }

        $now = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($data as $permissionKey => $value) {
            if (empty($definitionMap[$permissionKey]) || empty($value)) {
                continue;
            }

            $batch[] = [
                'permission_id' => $definitionMap[$permissionKey],
                'group_id'      => (int) $groupId,
                'user_id'       => null,
                'access'        => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch('permission_assignments', $batch);
        }
    }

    private function ensurePermissionDefinitions($keys = [])
    {
        $keys = array_values(array_filter(array_unique($keys)));
        if (empty($keys)) {
            return;
        }

        $existing = $this->db
            ->select('permission_key')
            ->where_in('permission_key', $keys)
            ->get('permission_definitions')
            ->result_array();

        $existingKeys = [];
        foreach ($existing as $row) {
            $existingKeys[] = $row['permission_key'];
        }

        $now = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($keys as $key) {
            if (in_array($key, $existingKeys, true)) {
                continue;
            }

            $batch[] = [
                'permission_key' => $key,
                'name'           => ucwords(str_replace(['-', '_'], ' ', $key)),
                'module'         => strpos($key, '-') !== false ? strtok($key, '-') : $key,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch('permission_definitions', $batch);
        }
    }

    private function filterLegacyPermissionColumns($data = [])
    {
        if (!$this->db->table_exists('permissions')) {
            return [];
        }

        $fields = $this->db->list_fields('permissions');
        $allowed = [];
        foreach ($fields as $field) {
            if (in_array($field, ['id', 'group_id'], true)) {
                continue;
            }
            $allowed[$field] = true;
        }

        $filtered = [];
        foreach ($data as $key => $value) {
            if (isset($allowed[$key])) {
                $filtered[$key] = !empty($value) ? 1 : 0;
            }
        }

        return $filtered;
    }

    public function getEffectiveUserPermissions($userId)
    {
        if (!$this->usesNormalizedPermissions()) {
            return false;
        }

        $user = $this->db->select('id, group_id')->get_where('users', ['id' => (int) $userId], 1)->row();
        if (!$user) {
            return false;
        }

        $permissions = $this->getNormalizedGroupPermissionsArray((int) $user->group_id);
        if (empty($permissions)) {
            $permissions = ['group_id' => (int) $user->group_id];
        }

        $overrides = $this->getUserPermissionOverrides($userId);
        if (!empty($overrides)) {
            foreach ($overrides as $key => $value) {
                $permissions[$key] = $value;
            }
        }

        $object = new stdClass();
        foreach ($permissions as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }

    public function getUserPermissionOverrides($userId)
    {
        if (!$this->usesNormalizedPermissions()) {
            return false;
        }

        $rows = $this->db
            ->select('pd.permission_key, pa.access')
            ->from('permission_assignments pa')
            ->join('permission_definitions pd', 'pd.id = pa.permission_id', 'inner')
            ->where('pa.user_id', (int) $userId)
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[$row['permission_key']] = (int) $row['access'];
        }

        return $permissions;
    }

    public function updateUserPermissionOverrides($userId, $data = [])
    {
        if (!$this->usesNormalizedPermissions()) {
            return false;
        }

        $this->db->trans_start();
        $this->syncNormalizedUserPermissions($userId, $data);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    private function syncNormalizedUserPermissions($userId, $data = [])
    {
        if (empty($data)) {
            return;
        }

        $keys = array_keys($data);
        $this->ensurePermissionDefinitions($keys);

        $definitions = $this->db
            ->select('id, permission_key')
            ->where_in('permission_key', $keys)
            ->get('permission_definitions')
            ->result_array();

        $definitionMap = [];
        foreach ($definitions as $definition) {
            $definitionMap[$definition['permission_key']] = (int) $definition['id'];
        }

        if (!empty($definitionMap)) {
            $this->db
                ->where('user_id', (int) $userId)
                ->where_in('permission_id', array_values($definitionMap))
                ->delete('permission_assignments');
        }

        $now = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($data as $permissionKey => $value) {
            if (empty($definitionMap[$permissionKey])) {
                continue;
            }

            $access = is_numeric($value) ? (int) $value : (!empty($value) ? 1 : 0);
            if ($access !== 0 && $access !== 1) {
                $access = 1;
            }

            $batch[] = [
                'permission_id' => $definitionMap[$permissionKey],
                'group_id'      => null,
                'user_id'       => (int) $userId,
                'access'        => $access,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch('permission_assignments', $batch);
        }
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