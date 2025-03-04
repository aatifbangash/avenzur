<?php

defined('BASEPATH') or exit('No direct script access allowed');

class system_settings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('admin');
        }

        $this->lang->admin_load('settings', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('settings_model');

        $this->upload_path        = 'assets/uploads/';
        $this->thumbs_path        = 'assets/uploads/thumbs/';
        $this->image_types        = 'gif|jpg|jpeg|png|tif|webp';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size  = '2048';
    }

    public function add_brand()
    {
        $this->form_validation->set_rules('name', lang('brand_name'), 'trim|required|is_unique[brands.name]|alpha_numeric_spaces');
        $this->form_validation->set_rules('slug', lang('slug'), 'trim|required|is_unique[brands.slug]|alpha_dash');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
            ];

    if ($_FILES['userfile']['size'] > 0) {
        $this->load->library('upload');
        $config['upload_path']   = $this->upload_path;
        $config['allowed_types'] = $this->image_types;
        $config['max_size']      = $this->allowed_file_size;
        $config['max_width']     = $this->Settings->iwidth;
        $config['max_height']    = $this->Settings->iheight;
        $config['overwrite']     = false;
        $config['encrypt_name']  = true;
        $config['max_filename']  = 25;
        $this->upload->initialize($config);
        if (!$this->upload->do_upload()) {
        $error = $this->upload->display_errors();
        $this->session->set_flashdata('error', $error);
        redirect($_SERVER['HTTP_REFERER']);
        }
        $photo         = $this->upload->file_name;
        $data['image'] = $photo;
        $this->load->library('image_lib');
        $config['image_library']  = 'gd2';
        $config['source_image']   = $this->upload_path . $photo;
        $config['new_image']      = $this->thumbs_path . $photo;
        $config['maintain_ratio'] = true;
        $config['width']          = $this->Settings->twidth;
        $config['height']         = $this->Settings->theight;
        $this->image_lib->clear();
        $this->image_lib->initialize($config);
        if (!$this->image_lib->resize()) {
        echo $this->image_lib->display_errors();
        }
        $this->image_lib->clear();

        }



        } elseif ($this->input->post('add_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/brands');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addBrand($data)) {
            $this->session->set_flashdata('message', lang('brand_added'));
            admin_redirect('system_settings/brands');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_brand', $this->data);
        }

    }

    public function add_category()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[categories.slug]|alpha_dash');
        $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('add_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/categories');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategory($data)) {
            $this->session->set_flashdata('message', lang('category_added'));
            admin_redirect('system_settings/categories');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->settings_model->getParentCategories();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_category', $this->data);
        }
    }

    public function add_currency()
    {
        $this->form_validation->set_rules('code', lang('currency_code'), 'trim|is_unique[currencies.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('rate', lang('exchange_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['code'   => $this->input->post('code'),
                'name'        => $this->input->post('name'),
                'rate'        => $this->input->post('rate'),
                'symbol'      => $this->input->post('symbol'),
                'auto_update' => $this->input->post('auto_update') ? $this->input->post('auto_update') : 0,
            ];
        } elseif ($this->input->post('add_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/currencies');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCurrency($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang('currency_added'));
            admin_redirect('system_settings/currencies');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['page_title'] = lang('new_currency');
            $this->load->view($this->theme . 'settings/add_currency', $this->data);
        }
    }

    public function add_customer_group()
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[customer_groups.name]|required');
        $this->form_validation->set_rules('percent', lang('group_percentage'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'percent'   => $this->input->post('percent'),
                'discount'  => $this->input->post('discount'),
            ];
        } elseif ($this->input->post('add_customer_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/customer_groups');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCustomerGroup($data)) {
            $this->session->set_flashdata('message', lang('customer_group_added'));
            admin_redirect('system_settings/customer_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_customer_group', $this->data);
        }
    }

    public function add_expense_category()
    {
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');

        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
            ];
        } elseif ($this->input->post('add_expense_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/expense_categories');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategory($data)) {
            $this->session->set_flashdata('message', lang('expense_category_added'));
            admin_redirect('system_settings/expense_categories');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_expense_category', $this->data);
        }
    }

    public function add_price_group()
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('add_price_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/price_groups');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
            $this->session->set_flashdata('message', lang('price_group_added'));
            admin_redirect('system_settings/price_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_price_group', $this->data);
        }
    }

    public function add_tax_rate()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|is_unique[tax_rates.name]|required');
        $this->form_validation->set_rules('type', lang('type'), 'required');
        $this->form_validation->set_rules('rate', lang('tax_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'code'      => $this->input->post('code'),
                'type'      => $this->input->post('type'),
                'rate'      => $this->input->post('rate'),
            ];
        } elseif ($this->input->post('add_tax_rate')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/tax_rates');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addTaxRate($data)) {
            $this->session->set_flashdata('message', lang('tax_rate_added'));
            admin_redirect('system_settings/tax_rates');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_tax_rate', $this->data);
        }
    }

    public function add_unit()
    {
        $this->form_validation->set_rules('code', lang('unit_code'), 'trim|is_unique[units.code]|required');
        $this->form_validation->set_rules('name', lang('unit_name'), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang('operator'), 'required');
            $this->form_validation->set_rules('operation_value', lang('operation_value'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'name'            => $this->input->post('name'),
                'code'            => $this->input->post('code'),
                'base_unit'       => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                'operator'        => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
            ];
        } elseif ($this->input->post('add_unit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/units');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addUnit($data)) {
            $this->session->set_flashdata('message', lang('unit_added'));
            admin_redirect('system_settings/units');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_unit', $this->data);
        }
    }

    public function add_variant()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|is_unique[variants.name]|required');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('add_variant')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/variants');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addVariant($data)) {
            $this->session->set_flashdata('message', lang('variant_added'));
            admin_redirect('system_settings/variants');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_variant', $this->data);
        }
    }

    public function add_warehouse()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('code'), 'trim|is_unique[warehouses.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('address', lang('address'), 'required');
        $this->form_validation->set_rules('userfile', lang('map_image'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = '2000';
                $config['max_height']    = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    admin_redirect('system_settings/warehouses');
                }

                $map = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/' . $map;
                $config['new_image']      = 'assets/uploads/thumbs/' . $map;
                $config['maintain_ratio'] = true;
                $config['width']          = 76;
                $config['height']         = 76;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            } else {
                $map = null;
            }

            if($this->input->post('type') == 'warehouse'){
                $data = ['code'      => $this->input->post('code'),
                    'name'           => $this->input->post('name'),
                    'phone'          => $this->input->post('phone'),
                    'email'          => $this->input->post('email'),
                    'address'        => $this->input->post('address'),
                    'price_group_id' => $this->input->post('price_group'),
                    'warehouse_type' => $this->input->post('type'),
                    'country'            => $this->input->post('country'),
                    'inventory_ledger'    => $this->input->post('inventory_ledger'),
                ];
            }else if($this->input->post('type') == 'pharmacy'){
                $data = ['code'      => $this->input->post('code'),
                    'name'           => $this->input->post('name'),
                    'phone'          => $this->input->post('phone'),
                    'email'          => $this->input->post('email'),
                    'address'        => $this->input->post('address'),
                    'price_group_id' => $this->input->post('price_group'),
                    'warehouse_type' => $this->input->post('type'),
                    'country'            => $this->input->post('country'),
                    'fund_books_ledger'   => $this->input->post('fund_books_ledger'),
                    'credit_card_ledger'  => $this->input->post('credit_card_ledger'),
                    'cogs_ledger'         => $this->input->post('cogs_ledger'),
                    'inventory_ledger'    => $this->input->post('inventory_ledger'),
                    'sales_ledger'        => $this->input->post('sales_ledger'),
                    'price_difference_ledger'   => $this->input->post('price_difference_ledger'),
                    'discount_ledger'     => $this->input->post('discount_ledger'),
                    'vat_on_sales_ledger' => $this->input->post('vat_on_sales_ledger'),
                ];
            }

        } elseif ($this->input->post('add_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/warehouses');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addWarehouse($data)) {
            $this->session->set_flashdata('message', lang('warehouse_added'));
            admin_redirect('system_settings/warehouses');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['country'] = $this->settings_model->getallCountry();
            $this->load->view($this->theme . 'settings/add_warehouse', $this->data);
        }
    }

    public function backup_database()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->dbutil();
        $prefs = [
            'format'   => 'txt',
            'filename' => 'sma_db_backup.sql',
        ];
        $back    = $this->dbutil->backup($prefs);
        $backup  = &$back;
        $db_name = 'db-backup-on-' . date('Y-m-d-H-i-s') . '.txt';
        $save    = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);
        $this->session->set_flashdata('messgae', lang('db_saved'));
        admin_redirect('system_settings/backups');
    }

    public function backup_files()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $name = 'file-backup-' . date('Y-m-d-H-i-s');
        $this->sma->zip('./', './files/backups/', $name);
        $this->session->set_flashdata('messgae', lang('backup_saved'));
        admin_redirect('system_settings/backups');
        exit();
    }

    public function backups()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->data['files'] = glob('./files/backups/*.zip', GLOB_BRACE);
        $this->data['dbs']   = glob('./files/backups/*.txt', GLOB_BRACE);
        krsort($this->data['files']);
        krsort($this->data['dbs']);
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('backups')]];
        $meta = ['page_title' => lang('backups'), 'bc' => $bc];
        $this->page_construct('settings/backups', $meta, $this->data);
    }

    public function brand_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteBrand($id);
                    }
                    $this->session->set_flashdata('message', lang('brands_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('brands'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('image'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $brand = $this->site->getBrandByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $brand->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $brand->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $brand->image);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'brands_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function brands()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('brands')]];
        $meta                = ['page_title' => lang('brands'), 'bc' => $bc];
        $this->page_construct('settings/brands', $meta, $this->data);
    }
 
    public function specialities()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('specialities')]];
        $meta                = ['page_title' => lang('specialities'), 'bc' => $bc];
        $this->page_construct('settings/specialities', $meta, $this->data);
    }
    public function speciality_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteSpeciality($id);
                    }
                    $this->session->set_flashdata('message', lang('Speciality_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Specialities'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('slug'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('parent'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc              = $this->settings_model->getSpecialityByID($id);
                        $parent_category = '';
                        if ($sc->parent_id) {
                            $pc              = $this->settings_model->getSpecialityByID($sc->parent_id);
                            $parent_category = $pc->code;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->slug);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->image);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $parent_category);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'Specialities_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getSpecialities()
    {
        $print_barcode =''; 
        //$print_barcode = anchor('admin/products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('specialities')}.id as id, {$this->db->dbprefix('specialities')}.image, {$this->db->dbprefix('specialities')}.code, {$this->db->dbprefix('specialities')}.name, {$this->db->dbprefix('specialities')}.slug, s.name as parent", false)
            ->from('specialities')
            ->join('specialities s', 's.id=specialities.parent_id', 'left')
            ->group_by('specialities.id')
            ->add_column('Actions', '<div class="text-center">' . $print_barcode . " <a href='" . admin_url('system_settings/edit_speciality/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_speciality') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_speciality/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_speciality()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('speciality_code'), 'trim|is_unique[specialities.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[specialities.slug]|alpha_dash');
        $this->form_validation->set_rules('userfile', lang('speciality_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('add_speciality')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/specialities');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSpeciality($data)) {
            $this->session->set_flashdata('message', lang('speciality_added'));
            admin_redirect('system_settings/specialities');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->settings_model->getParentSpecialities();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_speciality', $this->data);
        }
    }

    public function edit_speciality($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('speciality_code'), 'trim|required');
        $pr_details = $this->settings_model->getSpecialityByID($id);
        if ($this->input->post('code') != $pr_details->code) {
            $this->form_validation->set_rules('code', lang('speciality_code'), 'required|is_unique[specialities.code]');
        }
        $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
        if ($this->input->post('slug') != $pr_details->slug) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[specialities.slug]');
        }
        $this->form_validation->set_rules('name', lang('speciality_name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('speciality_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('edit_speciality')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/specialities');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSpeciality($id, $data)) {
            $this->session->set_flashdata('message', lang('speciality_updated'));
            admin_redirect('system_settings/specialities');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['speciality']   = $this->settings_model->getSpecialityByID($id);
            $this->data['specialities'] = $this->settings_model->getParentSpecialities();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_speciality', $this->data);
        }
    }
    public function delete_speciality($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->site->getSubSpecialities($id)) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('speciality_has_subcategory')]);
        }

        if ($this->settings_model->deleteSpeciality($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('speciality_deleted')]);
        }
    }

    public function import_specialities()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/specialities');
                }
                $csv       = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles     = array_shift($arrResult);
                $updated    = '';
                $specialities = $subspecialities= [];
                foreach ($arrResult as $key => $value) {
                    $code  = trim($value[0]);
                    $name  = trim($value[1]);
                    $pcode = isset($value[4]) ? trim($value[4]) : null;
                    if ($code && $name) {
                        $speciality = [
                            'code'        => $code,
                            'name'        => $name,
                            'slug'        => isset($value[2]) ? trim($value[2]) : $code,
                            'image'       => isset($value[3]) ? trim($value[3]) : 'no_image.png',
                            'parent_id'   => $pcode,
                            'description' => isset($value[5]) ? trim($value[5]) : null,
                        ];
                        if (!empty($pcode) && ($pspeciality = $this->settings_model->getSpecialityByCode($pcode))) {
                            $speciality['parent_id'] = $pspeciality->id;
                        }
                        if ($c = $this->settings_model->getSpecialityByCode($code)) {
                            $updated .= '<p>' . lang('speciality_updated') . ' (' . $code . ')</p>';
                            $this->settings_model->updateSpeciality($c->id, $speciality);
                        } else {
                            if ($speciality['parent_id']) {
                                $subspecialities[] = $speciality;
                            } else {
                                $specialities[] = $speciality;
                            }
                        }
                    }
                }
            }

            // $this->sma->print_arrays($specialities, $subspecialities);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategories($specialities, $subspecialities)) {
            $this->session->set_flashdata('message', lang('specialities_added') . $updated);
            admin_redirect('system_settings/specialities');
        } else {
            if ((isset($specialities) && empty($specialities)) || (isset($subspecialities) && empty($subspecialities))) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_specialities'));
                }
                admin_redirect('system_settings/specialities');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_specialities', $this->data);
        }
    }

    public function topics()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('topics')]];
        $meta                = ['page_title' => lang('topics'), 'bc' => $bc];
        $this->page_construct('settings/topics', $meta, $this->data);
    }
    public function topic_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTopic($id);
                    }
                    $this->session->set_flashdata('message', lang('Topic_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Topics'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('slug'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('parent'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc              = $this->settings_model->getTopicByID($id);
                        $parent_category = '';
                        if ($sc->parent_id) {
                            $pc              = $this->settings_model->getTopicByID($sc->parent_id);
                            $parent_category = $pc->code;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->slug);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->image);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $parent_category);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'Topics_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getTopics()
    {
        $print_barcode =''; 
        //$print_barcode = anchor('admin/products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('topics')}.id as id, {$this->db->dbprefix('topics')}.image, {$this->db->dbprefix('topics')}.code, {$this->db->dbprefix('topics')}.name, {$this->db->dbprefix('topics')}.slug, s.name as parent", false)
            ->from('topics')
            ->join('topics s', 's.id=topics.parent_id', 'left')
            ->group_by('topics.id')
            ->add_column('Actions', '<div class="text-center">' . $print_barcode . " <a href='" . admin_url('system_settings/edit_topic/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_topic') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_topic/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_topic()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('topic_code'), 'trim|is_unique[topics.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[topics.slug]|alpha_dash');
        $this->form_validation->set_rules('userfile', lang('topic_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('add_topic')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/topics');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addTopic($data)) {
            $this->session->set_flashdata('message', lang('topic_added'));
            admin_redirect('system_settings/topics');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->settings_model->getParentTopics();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_topic', $this->data);
        }
    }

    public function edit_topic($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('topic_code'), 'trim|required');
        $pr_details = $this->settings_model->getTopicByID($id);
        if ($this->input->post('code') != $pr_details->code) {
            $this->form_validation->set_rules('code', lang('topic_code'), 'required|is_unique[topics.code]');
        }
        $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
        if ($this->input->post('slug') != $pr_details->slug) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[topics.slug]');
        }
        $this->form_validation->set_rules('name', lang('topic_name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('topic_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('edit_topic')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/topics');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateTopic($id, $data)) {
            $this->session->set_flashdata('message', lang('topic_updated'));
            admin_redirect('system_settings/topics');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['topic']   = $this->settings_model->getTopicByID($id);
            $this->data['topics'] = $this->settings_model->getParentTopics();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_topic', $this->data);
        }
    }
    public function delete_topic($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->site->getSubTopics($id)) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('topic_has_subcategory')]);
        }

        if ($this->settings_model->deleteTopic($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('topic_deleted')]);
        }
    }

    public function import_topics()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/topics');
                }
                $csv       = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles     = array_shift($arrResult);
                $updated    = '';
                $topics = $subtopics= [];
                foreach ($arrResult as $key => $value) {
                    $code  = trim($value[0]);
                    $name  = trim($value[1]);
                    $pcode = isset($value[4]) ? trim($value[4]) : null;
                    if ($code && $name) {
                        $topic = [
                            'code'        => $code,
                            'name'        => $name,
                            'slug'        => isset($value[2]) ? trim($value[2]) : $code,
                            'image'       => isset($value[3]) ? trim($value[3]) : 'no_image.png',
                            'parent_id'   => $pcode,
                            'description' => isset($value[5]) ? trim($value[5]) : null,
                        ];
                        if (!empty($pcode) && ($ptopic = $this->settings_model->getTopicByCode($pcode))) {
                            $topic['parent_id'] = $ptopic->id;
                        }
                        if ($c = $this->settings_model->getTopicByCode($code)) {
                            $updated .= '<p>' . lang('topic_updated') . ' (' . $code . ')</p>';
                            $this->settings_model->updateTopic($c->id, $topic);
                        } else {
                            if ($topic['parent_id']) {
                                $subtopics[] = $topic;
                            } else {
                                $topics[] = $topic;
                            }
                        }
                    }
                }
            }

            // $this->sma->print_arrays($topics, $subtopics);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategories($topics, $subtopics)) {
            $this->session->set_flashdata('message', lang('topics_added') . $updated);
            admin_redirect('system_settings/topics');
        } else {
            if ((isset($topics) && empty($topics)) || (isset($subtopics) && empty($subtopics))) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_topics'));
                }
                admin_redirect('system_settings/topics');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_topics', $this->data);
        }
    }
    
    public function categories()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('categories')]];
        $meta                = ['page_title' => lang('categories'), 'bc' => $bc];
        $this->page_construct('settings/categories', $meta, $this->data);
    }

    public function category_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang('categories_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('slug'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('parent_category'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc              = $this->settings_model->getCategoryByID($id);
                        $parent_category = '';
                        if ($sc->parent_id) {
                            $pc              = $this->settings_model->getCategoryByID($sc->parent_id);
                            $parent_category = $pc->code;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->slug);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->image);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $parent_category);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function change_logo()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            $this->sma->md();
        }
        $this->load->helper('security');
        $this->form_validation->set_rules('site_logo', lang('site_logo'), 'xss_clean');
        $this->form_validation->set_rules('login_logo', lang('login_logo'), 'xss_clean');
        $this->form_validation->set_rules('biller_logo', lang('biller_logo'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($_FILES['site_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 80;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('site_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $site_logo = $this->upload->file_name;
                $this->db->update('settings', ['logo' => $site_logo], ['setting_id' => 1]);
            }

            if ($_FILES['login_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 80;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('login_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $login_logo = $this->upload->file_name;
                $this->db->update('settings', ['logo2' => $login_logo], ['setting_id' => 1]);
            }

            if ($_FILES['biller_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 80;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('biller_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
            }

            $this->session->set_flashdata('message', lang('logo_uploaded'));
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($this->input->post('upload_logo')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/change_logo', $this->data);
        }
    }

    public function create_group()
    {
        $this->form_validation->set_rules('group_name', lang('group_name'), 'required|alpha_dash|is_unique[groups.name]');

        if ($this->form_validation->run() == true) {
            $data = ['name' => strtolower($this->input->post('group_name')), 'description' => $this->input->post('description')];
        } elseif ($this->input->post('create_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/user_groups');
        }

        if ($this->form_validation->run() == true && ($new_group_id = $this->settings_model->addGroup($data))) {
            $this->session->set_flashdata('message', lang('group_added'));
            admin_redirect('system_settings/permissions/' . $new_group_id);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['group_name'] = [
                'name'  => 'group_name',
                'id'    => 'group_name',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name'),
            ];
            $this->data['description'] = [
                'name'  => 'description',
                'id'    => 'description',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('description'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/create_group', $this->data);
        }
    }

    public function currencies()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('currencies')]];
        $meta = ['page_title' => lang('currencies'), 'bc' => $bc];
        $this->page_construct('settings/currencies', $meta, $this->data);
    }

    public function currency_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang('currencies_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('currencies'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('rate'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCurrencyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->rate);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'currencies_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function customer_group_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCustomerGroup($id);
                    }
                    $this->session->set_flashdata('message', lang('customer_groups_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('group_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('group_percentage'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pg = $this->settings_model->getCustomerGroupByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pg->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pg->percent);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'customer_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_customer_group_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function customer_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('customer_groups')]];
        $meta = ['page_title' => lang('customer_groups'), 'bc' => $bc];
        $this->page_construct('settings/customer_groups', $meta, $this->data);
    }

    public function delete_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        unlink('./files/backups/' . $zipfile . '.zip');
        $this->session->set_flashdata('messgae', lang('backup_deleted'));
        admin_redirect('system_settings/backups');
    }

    public function delete_brand($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->brandHasProducts($id)) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('brand_has_products')]);
        }

        if ($this->settings_model->deleteBrand($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('brand_deleted')]);
        }
    }

    public function delete_category($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->site->getSubCategories($id)) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('category_has_subcategory')]);
        }

        if ($this->settings_model->deleteCategory($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('category_deleted')]);
        }
    }

    public function delete_currency($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->deleteCurrency($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('currency_deleted')]);
        }
    }

    public function delete_customer_group($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->deleteCustomerGroup($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('customer_group_deleted')]);
        }
    }

    public function delete_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        unlink('./files/backups/' . $dbfile . '.txt');
        $this->session->set_flashdata('messgae', lang('db_deleted'));
        admin_redirect('system_settings/backups');
    }

    public function delete_expense_category($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->hasExpenseCategoryRecord($id)) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('category_has_expenses')]);
        }

        if ($this->settings_model->deleteExpenseCategory($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('expense_category_deleted')]);
        }
    }

    public function delete_group($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->checkGroupUsers($id)) {
            $this->session->set_flashdata('error', lang('group_x_b_deleted'));
            admin_redirect('system_settings/user_groups');
        }

        if ($this->settings_model->deleteGroup($id)) {
            $this->session->set_flashdata('message', lang('group_deleted'));
            admin_redirect('system_settings/user_groups');
        }
    }

    public function delete_price_group($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->deletePriceGroup($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('price_group_deleted')]);
        }
    }

    public function delete_tax_rate($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->deleteTaxRate($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('tax_rate_deleted')]);
        }
    }

    public function delete_unit($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->getUnitChildren($id)) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('unit_has_subunit')]);
        }

        if ($this->settings_model->deleteUnit($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('unit_deleted')]);
        }
    }

    public function delete_variant($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->deleteVariant($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('variant_deleted')]);
        }
    }

    public function delete_warehouse($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->settings_model->deleteWarehouse($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('warehouse_deleted')]);
        }
    }

    public function download_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('download');
        force_download('./files/backups/' . $zipfile . '.zip', null);
        exit();
    }

    public function download_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->library('zip');
        $this->zip->read_file('./files/backups/' . $dbfile . '.txt');
        $name = $dbfile . '.zip';
        $this->zip->download($name);
        exit();
    }

    public function edit_brand($id = null)
    {
        $this->form_validation->set_rules('name', lang('brand_name'), 'trim|required|alpha_numeric_spaces');
        $brand_details = $this->site->getBrandByID($id);
        if ($this->input->post('name') != $brand_details->name) {
            $this->form_validation->set_rules('name', lang('brand_name'), 'required|is_unique[brands.name]');
        }
        $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
        if ($this->input->post('slug') != $brand_details->slug) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[brands.slug]');
        }
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();
            }
        } elseif ($this->input->post('edit_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/brands');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateBrand($id, $data)) {
            $this->session->set_flashdata('message', lang('brand_updated'));
            admin_redirect('system_settings/brands');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['brand']    = $brand_details;
            $this->load->view($this->theme . 'settings/edit_brand', $this->data);
        }
    }

    public function edit_category($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|required');
        $pr_details = $this->settings_model->getCategoryByID($id);
        if ($this->input->post('code') != $pr_details->code) {
            $this->form_validation->set_rules('code', lang('category_code'), 'required|is_unique[categories.code]');
        }
        $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
        if ($this->input->post('slug') != $pr_details->slug) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[categories.slug]');
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('edit_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/categories');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCategory($id, $data)) {
            $this->session->set_flashdata('message', lang('category_updated'));
            admin_redirect('system_settings/categories');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['category']   = $this->settings_model->getCategoryByID($id);
            $this->data['categories'] = $this->settings_model->getParentCategories();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_category', $this->data);
        }
    }

    public function edit_currency($id = null)
    {
        $this->form_validation->set_rules('code', lang('currency_code'), 'trim|required');
        $cur_details = $this->settings_model->getCurrencyByID($id);
        if ($this->input->post('code') != $cur_details->code) {
            $this->form_validation->set_rules('code', lang('currency_code'), 'required|is_unique[currencies.code]');
        }
        $this->form_validation->set_rules('name', lang('currency_name'), 'required');
        $this->form_validation->set_rules('rate', lang('exchange_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['code'   => $this->input->post('code'),
                'name'        => $this->input->post('name'),
                'rate'        => $this->input->post('rate'),
                'symbol'      => $this->input->post('symbol'),
                'auto_update' => $this->input->post('auto_update') ? $this->input->post('auto_update') : 0,
            ];
        } elseif ($this->input->post('edit_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/currencies');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCurrency($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('currency_updated'));
            admin_redirect('system_settings/currencies');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currency'] = $this->settings_model->getCurrencyByID($id);
            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_currency', $this->data);
        }
    }

    public function edit_customer_group($id = null)
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|required');
        $pg_details = $this->settings_model->getCustomerGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang('group_name'), 'required|is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('percent', lang('group_percentage'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'percent'   => $this->input->post('percent'),
                'discount'  => $this->input->post('discount'),
            ];
        } elseif ($this->input->post('edit_customer_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/customer_groups');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateCustomerGroup($id, $data)) {
            $this->session->set_flashdata('message', lang('customer_group_updated'));
            admin_redirect('system_settings/customer_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['customer_group'] = $this->settings_model->getCustomerGroupByID($id);

            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_customer_group', $this->data);
        }
    }

    public function edit_expense_category($id = null)
    {
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|required');
        $category = $this->settings_model->getExpenseCategoryByID($id);
        if ($this->input->post('code') != $category->code) {
            $this->form_validation->set_rules('code', lang('category_code'), 'required|is_unique[expense_categories.code]');
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[3]');

        if ($this->form_validation->run() == true) {
            $data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
            ];
        } elseif ($this->input->post('edit_expense_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/expense_categories');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateExpenseCategory($id, $data)) {
            $this->session->set_flashdata('message', lang('expense_category_updated'));
            admin_redirect('system_settings/expense_categories');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['category'] = $category;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_expense_category', $this->data);
        }
    }

    public function edit_group($id)
    {
        if (!$id || empty($id)) {
            admin_redirect('system_settings/user_groups');
        }

        $group = $this->settings_model->getGroupByID($id);

        $this->form_validation->set_rules('group_name', lang('group_name'), 'required|alpha_dash');

        if ($this->form_validation->run() === true) {
            $data         = ['name' => strtolower($this->input->post('group_name')), 'description' => $this->input->post('description')];
            $group_update = $this->settings_model->updateGroup($id, $data);

            if ($group_update) {
                $this->session->set_flashdata('message', lang('group_udpated'));
            } else {
                $this->session->set_flashdata('error', lang('attempt_failed'));
            }
            admin_redirect('system_settings/user_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['group'] = $group;

            $this->data['group_name'] = [
                'name'  => 'group_name',
                'id'    => 'group_name',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name', $group->name),
            ];
            $this->data['group_description'] = [
                'name'  => 'group_description',
                'id'    => 'group_description',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_description', $group->description),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_group', $this->data);
        }
    }

    public function edit_price_group($id = null)
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|required|alpha_numeric_spaces');
        $pg_details = $this->settings_model->getPriceGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang('group_name'), 'required|is_unique[price_groups.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('edit_price_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/price_groups');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePriceGroup($id, $data)) {
            $this->session->set_flashdata('message', lang('price_group_updated'));
            admin_redirect('system_settings/price_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['price_group'] = $pg_details;
            $this->data['id']          = $id;
            $this->data['modal_js']    = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_price_group', $this->data);
        }
    }

    public function edit_tax_rate($id = null)
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $tax_details = $this->settings_model->getTaxRateByID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('type', lang('type'), 'required');
        $this->form_validation->set_rules('rate', lang('tax_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'code'      => $this->input->post('code'),
                'type'      => $this->input->post('type'),
                'rate'      => $this->input->post('rate'),
            ];
        } elseif ($this->input->post('edit_tax_rate')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/tax_rates');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateTaxRate($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('tax_rate_updated'));
            admin_redirect('system_settings/tax_rates');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['tax_rate'] = $this->settings_model->getTaxRateByID($id);

            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_tax_rate', $this->data);
        }
    }

    public function edit_unit($id = null)
    {
        $this->form_validation->set_rules('code', lang('code'), 'trim|required');
        $unit_details = $this->site->getUnitByID($id);
        if ($this->input->post('code') != $unit_details->code) {
            $this->form_validation->set_rules('code', lang('code'), 'required|is_unique[units.code]');
        }
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang('operator'), 'required');
            $this->form_validation->set_rules('operation_value', lang('operation_value'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'name'            => $this->input->post('name'),
                'code'            => $this->input->post('code'),
                'base_unit'       => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                'operator'        => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
            ];
        } elseif ($this->input->post('edit_unit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/units');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateUnit($id, $data)) {
            $this->session->set_flashdata('message', lang('unit_updated'));
            admin_redirect('system_settings/units');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['unit']       = $unit_details;
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->load->view($this->theme . 'settings/edit_unit', $this->data);
        }
    }

    public function edit_variant($id = null)
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $tax_details = $this->settings_model->getVariantByID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[variants.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('edit_variant')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/variants');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateVariant($id, $data)) {
            $this->session->set_flashdata('message', lang('variant_updated'));
            admin_redirect('system_settings/variants');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['variant']  = $tax_details;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_variant', $this->data);
        }
    }

    public function edit_warehouse($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('code'), 'trim|required');
        $wh_details = $this->settings_model->getWarehouseByID($id);
        if ($this->input->post('code') != $wh_details->code) {
            $this->form_validation->set_rules('code', lang('code'), 'required|is_unique[warehouses.code]');
        }
        $this->form_validation->set_rules('address', lang('address'), 'required');
        $this->form_validation->set_rules('map', lang('map_image'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if($this->input->post('type') == 'warehouse'){
                $data = ['code'      => $this->input->post('code'),
                    'name'           => $this->input->post('name'),
                    'phone'          => $this->input->post('phone'),
                    'email'          => $this->input->post('email'),
                    'address'        => $this->input->post('address'),
                    'price_group_id' => $this->input->post('price_group'),
                    'warehouse_type' => $this->input->post('type'),
                    'country'        => $this->input->post('country'),
                    'inventory_ledger'    => $this->input->post('inventory_ledger'),
                ];
            }else if($this->input->post('type') == 'pharmacy'){
                $data = ['code'      => $this->input->post('code'),
                    'name'           => $this->input->post('name'),
                    'phone'          => $this->input->post('phone'),
                    'email'          => $this->input->post('email'),
                    'address'        => $this->input->post('address'),
                    'price_group_id' => $this->input->post('price_group'),
                    'warehouse_type' => $this->input->post('type'),
                    'country'            => $this->input->post('country'),
                    'fund_books_ledger'   => $this->input->post('fund_books_ledger'),
                    'credit_card_ledger'  => $this->input->post('credit_card_ledger'),
                    'cogs_ledger'         => $this->input->post('cogs_ledger'),
                    'inventory_ledger'    => $this->input->post('inventory_ledger'),
                    'sales_ledger'        => $this->input->post('sales_ledger'),
                    'price_difference_ledger'   => $this->input->post('price_difference_ledger'),
                    'discount_ledger'     => $this->input->post('discount_ledger'),
                    'vat_on_sales_ledger' => $this->input->post('vat_on_sales_ledger'),
                ];
            }

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = '2000';
                $config['max_height']    = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    admin_redirect('system_settings/warehouses');
                }

                $data['map'] = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/' . $data['map'];
                $config['new_image']      = 'assets/uploads/thumbs/' . $data['map'];
                $config['maintain_ratio'] = true;
                $config['width']          = 76;
                $config['height']         = 76;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
        } elseif ($this->input->post('edit_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/warehouses');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateWarehouse($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('warehouse_updated'));
            admin_redirect('system_settings/warehouses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouse']    = $this->settings_model->getWarehouseByID($id);
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['id']           = $id;
            $this->data['country'] = $this->settings_model->getallCountry();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_warehouse', $this->data);
        }
    }

    public function email_templates($template = 'credentials')
    {
        $this->form_validation->set_rules('mail_body', lang('mail_message'), 'trim|required');
        $this->load->helper('file');
        $temp_path = is_dir('./themes/' . $this->theme . 'email_templates/');
        $theme     = $temp_path ? $this->theme : 'default';
        if ($this->form_validation->run() == true) {
            $data = $_POST['mail_body'];
            if (write_file('./themes/' . $this->theme . 'email_templates/' . $template . '.html', $data)) {
                $this->session->set_flashdata('message', lang('message_successfully_saved'));
                admin_redirect('system_settings/email_templates#' . $template);
            } else {
                $this->session->set_flashdata('error', lang('failed_to_save_message'));
                admin_redirect('system_settings/email_templates#' . $template);
            }
        } else {
            $this->data['credentials']     = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
            $this->data['sale']            = file_get_contents('./themes/' . $this->theme . 'email_templates/sale.html');
            $this->data['quote']           = file_get_contents('./themes/' . $this->theme . 'email_templates/quote.html');
            $this->data['purchase']        = file_get_contents('./themes/' . $this->theme . 'email_templates/purchase.html');
            $this->data['transfer']        = file_get_contents('./themes/' . $this->theme . 'email_templates/transfer.html');
            $this->data['payment']         = file_get_contents('./themes/' . $this->theme . 'email_templates/payment.html');
            $this->data['forgot_password'] = file_get_contents('./themes/' . $this->theme . 'email_templates/forgot_password.html');
            $this->data['activate_email']  = file_get_contents('./themes/' . $this->theme . 'email_templates/activate_email.html');
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('email_templates')]];
            $meta                          = ['page_title' => lang('email_templates'), 'bc' => $bc];
            $this->page_construct('settings/email_templates', $meta, $this->data);
        }
    }

    public function expense_categories()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('expense_categories')]];
        $meta                = ['page_title' => lang('categories'), 'bc' => $bc];
        $this->page_construct('settings/expense_categories', $meta, $this->data);
    }

    public function expense_category_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang('categories_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCategoryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'expense_categories_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getBrands()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, image, code, name, slug')
            ->from('brands')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_brand/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_brand') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_brand') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_brand/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function getCategories()
    {
        $print_barcode = anchor('admin/products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('categories')}.id as id, {$this->db->dbprefix('categories')}.image, {$this->db->dbprefix('categories')}.code, {$this->db->dbprefix('categories')}.name, {$this->db->dbprefix('categories')}.slug, c.name as parent", false)
            ->from('categories')
            ->join('categories c', 'c.id=categories.parent_id', 'left')
            ->group_by('categories.id')
            ->add_column('Actions', '<div class="text-center">' . $print_barcode . " <a href='" . admin_url('system_settings/edit_category/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_category') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function getCurrencies()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, code, name, rate, symbol')
            ->from('currencies')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_currency/$1') . "' class='tip' title='" . lang('edit_currency') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_currency') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_currency/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getCustomerGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, percent')
            ->from('customer_groups')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_customer_group/$1') . "' class='tip' title='" . lang('edit_customer_group') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_customer_group') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_customer_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getExpenseCategories()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, code, name')
            ->from('expense_categories')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_expense_category/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_expense_category') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_expense_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_expense_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function getPriceGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('price_groups')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/group_product_prices/$1') . "' class='tip' title='" . lang('group_product_prices') . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . admin_url('system_settings/edit_price_group/$1') . "' class='tip' title='" . lang('edit_price_group') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_price_group') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getProductPrices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, PP.price as price ")
            ->from('products')
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column('price', '$1__$2', 'id, price')
            ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

        echo $this->datatables->generate();
    }

    public function getTaxRates()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, code, rate, type')
            ->from('tax_rates')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_tax_rate/$1') . "' class='tip' title='" . lang('edit_tax_rate') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_tax_rate') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_tax_rate/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getUnits()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('units')}.id as id, {$this->db->dbprefix('units')}.code, {$this->db->dbprefix('units')}.name, b.name as base_unit, {$this->db->dbprefix('units')}.operator, {$this->db->dbprefix('units')}.operation_value", false)
            ->from('units')
            ->join('units b', 'b.id=units.base_unit', 'left')
            ->group_by('units.id')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_unit/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_unit') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_unit') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_unit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function getVariants()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('variants')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_variant/$1') . "' class='tip' title='" . lang('edit_variant') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_variant') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_variant/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getWarehouses()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('warehouses')}.id as id, map, code, {$this->db->dbprefix('warehouses')}.name as name, {$this->db->dbprefix('price_groups')}.name as price_group, phone, email, address")
            ->from('warehouses')
            ->join('price_groups', 'price_groups.id=warehouses.price_group_id', 'left')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/add_shelf/$1') . "' class='tip' title='" . lang('Add Shelf') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus\"></i></a>&nbsp;<a href='" . admin_url('system_settings/view_shelf/$1') . "' class='tip' title='" . lang('View Shelf') . "'><i class=\"fa fa-file-text-o\"></i></a>&nbsp;<a href='" . admin_url('system_settings/edit_warehouse/$1') . "' class='tip' title='" . lang('edit_warehouse') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_warehouse') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_warehouse/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function add_shelf($warehouse = null)
    {
        $this->form_validation->set_rules('warehouse_id','Warehouse', 'required');

        if ($this->form_validation->run() == true) {
        $warehouse_id = $this->input->post('warehouse_id');
        $i = sizeof($_POST['shelf_name']);
        for ($r = 0; $r < $i; $r++) 
        {
            $shelf_name = $_POST['shelf_name'][$r];
            $shelf = [
                'warehouse_id' => $warehouse_id,
                'shelf_name'   => $shelf_name
            ];
            $shelves[] = $shelf;
        }
        if ($this->settings_model->addShelf($shelves)) {
            $this->session->set_flashdata('message', $this->lang->line('Shelf Added into Warehouse'));
            admin_redirect('system_settings/warehouses');
        }

        }else{

            $this->data['warehouse_id'] = $warehouse;
            $this->load->view($this->theme . 'settings/add_shelf', $this->data);
        }

           
    }

    public function view_shelf($warehouse = null)
    {
        $this->data['shelves'] = $this->settings_model->getAllShelf($warehouse);
        $this->data['warehouse'] = $this->settings_model->getWarehouseByID($warehouse);

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('Warehouse Shelves')]];
        $meta = ['page_title' => lang('Warehouse Shelves'), 'bc' => $bc];
        $this->page_construct('settings/warehouse_shelf', $meta, $this->data);

    }

    public function delete_warehouse_shelf($id = null,$warehouse = null)
    {
        
        if ($this->settings_model->deletewarehouseShelf($id)) {
            
            $this->session->set_flashdata('message', lang('Country_deleted'));
                 admin_redirect('system_settings/view_shelf/'.$warehouse);
        }
    }



    public function group_product_prices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['error']       = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')],  ['link' => admin_url('system_settings/price_groups'), 'page' => lang('price_groups')], ['link' => '#', 'page' => lang('group_product_prices')]];
        $meta                      = ['page_title' => lang('group_product_prices'), 'bc' => $bc];
        $this->page_construct('settings/group_product_prices', $meta, $this->data);
    }

    public function import_brands()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/brands');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['name', 'code', 'image', 'slug', 'description'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getBrandByName(trim($csv_ct['name']))) {
                        $data[] = [
                            'code'  => trim($csv_ct['code']),
                            'name'  => trim($csv_ct['name']),
                            'image' => trim($csv_ct['image']),
                            'slug' => trim($csv_ct['slug']),
                            'description' => trim($csv_ct['description']),
                        ];
                    }
                }
            }

            // $this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && !empty($data) && $this->settings_model->addBrands($data)) {
            $this->session->set_flashdata('message', lang('brands_added'));
            admin_redirect('system_settings/brands');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_brands', $this->data);
        }
    }

    public function import_categories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/categories');
                }
                $csv       = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles     = array_shift($arrResult);
                $updated    = '';
                $categories = $subcategories = [];
                foreach ($arrResult as $key => $value) {
                    $code  = trim($value[0]);
                    $name  = trim($value[1]);
                    $pcode = isset($value[4]) ? trim($value[4]) : null;
                    if ($code && $name) {
                        $category = [
                            'code'        => $code,
                            'name'        => $name,
                            'slug'        => isset($value[2]) ? trim($value[2]) : $code,
                            'image'       => isset($value[3]) ? trim($value[3]) : 'no_image.png',
                            'parent_id'   => $pcode,
                            'description' => isset($value[5]) ? trim($value[5]) : null,
                        ];
                        if (!empty($pcode) && ($pcategory = $this->settings_model->getCategoryByCode($pcode))) {
                            $category['parent_id'] = $pcategory->id;
                        }
                        if ($c = $this->settings_model->getCategoryByCode($code)) {
                            $updated .= '<p>' . lang('category_updated') . ' (' . $code . ')</p>';
                            $this->settings_model->updateCategory($c->id, $category);
                        } else {
                            if ($category['parent_id']) {
                                $subcategories[] = $category;
                            } else {
                                $categories[] = $category;
                            }
                        }
                    }
                }
            }

            // $this->sma->print_arrays($categories, $subcategories);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategories($categories, $subcategories)) {
            $this->session->set_flashdata('message', lang('categories_added') . $updated);
            admin_redirect('system_settings/categories');
        } else {
            if ((isset($categories) && empty($categories)) || (isset($subcategories) && empty($subcategories))) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_categories'));
                }
                admin_redirect('system_settings/categories');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_categories', $this->data);
        }
    }

    public function import_expense_categories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/expense_categories');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'name'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getExpenseCategoryByCode(trim($csv_ct['code']))) {
                        $data[] = [
                            'code' => trim($csv_ct['code']),
                            'name' => trim($csv_ct['name']),
                        ];
                    }
                }
            }

            // $this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategories($data)) {
            $this->session->set_flashdata('message', lang('categories_added'));
            admin_redirect('system_settings/expense_categories');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_expense_categories', $this->data);
        }
    }

    public function import_subcategories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/categories');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'name', 'category_code', 'image'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $rw = 2;
                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getSubcategoryByCode(trim($csv_ct['code']))) {
                        if ($parent_actegory = $this->settings_model->getCategoryByCode(trim($csv_ct['category_code']))) {
                            $data[] = [
                                'code'        => trim($csv_ct['code']),
                                'name'        => trim($csv_ct['name']),
                                'image'       => trim($csv_ct['image']),
                                'category_id' => $parent_actegory->id,
                            ];
                        } else {
                            $this->session->set_flashdata('error', lang('check_category_code') . ' (' . $csv_ct['category_code'] . '). ' . lang('category_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                            admin_redirect('system_settings/categories');
                        }
                    }
                    $rw++;
                }
            }

            // $this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSubCategories($data)) {
            $this->session->set_flashdata('message', lang('subcategories_added'));
            admin_redirect('system_settings/categories');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_subcategories', $this->data);
        }
    }

    public function add_ledgers(){
        $this->form_validation->set_rules('vat_on_purchase_ledger', lang('Vat On Purchase Ledger'), 'trim|numeric|required');
        $this->form_validation->set_rules('vat_on_sale_ledger', lang('Vat On Sale Ledger'), 'trim|numeric|required');
        $this->form_validation->set_rules('bank_fund_cash_ledger', lang('Bank Fund Cash Ledger'), 'trim|numeric|required');
        $this->form_validation->set_rules('bank_fees_ledger', lang('Bank Fees Ledger'), 'trim|numeric|required');
        $this->form_validation->set_rules('bank_checking_account_ledger', lang('Bank Checking Account Ledger'), 'trim|numeric|required');

        if ($this->form_validation->run() == true) {
            $vat_on_purchase_ledger = $this->input->post('vat_on_purchase_ledger');
            $vat_on_sale_ledger = $this->input->post('vat_on_sale_ledger');
            $bank_fund_cash_ledger = $this->input->post('bank_fund_cash_ledger');
            $bank_fees_ledger = $this->input->post('bank_fees_ledger');
            $bank_checking_account_ledger = $this->input->post('bank_checking_account_ledger');

            $data = [
                'vat_on_purchase_ledger' => $vat_on_purchase_ledger,
                'vat_on_sale_ledger' => $vat_on_sale_ledger,
                'bank_fund_cash_ledger' => $bank_fund_cash_ledger,
                'bank_fees_ledger' => $bank_fees_ledger,
                'bank_checking_account_ledger' => $bank_checking_account_ledger,
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->setLedgers($data)) {
            $this->session->set_flashdata('message', lang('Ledgers Set Successfully'));
            admin_redirect('system_settings/add_ledgers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['settings']        = $this->settings_model->getSettings();
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Accounts Ledgers')]];
            $meta                          = ['page_title' => lang('Accounts Ledgers'), 'bc' => $bc];
            $this->page_construct('settings/add_ledgers', $meta, $this->data);
        }
    }

    public function index()
    {
        $this->load->library('gst');
        $this->form_validation->set_rules('site_name', lang('site_name'), 'trim|required');
        $this->form_validation->set_rules('dateformat', lang('dateformat'), 'trim|required');
        $this->form_validation->set_rules('timezone', lang('timezone'), 'trim|required');
        $this->form_validation->set_rules('mmode', lang('maintenance_mode'), 'trim|required');
        //$this->form_validation->set_rules('logo', lang('logo'), 'trim');
        $this->form_validation->set_rules('iwidth', lang('image_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('iheight', lang('image_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('twidth', lang('thumbnail_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('theight', lang('thumbnail_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('display_all_products', lang('display_all_products'), 'trim|numeric|required');
        $this->form_validation->set_rules('watermark', lang('watermark'), 'trim|required');
        $this->form_validation->set_rules('currency', lang('default_currency'), 'trim|required');
        $this->form_validation->set_rules('email', lang('default_email'), 'trim|required');
        $this->form_validation->set_rules('language', lang('language'), 'trim|required');
        $this->form_validation->set_rules('warehouse', lang('default_warehouse'), 'trim|required');
        $this->form_validation->set_rules('biller', lang('default_biller'), 'trim|required');
        $this->form_validation->set_rules('tax_rate', lang('product_tax'), 'trim|required');
        $this->form_validation->set_rules('tax_rate2', lang('invoice_tax'), 'trim|required');
        $this->form_validation->set_rules('sales_prefix', lang('sales_prefix'), 'trim');
        $this->form_validation->set_rules('quote_prefix', lang('quote_prefix'), 'trim');
        $this->form_validation->set_rules('purchase_prefix', lang('purchase_prefix'), 'trim');
        $this->form_validation->set_rules('transfer_prefix', lang('transfer_prefix'), 'trim');
        $this->form_validation->set_rules('delivery_prefix', lang('delivery_prefix'), 'trim');
        $this->form_validation->set_rules('payment_prefix', lang('payment_prefix'), 'trim');
        $this->form_validation->set_rules('return_prefix', lang('return_prefix'), 'trim');
        $this->form_validation->set_rules('expense_prefix', lang('expense_prefix'), 'trim');
        $this->form_validation->set_rules('detect_barcode', lang('detect_barcode'), 'trim|required');
        $this->form_validation->set_rules('theme', lang('theme'), 'trim|required');
        $this->form_validation->set_rules('rows_per_page', lang('rows_per_page'), 'trim|required');
        $this->form_validation->set_rules('accounting_method', lang('accounting_method'), 'trim|required');
        $this->form_validation->set_rules('product_serial', lang('product_serial'), 'trim|required');
        $this->form_validation->set_rules('product_discount', lang('product_discount'), 'trim|required');
        $this->form_validation->set_rules('bc_fix', lang('bc_fix'), 'trim|numeric|required');
        $this->form_validation->set_rules('protocol', lang('email_protocol'), 'trim|required');
        if ($this->input->post('protocol') == 'smtp') {
            $this->form_validation->set_rules('smtp_host', lang('smtp_host'), 'required');
            $this->form_validation->set_rules('smtp_user', lang('smtp_user'), 'required');
            $this->form_validation->set_rules('smtp_pass', lang('smtp_pass'), 'required');
            $this->form_validation->set_rules('smtp_port', lang('smtp_port'), 'required');
        }
        if ($this->input->post('protocol') == 'sendmail') {
            $this->form_validation->set_rules('mailpath', lang('mailpath'), 'required');
        }
        $this->form_validation->set_rules('decimals', lang('decimals'), 'trim|required');
        $this->form_validation->set_rules('decimals_sep', lang('decimals_sep'), 'trim|required');
        $this->form_validation->set_rules('thousands_sep', lang('thousands_sep'), 'trim|required');
        if ($this->Settings->indian_gst) {
            $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $language = $this->input->post('language');

            if ((file_exists(APPPATH . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'sma_lang.php') && is_dir(APPPATH . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language)) || $language == 'english') {
                $lang = $language;
            } else {
                $this->session->set_flashdata('error', lang('language_x_found'));
                admin_redirect('system_settings');
                $lang = 'english';
            }

            $tax1 = ($this->input->post('tax_rate') != 0) ? 1 : 0;
            $tax2 = ($this->input->post('tax_rate2') != 0) ? 1 : 0;

            $data = ['site_name' => DEMO ? 'Stock Manager Advance' : $this->input->post('site_name'),
                'rows_per_page'  => $this->input->post('rows_per_page'),
                'dateformat'     => $this->input->post('dateformat'),
                'timezone'       => DEMO ? 'Asia/Kuala_Lumpur' : $this->input->post('timezone'),
                'mmode'          => trim($this->input->post('mmode')),
                'iwidth'         => $this->input->post('iwidth'),
                'iheight'        => $this->input->post('iheight'),
                'twidth'         => $this->input->post('twidth'),
                'theight'        => $this->input->post('theight'),
                'watermark'      => $this->input->post('watermark'),
                // 'reg_ver' => $this->input->post('reg_ver'),
                // 'allow_reg' => $this->input->post('allow_reg'),
                // 'reg_notification' => $this->input->post('reg_notification'),
                'accounting_method'    => $this->input->post('accounting_method'),
                'default_email'        => DEMO ? 'noreply@tecdiary.com' : $this->input->post('email'),
                'language'             => $lang,
                'default_warehouse'    => $this->input->post('warehouse'),
                'default_tax_rate'     => $this->input->post('tax_rate'),
                'default_tax_rate2'    => $this->input->post('tax_rate2'),
                'sales_prefix'         => $this->input->post('sales_prefix'),
                'quote_prefix'         => $this->input->post('quote_prefix'),
                'purchase_prefix'      => $this->input->post('purchase_prefix'),
                'transfer_prefix'      => $this->input->post('transfer_prefix'),
                'delivery_prefix'      => $this->input->post('delivery_prefix'),
                'payment_prefix'       => $this->input->post('payment_prefix'),
                'ppayment_prefix'      => $this->input->post('ppayment_prefix'),
                'qa_prefix'            => $this->input->post('qa_prefix'),
                'return_prefix'        => $this->input->post('return_prefix'),
                'returnp_prefix'       => $this->input->post('returnp_prefix'),
                'expense_prefix'       => $this->input->post('expense_prefix'),
                'auto_detect_barcode'  => trim($this->input->post('detect_barcode')),
                'theme'                => trim($this->input->post('theme')),
                'product_serial'       => $this->input->post('product_serial'),
                'customer_group'       => $this->input->post('customer_group'),
                'product_expiry'       => $this->input->post('product_expiry'),
                'product_discount'     => $this->input->post('product_discount'),
                'default_currency'     => $this->input->post('currency'),
                'bc_fix'               => $this->input->post('bc_fix'),
                'tax1'                 => $tax1,
                'tax2'                 => $tax2,
                'overselling'          => $this->input->post('restrict_sale'),
                'reference_format'     => $this->input->post('reference_format'),
                'racks'                => $this->input->post('racks'),
                'attributes'           => $this->input->post('attributes'),
                'restrict_calendar'    => $this->input->post('restrict_calendar'),
                'captcha'              => $this->input->post('captcha'),
                'item_addition'        => $this->input->post('item_addition'),
                'protocol'             => DEMO ? 'mail' : $this->input->post('protocol'),
                'mailpath'             => $this->input->post('mailpath'),
                'smtp_host'            => $this->input->post('smtp_host'),
                'smtp_user'            => $this->input->post('smtp_user'),
                'smtp_port'            => $this->input->post('smtp_port'),
                'smtp_crypto'          => $this->input->post('smtp_crypto') ? $this->input->post('smtp_crypto') : null,
                'decimals'             => $this->input->post('decimals'),
                'decimals_sep'         => $this->input->post('decimals_sep'),
                'thousands_sep'        => $this->input->post('thousands_sep'),
                'default_biller'       => $this->input->post('biller'),
                'invoice_view'         => $this->input->post('invoice_view'),
                'rtl'                  => $this->input->post('rtl'),
                'each_spent'           => $this->input->post('each_spent') ? $this->input->post('each_spent') : null,
                'ca_point'             => $this->input->post('ca_point') ? $this->input->post('ca_point') : null,
                'each_sale'            => $this->input->post('each_sale') ? $this->input->post('each_sale') : null,
                'sa_point'             => $this->input->post('sa_point') ? $this->input->post('sa_point') : null,
                'sac'                  => $this->input->post('sac'),
                'qty_decimals'         => $this->input->post('qty_decimals'),
                'display_all_products' => $this->input->post('display_all_products'),
                'display_symbol'       => $this->input->post('display_symbol'),
                'symbol'               => $this->input->post('symbol'),
                'remove_expired'       => $this->input->post('remove_expired'),
                'barcode_separator'    => $this->input->post('barcode_separator'),
                'set_focus'            => $this->input->post('set_focus'),
                'disable_editing'      => $this->input->post('disable_editing'),
                'price_group'          => $this->input->post('price_group'),
                'barcode_img'          => $this->input->post('barcode_renderer'),
                'update_cost'          => $this->input->post('update_cost'),
                'apis'                 => $this->input->post('apis'),
                'pdf_lib'              => $this->input->post('pdf_lib'),
                'state'                => $this->input->post('state'),
                'use_code_for_slug'    => $this->input->post('use_code_for_slug'),
                'ws_barcode_type'      => $this->input->post('ws_barcode_type'),
                'ws_barcode_chars'     => $this->input->post('ws_barcode_chars'),
                'flag_chars'           => $this->input->post('flag_chars'),
                'item_code_start'      => $this->input->post('item_code_start'),
                'item_code_chars'      => $this->input->post('item_code_chars'),
                'price_start'          => $this->input->post('price_start'),
                'price_chars'          => $this->input->post('price_chars'),
                'price_divide_by'      => $this->input->post('price_divide_by'),
                'weight_start'         => $this->input->post('weight_start'),
                'weight_chars'         => $this->input->post('weight_chars'),
                'weight_divide_by'     => $this->input->post('weight_divide_by'),
                'ksa_qrcode'           => $this->input->post('ksa_qrcode'),
            ];
            if ($this->input->post('smtp_pass')) {
                $data['smtp_pass'] = $this->input->post('smtp_pass');
            }
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSetting($data)) {
            if (!DEMO && TIMEZONE != $data['timezone']) {
                if (!$this->write_index($data['timezone'])) {
                    $this->session->set_flashdata('error', lang('setting_updated_timezone_failed'));
                    admin_redirect('system_settings');
                }
            }

            $this->session->set_flashdata('message', lang('setting_updated'));
            admin_redirect('system_settings');
        } else {
            $this->data['error']           = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['settings']        = $this->settings_model->getSettings();
            $this->data['currencies']      = $this->settings_model->getAllCurrencies();
            $this->data['date_formats']    = $this->settings_model->getDateFormats();
            $this->data['tax_rates']       = $this->settings_model->getAllTaxRates();
            $this->data['customer_groups'] = $this->settings_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->settings_model->getAllPriceGroups();
            $this->data['warehouses']      = $this->settings_model->getAllWarehouses();
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('system_settings')]];
            $meta                          = ['page_title' => lang('system_settings'), 'bc' => $bc];
            $this->page_construct('settings/index', $meta, $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->sma->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                admin_redirect('system_settings/updates');
            }
        }
        $this->db->update('settings', ['version' => $version, 'update' => 0], ['setting_id' => 1]);
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        admin_redirect('system_settings/updates');
    }

    public function paypal()
    {
        $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
        $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
        if ($this->input->post('active')) {
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        }
        $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
        $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
        $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = ['active'         => $this->input->post('active'),
                'account_email'       => $this->input->post('account_email'),
                'fixed_charges'       => $this->input->post('fixed_charges'),
                'extra_charges_my'    => $this->input->post('extra_charges_my'),
                'extra_charges_other' => $this->input->post('extra_charges_other'),
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePaypal($data)) {
            $this->session->set_flashdata('message', $this->lang->line('paypal_setting_updated'));
            admin_redirect('system_settings/paypal');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['paypal'] = $this->settings_model->getPaypalSettings();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('paypal_settings')]];
            $meta = ['page_title' => lang('paypal_settings'), 'bc' => $bc];
            $this->page_construct('settings/paypal', $meta, $this->data);
        }
    }

    public function permissions($id = null)
    {
        $this->form_validation->set_rules('group', lang('group'), 'is_natural_no_zero');
        if ($this->form_validation->run() == true) {
            $data = [
                'products-index'             => $this->input->post('products-index'),
                'products-edit'              => $this->input->post('products-edit'),
                'products-add'               => $this->input->post('products-add'),
                'products-delete'            => $this->input->post('products-delete'),
                'products-cost'              => $this->input->post('products-cost'),
                'products-price'             => $this->input->post('products-price'),
                'customers-index'            => $this->input->post('customers-index'),
                'customers-edit'             => $this->input->post('customers-edit'),
                'customers-add'              => $this->input->post('customers-add'),
                'customers-delete'           => $this->input->post('customers-delete'),
                'suppliers-index'            => $this->input->post('suppliers-index'),
                'suppliers-edit'             => $this->input->post('suppliers-edit'),
                'suppliers-add'              => $this->input->post('suppliers-add'),
                'suppliers-delete'           => $this->input->post('suppliers-delete'),
                'sales-index'                => $this->input->post('sales-index'),
                'sales-edit'                 => $this->input->post('sales-edit'),
                'sales-add'                  => $this->input->post('sales-add'),
                'sales-delete'               => $this->input->post('sales-delete'),
                'sales-email'                => $this->input->post('sales-email'),
                'sales-pdf'                  => $this->input->post('sales-pdf'),
                'sales-deliveries'           => $this->input->post('sales-deliveries'),
                'sales-edit_delivery'        => $this->input->post('sales-edit_delivery'),
                'sales-add_delivery'         => $this->input->post('sales-add_delivery'),
                'sales-delete_delivery'      => $this->input->post('sales-delete_delivery'),
                'sales-email_delivery'       => $this->input->post('sales-email_delivery'),
                'sales-pdf_delivery'         => $this->input->post('sales-pdf_delivery'),
                'sales-gift_cards'           => $this->input->post('sales-gift_cards'),
                'sales-edit_gift_card'       => $this->input->post('sales-edit_gift_card'),
                'sales-add_gift_card'        => $this->input->post('sales-add_gift_card'),
                'sales-delete_gift_card'     => $this->input->post('sales-delete_gift_card'),
                'sales-coordinator'          => $this->input->post('sales-coordinator'),
                'sales-warehouse_supervisor' => $this->input->post('sales-warehouse_supervisor'),
        'sales-warehouse_supervisor_shipping'=> $this->input->post('sales-warehouse_supervisor_shipping'),
                'sales-accountant'           => $this->input->post('sales-accountant'),
                'sales-quality_supervisor'   => $this->input->post('sales-quality_supervisor'),
                'quotes-index'               => $this->input->post('quotes-index'),
                'quotes-edit'                => $this->input->post('quotes-edit'),
                'quotes-add'                 => $this->input->post('quotes-add'),
                'quotes-delete'              => $this->input->post('quotes-delete'),
                'quotes-email'               => $this->input->post('quotes-email'),
                'quotes-pdf'                 => $this->input->post('quotes-pdf'),
                'purchases-index'            => $this->input->post('purchases-index'),
                'purchases-edit'             => $this->input->post('purchases-edit'),
                'purchases-add'              => $this->input->post('purchases-add'),
                'purchases-delete'           => $this->input->post('purchases-delete'),
                'purchases-email'            => $this->input->post('purchases-email'),
                'purchases-pdf'              => $this->input->post('purchases-pdf'),
                'transfers-index'            => $this->input->post('transfers-index'),
                'transfers-edit'             => $this->input->post('transfers-edit'),
                'transfers-add'              => $this->input->post('transfers-add'),
                'transfers-delete'           => $this->input->post('transfers-delete'),
                'transfers-email'            => $this->input->post('transfers-email'),
                'transfers-pdf'              => $this->input->post('transfers-pdf'),
                'sales-return_sales'         => $this->input->post('sales-return_sales'),
                'reports-quantity_alerts'    => $this->input->post('reports-quantity_alerts'),
                'reports-expiry_alerts'      => $this->input->post('reports-expiry_alerts'),
                'reports-products'           => $this->input->post('reports-products'),
                'reports-daily_sales'        => $this->input->post('reports-daily_sales'),
                'reports-monthly_sales'      => $this->input->post('reports-monthly_sales'),
                'reports-payments'           => $this->input->post('reports-payments'),
                'reports-sales'              => $this->input->post('reports-sales'),
                'reports-purchases'          => $this->input->post('reports-purchases'),
                'reports-customers'          => $this->input->post('reports-customers'),
                'reports-suppliers'          => $this->input->post('reports-suppliers'),
                'reports-staff'              => $this->input->post('reports-staff'),
                'sales-payments'             => $this->input->post('sales-payments'),
                'purchases-payments'         => $this->input->post('purchases-payments'),
                'purchases-expenses'         => $this->input->post('purchases-expenses'),
                'products-adjustments'       => $this->input->post('products-adjustments'),
                'bulk_actions'               => $this->input->post('bulk_actions'),
                'customers-deposits'         => $this->input->post('customers-deposits'),
                'customers-delete_deposit'   => $this->input->post('customers-delete_deposit'),
                'products-barcode'           => $this->input->post('products-barcode'),
                'purchases-return_purchases' => $this->input->post('purchases-return_purchases'),
                'reports-expenses'           => $this->input->post('reports-expenses'),
                'reports-daily_purchases'    => $this->input->post('reports-daily_purchases'),
                'reports-monthly_purchases'  => $this->input->post('reports-monthly_purchases'),
                'products-stock_count'       => $this->input->post('products-stock_count'),
                'edit_price'                 => $this->input->post('edit_price'),
                'returns-index'              => $this->input->post('returns-index'),
                'returns-edit'               => $this->input->post('returns-edit'),
                'returns-add'                => $this->input->post('returns-add'),
                'returns-delete'             => $this->input->post('returns-delete'),
                'returns-email'              => $this->input->post('returns-email'),
                'returns-pdf'                => $this->input->post('returns-pdf'),
                'reports-tax'                => $this->input->post('reports-tax'),
                'stock_request_view'         => $this->input->post('stock_request_view'),
                'stock_request_approval'     => $this->input->post('stock_request_approval'),
                'truck_registration_view'    => $this->input->post('truck_registration_view'),
                'purchase_manager'           => $this->input->post('purchase_manager'),
                'purchase_receiving_supervisor' => $this->input->post('purchase_receiving_supervisor'),
                'purchase_warehouse_supervisor' => $this->input->post('purchase_warehouse_supervisor'),
                'purchase_supervisor'        => $this->input->post('purchase_supervisor'),
                'accountant'        => $this->input->post('accountant'),
                'stock_pharmacist'        => $this->input->post('stock_pharmacist'),
                'stock_warehouse_supervisor'        => $this->input->post('stock_warehouse_supervisor'),
                'transfer_pharmacist'        => $this->input->post('transfer_pharmacist'),
                'transfer_warehouse_supervisor'        => $this->input->post('transfer_warehouse_supervisor'),
                'blog_view'                  => $this->input->post('blog_view'),
                'blog_edit'                  => $this->input->post('blog_edit'),
                'blog_add'                   => $this->input->post('blog_add')
                
            ];

            if (POS) {
                $data['pos-index'] = $this->input->post('pos-index');
            }

            //$this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePermissions($id, $data)) {
            $this->session->set_flashdata('message', lang('group_permissions_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['id']    = $id;
            $this->data['p']     = $this->settings_model->getGroupPermissions($id);
            $this->data['group'] = $this->settings_model->getGroupByID($id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('group_permissions')]];
            $meta = ['page_title' => lang('group_permissions'), 'bc' => $bc];
            $this->page_construct('settings/permissions', $meta, $this->data);
        }
    }

    public function price_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('price_groups')]];
        $meta = ['page_title' => lang('price_groups'), 'bc' => $bc];
        $this->page_construct('settings/price_groups', $meta, $this->data);
    }

    public function product_group_price_actions($group_id)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->setProductPriceForPriceGroup($id, $group_id, $this->input->post('price' . $id));
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_updated'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteProductGroupPrice($id, $group_id);
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('group_name'));
                    $row   = 2;
                    $group = $this->settings_model->getPriceGroupByID($group_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getProductGroupPriceByPID($id, $group_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->price);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $group->name);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'price_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_price_group_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function restore_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $file = './files/backups/' . $zipfile . '.zip';
        $this->sma->unzip($file, './');
        $this->session->set_flashdata('success', lang('files_restored'));
        admin_redirect('system_settings/backups');
        exit();
    }

    public function restore_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $file = file_get_contents('./files/backups/' . $dbfile . '.txt');
        // $this->db->conn_id->multi_query($file);
        mysqli_multi_query($this->db->conn_id, $file);
        $this->db->conn_id->close();
        admin_redirect('logout/db');
    }

    public function skrill()
    {
        $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
        $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
        if ($this->input->post('active')) {
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        }
        $this->form_validation->set_rules('secret_word', $this->lang->line('secret_word'), 'trim');
        $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
        $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
        $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = ['active'         => $this->input->post('active'),
                'secret_word'         => $this->input->post('secret_word'),
                'account_email'       => $this->input->post('account_email'),
                'fixed_charges'       => $this->input->post('fixed_charges'),
                'extra_charges_my'    => $this->input->post('extra_charges_my'),
                'extra_charges_other' => $this->input->post('extra_charges_other'),
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSkrill($data)) {
            $this->session->set_flashdata('message', $this->lang->line('skrill_setting_updated'));
            admin_redirect('system_settings/skrill');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['skrill'] = $this->settings_model->getSkrillSettings();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('skrill_settings')]];
            $meta = ['page_title' => lang('skrill_settings'), 'bc' => $bc];
            $this->page_construct('settings/skrill', $meta, $this->data);
        }
    }
       public function directPay()
    {
        $this->form_validation->set_rules('merchant_id', $this->lang->line('merchant_id'), 'trim');
        $this->form_validation->set_rules('authentication_token', $this->lang->line('authentication_token'), 'trim');
        // if ($this->input->post('active')) {
        //     $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        // }
        $this->form_validation->set_rules('payment_link', $this->lang->line('payment_link'), 'trim');
        $this->form_validation->set_rules('refund_link', $this->lang->line('refund_link'), 'trim');
        $this->form_validation->set_rules('test_payment_link', $this->lang->line('test_payment_link'), 'trim');
        $this->form_validation->set_rules('test_refund_link', $this->lang->line('test_refund_link'), 'trim');

        $this->form_validation->set_rules('activation', $this->lang->line('activation'), 'trim');
        $this->form_validation->set_rules('version', $this->lang->line('version'), 'trim');
        $this->form_validation->set_rules('currencyISOCode', $this->lang->line('currencyISOCode'), 'trim');
        $this->form_validation->set_rules('payment_message_id', $this->lang->line('payment_message_id'), 'trim');
          $this->form_validation->set_rules('refund_message_id', $this->lang->line('refund_message_id'), 'trim');   
          
          
        if ($this->form_validation->run() == true) {
            $data = ['merchant_id'         => $this->input->post('merchant_id'),
                'authentication_token'         => $this->input->post('authentication_token'),
                'payment_link'       => $this->input->post('payment_link'),
                'refund_link'       => $this->input->post('refund_link'),
                'test_Merchant_id'  => $this->input->post('test_merchant_id'),
                'test_auth_token'   => $this->input->post('test_auth_token'),
                'test_payment_link'    => $this->input->post('test_payment_link'),
                'test_refund_link' => $this->input->post('test_refund_link'),
                 'activation' => $this->input->post('activation'),
                   'version' => $this->input->post('version'),
                     'currencyISOCode' => $this->input->post('currencyISOCode'),
                       'payment_message_id' => $this->input->post('payment_message_id'),
                         'refund_message_id' => $this->input->post('refund_message_id'),
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatedirectPay($data)) {
            $this->session->set_flashdata('message', $this->lang->line('DirectPay_setting_updated'));
            admin_redirect('system_settings/directPay');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['directpay'] = $this->settings_model->getdirectPay();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('Direct_Pay')]];
            $meta = ['page_title' => lang('Direct_Pay'), 'bc' => $bc];
            $this->page_construct('settings/directpay', $meta, $this->data);
        }
    }

