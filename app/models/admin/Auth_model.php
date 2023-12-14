<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public $_cache_user_in_group = [];

    public $_ion_like = [];

    public $_ion_limit = null;

    public $_ion_offset = null;

    public $_ion_order = null;

    public $_ion_order_by = null;

    public $_ion_select = [];

    public $_ion_where = [];

    public $activation_code;

    public $forgotten_password_code;

    public $identity;

    public $new_password;

    public $tables = [];

    protected $_cache_groups = [];

    protected $_ion_hooks;

    protected $error_end_delimiter;

    protected $error_start_delimiter;

    protected $errors;

    protected $messages;

    protected $response = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->config('ion_auth', true);

        //initialize db tables data
        $this->tables = $this->config->item('tables', 'ion_auth');

        //initialize data
        $this->identity_column = $this->config->item('identity', 'ion_auth');
        $this->store_salt      = $this->config->item('store_salt', 'ion_auth');
        $this->salt_length     = $this->config->item('salt_length', 'ion_auth');
        $this->join            = $this->config->item('join', 'ion_auth');

        //initialize hash method options (Bcrypt)
        $this->hash_method    = $this->config->item('hash_method', 'ion_auth');
        $this->default_rounds = $this->config->item('default_rounds', 'ion_auth');
        $this->random_rounds  = $this->config->item('random_rounds', 'ion_auth');
        $this->min_rounds     = $this->config->item('min_rounds', 'ion_auth');
        $this->max_rounds     = $this->config->item('max_rounds', 'ion_auth');

        //initialize messages and error
        $this->messages    = [];
        $this->errors      = [];
        $delimiters_source = $this->config->item('delimiters_source', 'ion_auth');

        //load the error delimeters either from the config file or use what's been supplied to form validation
        if ($delimiters_source === 'form_validation') {
            //load in delimiters from form_validation
            //to keep this simple we'll load the value using reflection since these properties are protected
            $this->load->library('form_validation');
            $form_validation_class = new ReflectionClass('CI_Form_validation');

            $error_prefix = $form_validation_class->getProperty('_error_prefix');
            $error_prefix->setAccessible(true);
            $this->error_start_delimiter   = $error_prefix->getValue($this->form_validation);
            $this->message_start_delimiter = $this->error_start_delimiter;

            $error_suffix = $form_validation_class->getProperty('_error_suffix');
            $error_suffix->setAccessible(true);
            $this->error_end_delimiter   = $error_suffix->getValue($this->form_validation);
            $this->message_end_delimiter = $this->error_end_delimiter;
        } else {
            //use delimiters from config
            $this->message_start_delimiter = $this->config->item('message_start_delimiter', 'ion_auth');
            $this->message_end_delimiter   = $this->config->item('message_end_delimiter', 'ion_auth');
            $this->error_start_delimiter   = $this->config->item('error_start_delimiter', 'ion_auth');
            $this->error_end_delimiter     = $this->config->item('error_end_delimiter', 'ion_auth');
        }

        //initialize our hooks object
        $this->_ion_hooks = new stdClass;

        //load the bcrypt class if needed
        if ($this->hash_method == 'bcrypt') {
            if ($this->random_rounds) {
                $rand   = rand($this->min_rounds, $this->max_rounds);
                $rounds = ['rounds' => $rand];
            } else {
                $rounds = ['rounds' => $this->default_rounds];
            }

            $this->load->library('bcrypt', $rounds);
        }

        $this->trigger_events('model_constructor');
    }

    protected function _call_hook($event, $name)
    {
        if (isset($this->_ion_hooks->{$event}[$name]) && method_exists($this->_ion_hooks->{$event}[$name]->class, $this->_ion_hooks->{$event}[$name]->method)) {
            $hook = $this->_ion_hooks->{$event}[$name];

            return call_user_func_array([$hook->class, $hook->method], $hook->arguments);
        }

        return false;
    }

    protected function _filter_data($table, $data)
    {
        $filtered_data = [];
        $columns       = $this->db->list_fields($table);

        if (is_array($data)) {
            foreach ($columns as $column) {
                if (array_key_exists($column, $data)) {
                    $filtered_data[$column] = $data[$column];
                }
            }
        }

        return $filtered_data;
    }

    protected function _prepare_ip($ip_address)
    {
        if ($this->db->platform() === 'postgre' || $this->db->platform() === 'sqlsrv' || $this->db->platform() === 'mssql' || $this->db->platform() === 'mysqli' || $this->db->platform() === 'mysql') {
            return $ip_address;
        }
        return inet_pton($ip_address);
    }

    public function activate($id, $code = false)
    {
        $this->trigger_events('pre_activate');

        if ($code !== false) {
            $query = $this->db->select($this->identity_column)
                ->where('activation_code', $code)
                ->limit(1)
                ->get($this->tables['users']);

            $result = $query->row();

            if ($query->num_rows() !== 1) {
                $this->trigger_events(['post_activate', 'post_activate_unsuccessful']);
                $this->set_error('activate_unsuccessful');
                return false;
            }

            $identity = $result->{$this->identity_column};
            $data     = ['activation_code' => null, 'active' => '1'];

            $this->trigger_events('extra_where');
            if ($this->db->update($this->tables['users'], $data, [$this->identity_column => $identity])) {
                $this->trigger_events(['post_activate', 'post_activate_successful']);
                $this->set_message('activate_successful');
                return true;
            }
            $this->trigger_events(['post_activate', 'post_activate_unsuccessful']);
            $this->set_error('activate_unsuccessful');
            return false;
        }
        $user = $this->user($id)->row();
        $data = ['activation_code' => null, 'active' => '1'];
        $this->trigger_events('extra_where');
        return $this->db->update($this->tables['users'], $data, ['id' => $id]);
        return false;
    }

    public function change_password($identity, $old, $new)
    {
        $this->trigger_events('pre_change_password');

        $this->trigger_events('extra_where');

        $query = $this->db->select('id, password, salt')
            ->where($this->identity_column, $identity)
            ->limit(1)
            ->get($this->tables['users']);

        if ($query->num_rows() !== 1) {
            $this->trigger_events(['post_change_password', 'post_change_password_unsuccessful']);
            $this->set_error('password_change_unsuccessful');
            return false;
        }

        $user = $query->row();

        $old_password_matches = $this->hash_password_db($user->id, $old);

        if ($old_password_matches === true) {
            //store the new password and reset the remember code so all remembered instances have to re-login
            $hashed_new_password = $this->hash_password($new, $user->salt);
            $data                = [
                'password'      => $hashed_new_password,
                'remember_code' => null,
            ];

            $this->trigger_events('extra_where');

            $successfully_changed_password_in_db = $this->db->update($this->tables['users'], $data, [$this->identity_column => $identity]);
            if ($successfully_changed_password_in_db) {
                $this->trigger_events(['post_change_password', 'post_change_password_successful']);
                $this->set_message('password_change_successful');
            } else {
                $this->trigger_events(['post_change_password', 'post_change_password_unsuccessful']);
                $this->set_error('password_change_unsuccessful');
            }

            return $successfully_changed_password_in_db;
        }
        $this->set_error('old_password_wrong');

        $this->set_error('password_change_unsuccessful');
        return false;
    }

    public function clear_forgotten_password_code($code)
    {
        if (empty($code)) {
            return false;
        }

        $this->db->where('forgotten_password_code', $code);

        if ($this->db->count_all_results($this->tables['users']) > 0) {
            $data = [
                'forgotten_password_code' => null,
                'forgotten_password_time' => null,
            ];

            $this->db->update($this->tables['users'], $data, ['forgotten_password_code' => $code]);

            return true;
        }

        return false;
    }

    public function clear_login_attempts($identity, $expire_period = 86400)
    {
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $ip_address = $this->_prepare_ip($this->input->ip_address());

            $this->db->where(['ip_address' => $ip_address, 'login' => $identity]);
            // Purge obsolete login attempts
            $this->db->or_where('time <', time() - $expire_period, false);

            return $this->db->delete($this->tables['login_attempts']);
        }
        return false;
    }

    public function create_group($group_name = false, $group_description = '', $additional_data = [])
    {
        // bail if the group name was not passed
        if (!$group_name) {
            $this->set_error('group_name_required');
            return false;
        }

        // bail if the group name already exists
        $existing_group = $this->db->get_where($this->tables['groups'], ['name' => $group_name])->num_rows();
        if ($existing_group !== 0) {
            $this->set_error('group_already_exists');
            return false;
        }

        $data = ['name' => $group_name, 'description' => $group_description];

        //filter out any data passed that doesnt have a matching column in the groups table
        //and merge the set group data and the additional data
        if (!empty($additional_data)) {
            $data = array_merge($this->_filter_data($this->tables['groups'], $additional_data), $data);
        }

        $this->trigger_events('extra_group_set');

        // insert the new group
        $this->db->insert($this->tables['groups'], $data);
        $group_id = $this->db->insert_id();

        // report success
        $this->set_message('group_creation_successful');
        // return the brand new group id
        return $group_id;
    }

    public function deactivate($id = null)
    {
        $this->trigger_events('deactivate');

        if (!isset($id)) {
            $this->set_error('deactivate_unsuccessful');
            return false;
        }

        $activation_code       = sha1(md5(microtime()));
        $this->activation_code = $activation_code;

        $data = [
            'activation_code' => $activation_code,
            'active'          => 0,
        ];

        $this->trigger_events('extra_where');
        $this->db->update($this->tables['users'], $data, ['id' => $id]);

        $return = $this->db->affected_rows() == 1;
        if ($return) {
            $this->set_message('deactivate_successful');
        } else {
            $this->set_error('deactivate_unsuccessful');
        }
        return $return;
    }

    public function delete_group($group_id = false)
    {
        // bail if mandatory param not set
        if (!$group_id || empty($group_id)) {
            return false;
        }

        $this->trigger_events('pre_delete_group');

        $this->db->trans_begin();

        // remove all users from this group
        //$this->db->delete($this->tables['users_groups'], array($this->join['groups'] => $group_id));
        // remove the group itself
        $this->db->delete($this->tables['groups'], ['id' => $group_id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->trigger_events(['post_delete_group', 'post_delete_group_unsuccessful']);
            $this->set_error('group_delete_unsuccessful');
            return false;
        }

        $this->db->trans_commit();

        $this->trigger_events(['post_delete_group', 'post_delete_group_successful']);
        $this->set_message('group_delete_successful');
        return true;
    }

    public function delete_user($id)
    {
        $this->trigger_events('pre_delete_user');

        $this->db->trans_begin();

        // remove user from groups
        //$this->remove_from_group(NULL, $id);

        // delete user from users table should be placed after remove from group
        $this->db->delete($this->tables['users'], ['id' => $id]);

        // if user does not exist in database then it returns FALSE else removes the user from groups
        if ($this->db->affected_rows() == 0) {
            return false;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->trigger_events(['post_delete_user', 'post_delete_user_unsuccessful']);
            $this->set_error('delete_unsuccessful');
            return false;
        }

        $this->db->trans_commit();

        $this->trigger_events(['post_delete_user', 'post_delete_user_successful']);
        $this->set_message('delete_successful');
        return true;
    }

    public function email_check($email = '')
    {
        $this->trigger_events('email_check');

        if (empty($email)) {
            return false;
        }

        $this->trigger_events('extra_where');

        return $this->db->where('email', $email)
            ->count_all_results($this->tables['users']) > 0;
    }

    public function errors()
    {
        $_output = '';
        foreach ($this->errors as $error) {
            $errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
            $_output .= $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
        }

        return $_output;
    }

    public function errors_array($langify = true)
    {
        if ($langify) {
            $_output = [];
            foreach ($this->errors as $error) {
                $errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
                $_output[] = $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
            }
            return $_output;
        }
        return $this->errors;
    }

    public function forgotten_password($identity)
    {
        if (empty($identity)) {
            $this->trigger_events(['post_forgotten_password', 'post_forgotten_password_unsuccessful']);
            return false;
        }

        //All some more randomness
        $activation_code_part = '';
        if (function_exists('openssl_random_pseudo_bytes')) {
            $activation_code_part = openssl_random_pseudo_bytes(128);
        }

        for ($i = 0; $i < 1024; $i++) {
            $activation_code_part = sha1($activation_code_part . mt_rand() . microtime());
        }

        $key = $this->hash_code($activation_code_part . $identity);

        $this->forgotten_password_code = $key;

        $this->trigger_events('extra_where');

        $update = [
            'forgotten_password_code' => $key,
            'forgotten_password_time' => time(),
        ];

        $this->db->update($this->tables['users'], $update, [$this->identity_column => $identity]);

        $return = $this->db->affected_rows() == 1;

        if ($return) {
            $this->trigger_events(['post_forgotten_password', 'post_forgotten_password_successful']);
        } else {
            $this->trigger_events(['post_forgotten_password', 'post_forgotten_password_unsuccessful']);
        }

        return $return;
    }

    public function forgotten_password_complete($code, $salt = false)
    {
        $this->trigger_events('pre_forgotten_password_complete');

        if (empty($code)) {
            $this->trigger_events(['post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful']);
            return false;
        }

        $profile = $this->where('forgotten_password_code', $code)->users()->row(); //pass the code to profile

        if ($profile) {
            if ($this->config->item('forgot_password_expiration', 'ion_auth') > 0) {
                //Make sure it isn't expired
                $expiration = $this->config->item('forgot_password_expiration', 'ion_auth');
                if (time() - $profile->forgotten_password_time > $expiration) {
                    //it has expired
                    $this->set_error('forgot_password_expired');
                    $this->trigger_events(['post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful']);
                    return false;
                }
            }

            $password = $this->salt();

            $data = [
                'password'                => $this->hash_password($password, $salt),
                'forgotten_password_code' => null,
                'active'                  => 1,
            ];

            $this->db->update($this->tables['users'], $data, ['forgotten_password_code' => $code]);

            $this->trigger_events(['post_forgotten_password_complete', 'post_forgotten_password_complete_successful']);
            return $password;
        }

        $this->trigger_events(['post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful']);
        return false;
    }

    public function get_attempts_num($identity)
    {
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $ip_address = $this->_prepare_ip($this->input->ip_address());
            $this->db->select('1', false);
            if ($this->config->item('track_login_ip_address', 'ion_auth')) {
                $this->db->where('ip_address', $ip_address);
            } elseif (strlen($identity) > 0) {
                $this->db->or_where('login', $identity);
            }
            $qres = $this->db->get($this->tables['login_attempts']);
            return $qres->num_rows();
        }
        return 0;
    }

    public function get_last_attempt_time($identity)
    {
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $ip_address = $this->_prepare_ip($this->input->ip_address());

            $this->db->select_max('time');
            if ($this->config->item('track_login_ip_address', 'ion_auth')) {
                $this->db->where('ip_address', $ip_address);
            } elseif (strlen($identity) > 0) {
                $this->db->or_where('login', $identity);
            }
            $qres = $this->db->get($this->tables['login_attempts'], 1);

            if ($qres->num_rows() > 0) {
                return $qres->row()->time;
            }
        }

        return 0;
    }

    /**
     * get_users_groups
     *
     * @return array
     * @author Ben Edmunds
     * */
    public function get_users_group($id = false)
    {
        $this->trigger_events('get_users_group');

        //if no id was passed use the current users id
        $id || $id = $this->session->userdata('user_id');

        return $this->db->select($this->tables['groups'] . '.id as id, ' . $this->tables['groups'] . '.name, ' . $this->tables['groups'] . '.description')
            ->where($this->tables['groups'] . '.id', $id)
            //->join($this->tables['groups'], $this->tables['users_groups'] . '.' . $this->join['groups'] . '=' . $this->tables['groups'] . '.id')
            ->get($this->tables['groups']);
    }

    public function getBillingAddress($id)
    {
        $q = $this->db->get_where('addresses', ['user_id' => $id, 'type' => 'billing'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getDeliveryAddress($id)
    {
        $q = $this->db->get_where('addresses', ['user_id' => $id, 'type' => 'delivery'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function group($id = null)
    {
        $this->trigger_events('group');

        if (isset($id)) {
            $this->db->where($this->tables['groups'] . '.id', $id);
        }

        $this->limit(1);

        return $this->groups();
    }

    /**
     * groups
     *
     * @return object
     * @author Ben Edmunds
     * */
    public function groups()
    {
        $this->trigger_events('groups');

        //run each where that was passed
        if (isset($this->_ion_where) && !empty($this->_ion_where)) {
            foreach ($this->_ion_where as $where) {
                $this->db->where($where);
            }
            $this->_ion_where = [];
        }

        if (isset($this->_ion_limit) && isset($this->_ion_offset)) {
            $this->db->limit($this->_ion_limit, $this->_ion_offset);

            $this->_ion_limit  = null;
            $this->_ion_offset = null;
        } elseif (isset($this->_ion_limit)) {
            $this->db->limit($this->_ion_limit);

            $this->_ion_limit = null;
        }

        //set the order
        if (isset($this->_ion_order_by) && isset($this->_ion_order)) {
            $this->db->order_by($this->_ion_order_by, $this->_ion_order);
        }

        $this->response = $this->db->get($this->tables['groups']);

        return $this;
    }

    public function hash_code($password)
    {
        return $this->hash_password($password, false, true);
    }

    public function hash_password($password, $salt = false, $use_sha1_override = false)
    {
        if (empty($password)) {
            return false;
        }

        //bcrypt
        if ($use_sha1_override === false && $this->hash_method == 'bcrypt') {
            return $this->bcrypt->hash($password);
        }

        if ($this->store_salt && $salt) {
            return sha1($password . $salt);
        }
        $salt = $this->salt();
        return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
    }

    public function hash_password_db($id, $password, $use_sha1_override = false)
    {
        if (empty($id) || empty($password)) {
            return false;
        }

        $this->trigger_events('extra_where');

        $query = $this->db->select('password, salt')
            ->where('id', $id)
            ->limit(1)
            ->get($this->tables['users']);

        $hash_password_db = $query->row();

        if ($query->num_rows() !== 1) {
            return false;
        }

        // bcrypt
        if ($use_sha1_override === false && $this->hash_method == 'bcrypt') {
            if ($this->bcrypt->verify($password, $hash_password_db->password)) {
                return true;
            }

            return false;
        }

        // sha1
        if ($this->store_salt) {
            $db_password = sha1($password . $hash_password_db->salt);
        } else {
            $salt = substr($hash_password_db->password, 0, $this->salt_length);

            $db_password = $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }

        if ($db_password == $hash_password_db->password) {
            return true;
        }
        return false;
    }

    public function identity_check($identity = '')
    {
        $this->trigger_events('identity_check');

        if (empty($identity)) {
            return false;
        }

        return $this->db->where($this->identity_column, $identity)
            ->count_all_results($this->tables['users']) > 0;
    }

    public function increase_login_attempts($identity)
    {
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $ip_address = $this->_prepare_ip($this->input->ip_address());
            return $this->db->insert($this->tables['login_attempts'], ['ip_address' => $ip_address, 'login' => $identity, 'time' => time()]);
        }
        return false;
    }

    public function is_max_login_attempts_exceeded($identity)
    {
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $max_attempts = $this->config->item('maximum_login_attempts', 'ion_auth');
            if ($max_attempts > 0) {
                $attempts = $this->get_attempts_num($identity);
                return $attempts >= $max_attempts;
            }
        }
        return false;
    }

    public function is_time_locked_out($identity)
    {
        return $this->is_max_login_attempts_exceeded($identity) && $this->get_last_attempt_time($identity) > time() - $this->config->item('lockout_time', 'ion_auth');
    }

    public function like($like, $value = null, $position = 'both')
    {
        $this->trigger_events('like');

        if (!is_array($like)) {
            $like = [$like => [
                'value'    => $value,
                'position' => $position,
            ]];
        }

        array_push($this->_ion_like, $like);

        return $this;
    }

    public function limit($limit)
    {
        $this->trigger_events('limit');
        $this->_ion_limit = $limit;

        return $this;
    }

    public function login($identity, $password, $remember = false)
    {
        $this->trigger_events('pre_login');

        if (empty($identity) || empty($password)) {
            $this->set_error('login_unsuccessful');
            return false;
        }

        $this->trigger_events('extra_where');
        $this->load->helper('email');
        $this->identity_column = valid_email($identity) ? 'email' : 'username';
        $query                 = $this->db->select($this->identity_column . ', username, email, id, password, active, last_login, last_ip_address, avatar, gender, group_id, warehouse_id, biller_id, company_id, view_right, edit_right, allow_discount, show_cost, show_price,allow_discount_value')
            ->where($this->identity_column, $this->db->escape_str($identity))
            ->limit(1)
            ->get($this->tables['users']);

        if ($this->is_time_locked_out($identity)) {
            //Hash something anyway, just to take up time
            $this->hash_password($password);

            $this->trigger_events('post_login_unsuccessful');
            $this->set_error('login_timeout');

            return false;
        }

        if ($query->num_rows() === 1) {
            $user = $query->row();
             $allow_discount_value      = $user->allow_discount_value;
             // Password not required for OTP login
            $password = true; //$this->hash_password_db($user->id, $password);

            if ($password === true) {
                if ($user->active != 1) {
                    $this->trigger_events('post_login_unsuccessful');
                    $this->set_error('login_unsuccessful_not_active');
                    return false;
                }

                $this->set_session($user);
                $this->session->set_userdata('allow_discount_value', $allow_discount_value);
                $this->update_last_login($user->id);
                $this->update_last_login_ip($user->id);
                $ldata = ['user_id' => $user->id, 'ip_address' => $this->input->ip_address(), 'login' => $identity, 'time' => date('Y-m-d H:i:s')];
                $this->db->insert('user_logins', $ldata);
                $this->clear_login_attempts($identity);

                if ($remember && $this->config->item('remember_users', 'ion_auth')) {
                    $this->remember_user($user->id);
                }

                $this->trigger_events(['post_login', 'post_login_successful']);
                $this->set_message('login_successful');

                return true;
            }
        }

        //Hash something anyway, just to take up time
        $this->hash_password($password);

        $this->increase_login_attempts($identity);

        $this->trigger_events('post_login_unsuccessful');
        $this->set_error('login_unsuccessful');

        return false;
    }

    public function login_remembered_user()
    {
        $this->trigger_events('pre_login_remembered_user');

        //check for valid data
        if (!get_cookie('identity') || !get_cookie('remember_code') || !$this->identity_check(get_cookie('identity'))) {
            $this->trigger_events(['post_login_remembered_user', 'post_login_remembered_user_unsuccessful']);
            return false;
        }

        //get the user
        $this->trigger_events('extra_where');
        $query = $this->db->select($this->identity_column . ', id, username, email, last_login, last_ip_address, avatar, gender, group_id, warehouse_id, biller_id, company_id, view_right, allow_discount, edit_right, show_cost, show_price')
            ->where($this->identity_column, get_cookie('identity'))
            ->where('remember_code', get_cookie('remember_code'))
            ->limit(1)
            ->get($this->tables['users']);

        //if the user was found, sign them in
        if ($query->num_rows() == 1) {
            $user = $query->row();

            $this->update_last_login($user->id);
            $this->update_last_login_ip($user->id);

            $this->set_session($user);

            //extend the users cookies if the option is enabled
            if ($this->config->item('user_extend_on_login', 'ion_auth')) {
                $this->remember_user($user->id);
            }

            $this->trigger_events(['post_login_remembered_user', 'post_login_remembered_user_successful']);
            return true;
        }

        $this->trigger_events(['post_login_remembered_user', 'post_login_remembered_user_unsuccessful']);
        return false;
    }

    public function messages()
    {
        $_output = '';
        foreach ($this->messages as $message) {
            $messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
            $_output .= $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
        }

        return $_output;
    }

    public function messages_array($langify = true)
    {
        if ($langify) {
            $_output = [];
            foreach ($this->messages as $message) {
                $messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
                $_output[]   = $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
            }
            return $_output;
        }
        return $this->messages;
    }

    public function num_rows()
    {
        $this->trigger_events(['num_rows']);

        $result = $this->response->num_rows();
        $this->response->free_result();

        return $result;
    }

    public function offset($offset)
    {
        $this->trigger_events('offset');
        $this->_ion_offset = $offset;

        return $this;
    }

    public function order_by($by, $order = 'desc')
    {
        $this->trigger_events('order_by');

        $this->_ion_order_by = $by;
        $this->_ion_order    = $order;

        return $this;
    }

    public function register($username, $password, $email, $additional_data = [], $active = true)
    {
        $this->trigger_events('pre_register');

        $manual_activation = $this->config->item('manual_activation', 'ion_auth');

        // if ($this->identity_column == 'email' && $this->email_check($email)) {
        //     $this->set_error('account_creation_duplicate_email');
        //     return false;
        // } elseif ($this->identity_column == 'username' && $this->username_check($username)) {
        //     $this->set_error('account_creation_duplicate_username');
        //     return false;
        // }

        // If username is taken, use username1 or username2, etc.
        if ($this->identity_column != 'username') {
            $original_username = $username;
            for ($i = 0; $this->username_check($username); $i++) {
                if ($i > 0) {
                    $username = $original_username . $i;
                }
            }
        }

        // IP Address
        $ip_address = $this->_prepare_ip($this->input->ip_address());
        $salt       = $this->store_salt ? $this->salt() : false;
        $password   = $this->hash_password($password, $salt);

        // Users table.
        $data = [
            'username'   => $username,
            'password'   => $password,
            'email'      => $email,
            'ip_address' => $ip_address,
            'created_on' => time(),
            'last_login' => time(),
            'active'     => ($active ? 1 : (($manual_activation === true) ? 1 : 0)),
        ];

        if ($this->store_salt) {
            $data['salt'] = $salt;
        }

        if (!isset($additional_data['group_id'])) {
            $additional_data['group_id'] = 3;
        }
        //filter out any data passed that doesnt have a matching column in the users table
        //and merge the set user data and the additional data
        $user_data = array_merge($this->_filter_data($this->tables['users'], $additional_data), $data);

        $this->trigger_events('extra_set');

        $this->db->insert($this->tables['users'], $user_data);

        $id = $this->db->insert_id();

        /*if(!empty($groups)) {
            //add to groups
            foreach ($groups as $group) {
                $this->add_to_group($group, $id);
            }
        }

        //add to default group if not already set
        $default_group = $this->where('name', $this->config->item('default_group', 'ion_auth'))->group()->row();
        if((isset($default_group->id) && !isset($groups)) || (empty($groups) && !in_array($default_group->id, $groups))) {
            $this->add_to_group($default_group->id, $id);
        }*/

        $this->trigger_events('post_register');

        return (isset($id)) ? $id : false;
    }

    public function remember_user($id)
    {
        $this->trigger_events('pre_remember_user');

        if (!$id) {
            return false;
        }

        $user = $this->user($id)->row();

        $salt = sha1($user->password);

        $this->db->update($this->tables['users'], ['remember_code' => $salt], ['id' => $id]);

        if ($this->db->affected_rows() > -1) {
            // if the user_expire is set to zero we'll set the expiration two years from now.
            if ($this->config->item('user_expire', 'ion_auth') === 0) {
                $expire = (60 * 60 * 24 * 365 * 2);
            } // otherwise use what is set
            else {
                $expire = $this->config->item('user_expire', 'ion_auth');
            }

            set_cookie([
                'name'   => 'identity',
                'value'  => $user->{$this->identity_column},
                'expire' => $expire,
            ]);

            set_cookie([
                'name'   => 'remember_code',
                'value'  => $salt,
                'expire' => $expire,
            ]);

            $this->trigger_events(['post_remember_user', 'remember_user_successful']);
            return true;
        }

        $this->trigger_events(['post_remember_user', 'remember_user_unsuccessful']);
        return false;
    }

    public function remove_hook($event, $name)
    {
        if (isset($this->_ion_hooks->{$event}[$name])) {
            unset($this->_ion_hooks->{$event}[$name]);
        }
    }

    public function remove_hooks($event)
    {
        if (isset($this->_ion_hooks->$event)) {
            unset($this->_ion_hooks->$event);
        }
    }

    public function reset_password($identity, $new)
    {
        $this->trigger_events('pre_change_password');

        if (!$this->identity_check($identity)) {
            $this->trigger_events(['post_change_password', 'post_change_password_unsuccessful']);
            return false;
        }

        $this->trigger_events('extra_where');

        $query = $this->db->select('id, password, salt')
            ->where($this->identity_column, $identity)
            ->limit(1)
            ->get($this->tables['users']);

        if ($query->num_rows() !== 1) {
            $this->trigger_events(['post_change_password', 'post_change_password_unsuccessful']);
            $this->set_error('password_change_unsuccessful');
            return false;
        }

        $result = $query->row();

        $new = $this->hash_password($new, $result->salt);

        //store the new password and reset the remember code so all remembered instances have to re-login
        //also clear the forgotten password code
        $data = [
            'password'                => $new,
            'remember_code'           => null,
            'forgotten_password_code' => null,
            'forgotten_password_time' => null,
        ];

        $this->trigger_events('extra_where');
        $this->db->update($this->tables['users'], $data, [$this->identity_column => $identity]);

        $return = $this->db->affected_rows() == 1;
        if ($return) {
            $this->trigger_events(['post_change_password', 'post_change_password_successful']);
            $this->set_message('password_change_successful');
        } else {
            $this->trigger_events(['post_change_password', 'post_change_password_unsuccessful']);
            $this->set_error('password_change_unsuccessful');
        }

        return $return;
    }

    public function result()
    {
        $this->trigger_events('result');

        $result = $this->response->result();
        $this->response->free_result();

        return $result;
    }

    public function result_array()
    {
        $this->trigger_events(['result', 'result_array']);

        $result = $this->response->result_array();
        $this->response->free_result();

        return $result;
    }

    public function row()
    {
        $this->trigger_events('row');

        $row = $this->response->row();
        $this->response->free_result();

        return $row;
    }

    public function row_array()
    {
        $this->trigger_events(['row', 'row_array']);

        $row = $this->response->row_array();
        $this->response->free_result();

        return $row;
    }

    public function salt()
    {
        return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
    }

    public function select($select)
    {
        $this->trigger_events('select');

        $this->_ion_select[] = $select;

        return $this;
    }

    public function set_error($error)
    {
        $this->errors[] = $error;

        return $error;
    }

    public function set_error_delimiters($start_delimiter, $end_delimiter)
    {
        $this->error_start_delimiter = $start_delimiter;
        $this->error_end_delimiter   = $end_delimiter;

        return true;
    }

    public function set_hook($event, $name, $class, $method, $arguments)
    {
        $this->_ion_hooks->{$event}[$name]            = new stdClass;
        $this->_ion_hooks->{$event}[$name]->class     = $class;
        $this->_ion_hooks->{$event}[$name]->method    = $method;
        $this->_ion_hooks->{$event}[$name]->arguments = $arguments;
    }

    public function set_lang($lang = 'en')
    {
        $this->trigger_events('set_lang');

        // if the user_expire is set to zero we'll set the expiration two years from now.
        if ($this->config->item('user_expire', 'ion_auth') === 0) {
            $expire = (60 * 60 * 24 * 365 * 2);
        } // otherwise use what is set
        else {
            $expire = $this->config->item('user_expire', 'ion_auth');
        }

        set_cookie([
            'name'   => 'lang_code',
            'value'  => $lang,
            'expire' => $expire,
        ]);

        return true;
    }

    public function set_message($message)
    {
        $this->messages[] = $message;

        return $message;
    }

    public function set_message_delimiters($start_delimiter, $end_delimiter)
    {
        $this->message_start_delimiter = $start_delimiter;
        $this->message_end_delimiter   = $end_delimiter;

        return true;
    }

    public function set_session($user)
    {
        $this->trigger_events('pre_set_session');

        $session_data = [
            'identity'       => $user->{$this->identity_column},
            'username'       => $user->username,
            'email'          => $user->email,
            'user_id'        => $user->id, //everyone likes to overwrite id so we'll use user_id
            'old_last_login' => $user->last_login,
            'last_ip'        => $user->last_ip_address,
            'avatar'         => $user->avatar,
            'gender'         => $user->gender,
            'group_id'       => $user->group_id,
            'warehouse_id'   => $user->warehouse_id,
            'view_right'     => $user->view_right,
            'edit_right'     => $user->edit_right,
            'allow_discount' => $user->allow_discount,
            'biller_id'      => $user->biller_id,
            'company_id'     => $user->company_id,
            'show_cost'      => $user->show_cost,
            'show_price'     => $user->show_price,
        ];

        $this->session->set_userdata($session_data);

        $this->trigger_events('post_set_session');

        return true;
    }

    public function trigger_events($events)
    {
        if (is_array($events) && !empty($events)) {
            foreach ($events as $event) {
                $this->trigger_events($event);
            }
        } else {
            if (isset($this->_ion_hooks->$events) && !empty($this->_ion_hooks->$events)) {
                foreach ($this->_ion_hooks->$events as $name => $hook) {
                    $this->_call_hook($events, $name);
                }
            }
        }
    }

    public function update($id, array $data, $upgs = [])
    {
        $this->trigger_events('pre_update_user');

        $user = $this->user($id)->row();

        $this->db->trans_begin();

        if (array_key_exists($this->identity_column, $data) && $this->identity_check($data[$this->identity_column]) && $user->{$this->identity_column} !== $data[$this->identity_column]) {
            $this->db->trans_rollback();
            $this->set_error('account_creation_duplicate_' . $this->identity_column);

            $this->trigger_events(['post_update_user', 'post_update_user_unsuccessful']);
            $this->set_error('update_unsuccessful');

            return false;
        }

        // Filter the data passed
        $data = $this->_filter_data($this->tables['users'], $data);

        if (array_key_exists('username', $data) || array_key_exists('password', $data) || array_key_exists('email', $data)) {
            if (array_key_exists('password', $data)) {
                if (!empty($data['password'])) {
                    $data['password'] = $this->hash_password($data['password'], $user->salt);
                } else {
                    // unset password so it doesn't effect database entry if no password passed
                    unset($data['password']);
                }
            }
        }

        $this->trigger_events('extra_where');
        $this->db->update($this->tables['users'], $data, ['id' => $user->id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $this->trigger_events(['post_update_user', 'post_update_user_unsuccessful']);
            $this->set_error('update_unsuccessful');
            return false;
        }

        $this->db->trans_commit();

        $this->trigger_events(['post_update_user', 'post_update_user_successful']);
        $this->set_message('update_successful');
        return true;
    }

    public function update_group($group_id = false, $group_name = false, $additional_data = [])
    {
        if (empty($group_id)) {
            return false;
        }

        $data = [];

        if (!empty($group_name)) {
            // we are changing the name, so do some checks
            // bail if the group name already exists
            $existing_group = $this->db->get_where($this->tables['groups'], ['name' => $group_name])->row();
            if (isset($existing_group->id) && $existing_group->id != $group_id) {
                $this->set_error('group_already_exists');
                return false;
            }

            $data['name'] = $group_name;
        }

        // IMPORTANT!! Third parameter was string type $description; this following code is to maintain backward compatibility
        // New projects should work with 3rd param as array
        if (is_string($additional_data)) {
            $additional_data = ['description' => $additional_data];
        }

        //filter out any data passed that doesnt have a matching column in the groups table
        //and merge the set group data and the additional data
        if (!empty($additional_data)) {
            $data = array_merge($this->_filter_data($this->tables['groups'], $additional_data), $data);
        }

        $this->db->update($this->tables['groups'], $data, ['id' => $group_id]);

        $this->set_message('group_update_successful');

        return true;
    }

    public function update_last_login($id)
    {
        $this->trigger_events('update_last_login');

        $this->load->helper('date');

        $this->trigger_events('extra_where');

        $this->db->update($this->tables['users'], ['last_login' => time()], ['id' => $id]);

        return $this->db->affected_rows() == 1;
    }

    public function update_last_login_ip($id)
    {
        $this->trigger_events('update_last_login_ip');

        $this->trigger_events('extra_where');

        $this->db->update($this->tables['users'], ['last_ip_address' => $this->input->ip_address()], ['id' => $id]);

        return $this->db->affected_rows() == 1;
    }

    public function updateAvatar($id, $avatar)
    {
        if ($this->db->update($this->tables['users'], ['avatar' => $avatar], ['id' => $id])) {
            $this->set_message('avatar_updated');
            return true;
        }
        return false;
    }

    public function updateUser($id, $admin)
    {
        if ($this->db->update('users', ['admin_id' => $admin], ['id' => $id])) {
            $this->db->update('orders', ['admin_id' => $admin], ['customer_id' => $id]);
            $this->db->update('drafts', ['admin_id' => $admin], ['customer_id' => $id]);
            return true;
        }
        return false;
    }

    public function user($id = null)
    {
        $this->trigger_events('user');

        //if no id was passed use the current users id
        $id || $id = $this->session->userdata('user_id');

        $this->limit(1);
        $this->where($this->tables['users'] . '.id', $id);

        $this->users();

        return $this;
    }

    public function username_check($username = '')
    {
        $this->trigger_events('username_check');

        if (empty($username)) {
            return false;
        }

        $this->trigger_events('extra_where');

        return $this->db->where('username', $username)
            ->count_all_results($this->tables['users']) > 0;
    }

    public function users($groups = null)
    {
        $this->trigger_events('users');

        if (isset($this->_ion_select) && !empty($this->_ion_select)) {
            foreach ($this->_ion_select as $select) {
                $this->db->select($select);
            }

            $this->_ion_select = [];
        } else {
            //default selects
            $this->db->select([
                $this->tables['users'] . '.*',
                $this->tables['users'] . '.id as id',
                $this->tables['users'] . '.id as user_id',
            ]);
        }

        //filter by group id(s) if passed
        /*if(isset($groups)) {
            //build an array if only one group was passed
            if(is_numeric($groups)) {
                $groups = Array($groups);
            }

            //join and then run a where_in against the group ids
            if(isset($groups) && !empty($groups)) {
                $this->db->distinct();
                $this->db->join(
                        $this->tables['users_groups'], $this->tables['users_groups'] . '.' . $this->join['users'] . '=' . $this->tables['users'] . '.id', 'inner'
                );

                $this->db->where_in($this->tables['users_groups'] . '.' . $this->join['groups'], $groups);
            }
        }*/

        $this->trigger_events('extra_where');

        //run each where that was passed
        if (isset($this->_ion_where) && !empty($this->_ion_where)) {
            foreach ($this->_ion_where as $where) {
                $this->db->where($where);
            }

            $this->_ion_where = [];
        }

        if (isset($this->_ion_like) && !empty($this->_ion_like)) {
            foreach ($this->_ion_like as $like) {
                $this->db->or_like($like);
            }

            $this->_ion_like = [];
        }

        if (isset($this->_ion_limit) && isset($this->_ion_offset)) {
            $this->db->limit($this->_ion_limit, $this->_ion_offset);

            $this->_ion_limit  = null;
            $this->_ion_offset = null;
        } elseif (isset($this->_ion_limit)) {
            $this->db->limit($this->_ion_limit);

            $this->_ion_limit = null;
        }

        //set the order
        if (isset($this->_ion_order_by) && isset($this->_ion_order)) {
            $this->db->order_by($this->_ion_order_by, $this->_ion_order);

            $this->_ion_order    = null;
            $this->_ion_order_by = null;
        }

        $this->response = $this->db->get($this->tables['users']);

        return $this;
    }

    public function where($where, $value = null)
    {
        $this->trigger_events('where');

        if (!is_array($where)) {
            $where = [$where => $value];
        }

        array_push($this->_ion_where, $where);

        return $this;
    }
}