public function aramex()
    {
        $this->form_validation->set_rules('line1', $this->lang->line('line1'), 'trim');
        $this->form_validation->set_rules('line2', $this->lang->line('line2'), 'trim');
        // if ($this->input->post('active')) {
        //     $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        // }
        $this->form_validation->set_rules('city', $this->lang->line('city'), 'trim');
        $this->form_validation->set_rules('postal_code', $this->lang->line('postal_code'), 'trim');
        $this->form_validation->set_rules('country_code', $this->lang->line('country_code'), 'trim');
        $this->form_validation->set_rules('person_name', $this->lang->line('person_name'), 'trim');

        $this->form_validation->set_rules('company_name', $this->lang->line('company_name'), 'trim');
        $this->form_validation->set_rules('landline_number', $this->lang->line('landline_number'), 'trim');
        $this->form_validation->set_rules('cell_number', $this->lang->line('cell_number'), 'trim');
        $this->form_validation->set_rules('Email', $this->lang->line('Email'), 'trim');
          $this->form_validation->set_rules('account_entity', $this->lang->line('account_entity'), 'trim');   
            $this->form_validation->set_rules('account_number', $this->lang->line('account_number'), 'trim');
        $this->form_validation->set_rules('account_pin', $this->lang->line('account_pin'), 'trim');
        $this->form_validation->set_rules('user_name', $this->lang->line('user_name'), 'trim');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'trim');
          $this->form_validation->set_rules('version', $this->lang->line('version'), 'trim');   
          
          
        if ($this->form_validation->run() == true) {
            $data = ['line1'         => $this->input->post('line1'),
                'line2'         => $this->input->post('line2'),
                'city'       => $this->input->post('city'),
                'postal_code'       => $this->input->post('postal_code'),
                'country_code'    => $this->input->post('country_code'),
                'person_name' => $this->input->post('person_name'),
                 'company_name' => $this->input->post('company_name'),
                   'landline_number' => $this->input->post('landline_number'),
                     'cell_number' => $this->input->post('cell_number'),
                       'Email' => $this->input->post('Email'),
                         'account_entity' => $this->input->post('account_entity'),
                           'account_number'         => $this->input->post('account_number'),
                'account_pin'       => $this->input->post('account_pin'),
                'user_name'       => $this->input->post('user_name'),
                'password'    => $this->input->post('password'),
                'version' => $this->input->post('version'),
                'activation' => $this->input->post('activation'),
                 'shippment_url' => $this->input->post('shippment_url'),
                   'pickup_url' => $this->input->post('pickup_url'),
                     'cell_number' => $this->input->post('cell_number'),
                       'test_shippment_url' => $this->input->post('test_shippment_url'),
                         'test_pickup_url' => $this->input->post('test_pickup_url'),
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatearamex($data)) {
            $this->session->set_flashdata('message', $this->lang->line('Aramex_setting_updated'));
            admin_redirect('system_settings/aramex');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['aramex'] = $this->settings_model->getaramex();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('Aramex')]];
            $meta = ['page_title' => lang('Aramex'), 'bc' => $bc];
            $this->page_construct('settings/aramex', $meta, $this->data);
        }
    }

    public function tax_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTaxRate($id);
                    }
                    $this->session->set_flashdata('message', lang('tax_rates_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('type'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->settings_model->getTaxRateByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tax->rate);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($tax->type == 1) ? lang('percentage') : lang('fixed'));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'tax_rates_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function tax_rates()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('tax_rates')]];
        $meta = ['page_title' => lang('tax_rates'), 'bc' => $bc];
        $this->page_construct('settings/tax_rates', $meta, $this->data);
    }

    public function unit_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteUnit($id);
                    }
                    $this->session->set_flashdata('message', lang('units_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('base_unit'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('operator'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('operation_value'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $unit = $this->site->getUnitByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $unit->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $unit->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $unit->base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $unit->operator);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $unit->operation_value);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'units_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function units()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('units')]];
        $meta = ['page_title' => lang('units'), 'bc' => $bc];
        $this->page_construct('settings/units', $meta, $this->data);
    }

    public function update_prices_csv($group_id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('message', lang('disabled_in_demo'));
                admin_redirect('welcome');
            }

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/group_product_prices/' . $group_id);
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = ['code', 'price'];

                $final = [];

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                        $data[] = [
                            'product_id'     => $product->id,
                            'price'          => $csv_pr['price'],
                            'price_group_id' => $group_id,
                        ];
                    } else {
                        $this->session->set_flashdata('message', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('system_settings/group_product_prices/' . $group_id);
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            $this->settings_model->updateGroupPrices($data);
            $this->session->set_flashdata('message', lang('price_updated'));
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        } else {
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['group']    = $this->site->getPriceGroupByID($group_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/update_price', $this->data);
        }
    }

    public function update_product_group_price($group_id = null)
    {
        if (!$group_id) {
            $this->sma->send_json(['status' => 0]);
        }

        $product_id = $this->input->post('product_id', true);
        $price      = $this->input->post('price', true);
        if (!empty($product_id) && !empty($price)) {
            if ($this->settings_model->setProductPriceForPriceGroup($product_id, $group_id, $price)) {
                $this->sma->send_json(['status' => 1]);
            }
        }

        $this->sma->send_json(['status' => 0]);
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_rules('purchase_code', lang('purchase_code'), 'required');
        $this->form_validation->set_rules('envato_username', lang('envato_username'), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('settings', ['purchase_code' => $this->input->post('purchase_code', true), 'envato_username' => $this->input->post('envato_username', true)], ['setting_id' => 1]);
            admin_redirect('system_settings/updates');
        } else {
            $fields = ['version' => $this->Settings->version, 'code' => $this->Settings->purchase_code, 'username' => $this->Settings->envato_username, 'site' => base_url()];
            $this->load->helper('update');
            $protocol              = is_https() ? 'https://' : 'http://';
            $updates               = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('updates')]];
            $meta                  = ['page_title' => lang('updates'), 'bc' => $bc];
            $this->page_construct('settings/updates', $meta, $this->data);
        }
    }

    public function user_groups()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('auth');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['groups'] = $this->settings_model->getGroups();
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('groups')]];
        $meta                 = ['page_title' => lang('groups'), 'bc' => $bc];
        $this->page_construct('settings/user_groups', $meta, $this->data);
    }

    public function variants()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('variants')]];
        $meta = ['page_title' => lang('variants'), 'bc' => $bc];
        $this->page_construct('settings/variants', $meta, $this->data);
    }

    public function warehouse_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteWarehouse($id);
                    }
                    $this->session->set_flashdata('message', lang('warehouses_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('warehouses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('city'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $wh = $this->settings_model->getWarehouseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $wh->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $wh->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $wh->address);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $wh->city);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'warehouses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_warehouse_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function warehouses()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('warehouses')]];
        $meta = ['page_title' => lang('warehouses'), 'bc' => $bc];
        $this->page_construct('settings/warehouses', $meta, $this->data);
    }

    public function write_index($timezone)
    {
        $template_path = FCPATH . 'assets/config_dumps/index.php';
        $output_path   = FCPATH . 'index.php';
        $index_file    = file_get_contents($template_path);
        $new           = str_replace('%TIMEZONE%', $timezone, $index_file);
        $handle        = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new)) {
                @chmod($output_path, 0644);
                return true;
            }
            @chmod($output_path, 0644);
            return false;
        }
        @chmod($output_path, 0644);
        return false;
    }
    
         public function allCountry()
    {
         $this->data['all_data'] = $this->settings_model->getallCountry();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('country')], ['link' => '#', 'page' => lang('country')]];
        $meta = ['page_title' => lang('country'), 'bc' => $bc];
        $this->page_construct('settings/list_country', $meta, $this->data);
       // $this->page_construct('pages/blog_page', $meta, $this->data);
    }
 


    public function add_country()
    {
         $this->form_validation->set_rules('name', lang('name'), 'required|max_length[60]');
        $this->form_validation->set_rules('code', lang('code'), 'required|max_length[2]');
        
         if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
            ];
         }
//         if($this->input->post('add_country'))
// 		{
// 		    $data['name']=$this->input->post('name');
// 			$data['code']=$this->input->post('code');
			
// 			$response=$this->settings_model->insertCountry($data);
// 			if($response==true){
// 			        echo "Records Saved Successfully";
// 			}
// 			else{
// 					echo "Insert error !";
// 			}
// 		}
        
    elseif ($this->input->post('add_country')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/add_country');
        }

        if ($this->form_validation->run() == true && $this->settings_model->insertCountry($data)) {
            $this->session->set_flashdata('message', lang('Country_added'));
            admin_redirect('system_settings/allCountry');
        } else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('add_country')]];
            $meta                = ['page_title' => lang('add_country'), 'bc' => $bc];
            
            $this->page_construct('settings/add_country', $meta, $this->data);
          //  $this->load->view("blog/add_blog",$result);
        }
    }
     public function delete_country($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
         
         if($this->settings_model->checkCountryDeletion($id))
         {
            if ($this->settings_model->deleteCountry($id)) 
            {
                 $this->session->set_flashdata('message', lang('Country_deleted'));
                 admin_redirect('system_settings/allCountry');
            }
            
         }else
         {
             $this->session->set_flashdata('error', lang('Check the following: Product, warehouse or warehouses with countries. using this country. Please remove this contry from them first.'));
             admin_redirect('system_settings/allCountry');
         }
        
        
    }
    
    
     public function edit_country($id = null)
    {
        $page = $this->settings_model->getCountryByID($id);
        $this->form_validation->set_rules('name', lang('name'), 'required|max_length[60]');
        $this->form_validation->set_rules('code', lang('code'), 'required|max_length[2]');
       
        
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'       => $this->input->post('code'),
               
            ];
        }    

        
        

        if ($this->form_validation->run() == true && $this->settings_model->updateCountry($id, $data)) {
            $this->session->set_flashdata('message', lang('Country_updated'));
            admin_redirect('system_settings/allCountry');
        } else {
            $this->data['page']  = $page;
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('edit_blog')]];
            $meta                = ['page_title' => lang('edit_blog'), 'bc' => $bc];
            $this->page_construct('settings/edit_country', $meta, $this->data);
        }
    }
           public function warehousesCountry()
    {
         $this->data['warehouses']    = $this->site->getAllWarehouses();
         $this->data['countries'] = $this->settings_model->getallCountry();
$this->data['warehousecountries'] = $this->site->getallWCountry();
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('warehouses_with_country')], ['link' => '#', 'page' => lang('warehouses_with_country')]];
        $meta = ['page_title' => lang('warehouses_with_country'), 'bc' => $bc];
        $this->page_construct('settings/warehousecountry', $meta, $this->data);
       // $this->page_construct('pages/blog_page', $meta, $this->data);
    }
    
    public function add_warehouse_country()
    {
        
        if($this->input->post('add_warehouse'))
		{
		    $countries=$this->input->post('country_id');
		    $warehousese = $this->input->post('warehouses_id');
		    
		    $data = array();
		    for($i=0; $i < count($countries); $i++)
		    {
		       $cwdata = array(
		           'country_id' => $countries[$i],
		           'warehouses_id' => $warehousese[$i]
		           );  
		       
		       $data[] = $cwdata;
		    }
		}   
		
		$response=$this->settings_model->insertWareCountry($data);
		 
			if($response==true){
			        $this->session->set_flashdata('message', 'Warehouse with countries added');
			}
		
			else{
					 $this->session->set_flashdata('error', 'Error! Warehouse with countries not added');
					// var_dump($data);
			}    
		   
			/*
		
		
    }
		 if($this->input->post('add_warehouse_sa'))
	    	{
		    $data['country_id']=$this->input->post('country_id');
			$data['warehouses_id']=$this->input->post('warehouses_id');
			
			$response=$this->settings_model->insertWareCountry($data);
			if($response==true){
			        echo "Records Saved Successfully";
			}
			else{
					echo "Insert error !";
			}
		}*/
	
        admin_redirect('system_settings/warehousesCountry');  
    }
}
