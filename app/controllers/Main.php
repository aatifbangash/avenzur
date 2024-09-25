<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Main extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();

        //$this->load->library('session');
        //adding data to session 
        //$this->session->set_userdata('country','0');
        //set_cookie('shop_country', '0', 31536000);
        if ($this->Settings->mmode && $this->v != 'login') {
            redirect('notify/offline');
        }
        $this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->lang->admin_load('auth', $this->Settings->user_language);
        $this->load->helper('url');
        $this->load->model('Shop_model');
        $this->load->admin_model('settings_model');

        $this->load->library("pagination");

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    public function activate($id, $code)
    {
        if (!SHOP) {
            redirect('admin/auth/activate/' . $id . '/' . $code);
        }
        if ($code) {
            if ($activation = $this->ion_auth->activate($id, $code)) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('login');
            }
        } else {
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect('login');
        }
    }

    public function captcha_check($cap)
    {
        $expiration = time() - 300; // 5 minutes limit
        $this->db->delete('captcha', ['captcha_time <' => $expiration]);

        $this->db->select('COUNT(*) AS count')
            ->where('word', $cap)
            ->where('ip_address', $this->input->ip_address())
            ->where('captcha_time >', $expiration);

        if ($this->db->count_all_results('captcha')) {
            return true;
        }
        $this->form_validation->set_message('captcha_check', lang('captcha_wrong'));
        return false;
    }

    public function cookie($val)
    {
        set_cookie('shop_use_cookie', $val, 31536000);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function currency($currency)
    {
        set_cookie('shop_currency', $currency, 31536000);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function forgot_password()
    {
        if (!SHOP) {
            redirect('admin/auth/forgot_password');
        }
        $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email');

        if ($this->form_validation->run() == false) {
            $this->sma->send_json(validation_errors());
        } else {
            $identity = $this->ion_auth->where('email', strtolower($this->input->post('email')))->users()->row();
            if (empty($identity)) {
                $this->sma->send_json(lang('forgot_password_email_not_found'));
            }

            $forgotten = $this->ion_auth->forgotten_password($identity->email);
            if ($forgotten) {
                $this->sma->send_json(['status' => 'success', 'message' => $this->ion_auth->messages()]);
            } else {
                $this->sma->send_json(['status' => 'error', 'message' => $this->ion_auth->errors()]);
            }
        }
    }

    public function hide($id = null)
    {
        $this->session->set_userdata('hidden' . $id, 1);
        echo true;
    }

    public function index()
    {
        if ($_SERVER['HTTP_HOST'] === 'www.avenzur.com') {
            header('Location: https://avenzur.com');
        }

        $config = array();
        $config["base_url"] = base_url() . "";
        $config["total_rows"] = $this->Shop_model->get_count();
        $config["per_page"] = 10;
        $config["uri_segment"] = 1;

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;

        $data["links"] = $this->pagination->create_links();

        $data['authors'] = $this->Shop_model->get_authors($config["per_page"], $page);


        if (!SHOP) {
            redirect('/');
        }
        if ($this->shop_settings->private && !$this->loggedIn) {
            redirect('/');
        }
        /*else if(!$this->loggedIn){

            $cookies = get_cookie();
            foreach ($cookies as $cookie_name => $cookie_value) {
                delete_cookie($cookie_name);
            }

            $this->session->sess_destroy();
        }*/

        $this->site->logVisitor();

        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->data['featured_categories'] = $this->shop_model->getFeaturedCategories();
        $this->data['popular_categories'] = $this->shop_model->getPopularCategories();

        $this->data['best_sellers'] = $this->shop_model->getBestSellers(16, true, []);
        $this->data['best_sellers_additional'] = $this->shop_model->getBestSellersAdditional();
        $this->data['featured_products'] = $this->shop_model->getFeaturedProducts();
        $this->data['special_offers'] = $this->shop_model->getSpecialOffers();
        $this->data['slider'] = json_decode($this->shop_settings->slider);
        $this->data['page_title'] = $this->shop_settings->shop_name;
        $this->data['page_desc'] = $this->shop_settings->description;
        $this->page_construct('index', $this->data);
    }

    public function language($lang)
    {
        $folder = 'app/language/';
        $languagefiles = scandir($folder);
        if (in_array($lang, $languagefiles)) {
            set_cookie('shop_language', $lang, 31536000);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function get_country_by_ip()
    {
        $data = array();
        // Get the visitor's IP address
        $ip = $_SERVER['REMOTE_ADDR'];
        // Create a cURL request to fetch geolocation data
        $ch = curl_init("https://ipinfo.io/{$ip}/country"); // 188.53.165.141
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);

        // Close the cURL session
        curl_close($ch);

        return $response;
    }

    /*public function login($m = null)
    {
        $country_code = $this->get_country_by_ip();
        
        if (!SHOP || $this->Settings->mmode) {
            redirect('admin/login');
        }
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            redirect('/');
        }

        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run('auth/login') == true) {
            $remember = (bool)$this->input->post('remember_me');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                if ($this->Settings->mmode) {
                    if (!$this->ion_auth->in_group('owner')) {
                        $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                        redirect('logout');
                    }
                }

                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                redirect($referrer);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('login');
            }
        } else {
            if ($this->Settings->captcha) {
                $this->load->helper('captcha');
                $vals = [
                    'img_path'    => './assets/captcha/',
                    'img_url'     => base_url('assets/captcha/'),
                    'img_width'   => 150,
                    'img_height'  => 34,
                    'word_length' => 5,
                    'colors'      => ['background' => [255, 255, 255], 'border' => [204, 204, 204], 'text' => [102, 102, 102], 'grid' => [204, 204, 204]],
                ];
                $cap     = create_captcha($vals);
                $capdata = [
                    'captcha_time' => $cap['time'],
                    'ip_address'   => $this->input->ip_address(),
                    'word'         => $cap['word'],
                ];

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image']   = $cap['image'];
                $this->data['captcha'] = ['name' => 'captcha',
                    'id'                         => 'captcha',
                    'type'                       => 'text',
                    'class'                      => 'form-control',
                    'required'                   => 'required',
                    'placeholder'                => lang('type_captcha'),
                ];
            }
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message']    = $m ? lang('password_changed') : $this->session->flashdata('message');
            $this->data['page_title'] = lang('login');
            $this->data['page_desc']  = $this->shop_settings->description;
            $this->data['country'] = $this->shop_model->getallCountryR();
            $this->data['country_code'] = $country_code;
            $this->data['all_categories']    = $this->shop_model->getAllCategories();
            if ($this->shop_settings->private) {
                $this->data['message']       = $data['message'] ?? $this->session->flashdata('message');
                $this->data['error']         = isset($this->data['error']) ? $this->data['error'] : $this->session->flashdata('error');
                $this->data['warning']       = $data['warning']  ?? $this->session->flashdata('warning');
                $this->data['reminder']      = $data['reminder'] ?? $this->session->flashdata('reminder');
                $this->data['Settings']      = $this->Settings;
                $this->data['shop_settings'] = $this->shop_settings;
                $this->load->view($this->theme . 'user/private_login.php', $this->data);
                
            } else {
                //$this->session->set_flashdata('error', 'Login Failed / Wrong Credentials');
                //$referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                //redirect($referrer);
                $this->page_construct('user/login', $this->data);
            }
        }
    }*/

    public function logout($m = null)
    {  
        // if (!SHOP) {
        //     redirect('admin/logout');
        // }
        $logout = $this->ion_auth->logout();
        $this->cart->destroy();
        if (isset($_COOKIE['companyID'])) {
            $expirationTime = (time() + 3600 * 9999999) * -1;
            setcookie("companyID", "", $expirationTime, '/');
        }
        $referrer = ($_SERVER['HTTP_REFERER'] ?? '/');
        $this->session->set_flashdata('message', $this->ion_auth->messages());
        //redirect($m ? 'login/m' : $referrer);
        redirect('/');
    }

    public function profile($act = null)
    {
        if (!$this->loggedIn) {
            redirect('/');
        }
        if (!SHOP || $this->Staff) {
            redirect('admin/users/profile/' . $this->session->userdata('user_id'));
        }
        $user = $this->ion_auth->user()->row();
        if ($act == 'user') {
            $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
            $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
            $this->form_validation->set_rules('phone', lang('phone'), 'required');
            $this->form_validation->set_rules('email', lang('email'), 'required|valid_email');
            $this->form_validation->set_rules('company', lang('company'), 'trim');
            $this->form_validation->set_rules('vat_no', lang('vat_no'), 'trim');
            $this->form_validation->set_rules('address', lang('billing_address'), 'required');
            $this->form_validation->set_rules('city', lang('city'), 'required');
            $this->form_validation->set_rules('state', lang('state'), 'required');
            $this->form_validation->set_rules('postal_code', lang('postal_code'), 'required');
            $this->form_validation->set_rules('country', lang('country'), 'required');
            if ($user->email != $this->input->post('email')) {
                $this->form_validation->set_rules('email', lang('email'), 'trim|is_unique[users.email]');
            }

            if ($this->form_validation->run() === true) {
                $bdata = [
                    'name' => $this->input->post('first_name') . ' ' . $this->input->post('last_name'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                    'company' => $this->input->post('company'),
                    'vat_no' => $this->input->post('vat_no'),
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country'),
                ];

                $udata = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                ];

                if ($this->ion_auth->update($user->id, $udata) && $this->shop_model->updateCompany($user->company_id, $bdata)) {
                    $this->session->set_flashdata('message', lang('user_updated'));
                    $this->session->set_flashdata('message', lang('billing_data_updated'));
                    redirect('profile');
                }
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($act == 'password') {
            $this->form_validation->set_rules('old_password', lang('old_password'), 'required');
            $this->form_validation->set_rules('new_password', lang('new_password'), 'required|min_length[8]|max_length[25]');
            $this->form_validation->set_rules('new_password_confirm', lang('confirm_password'), 'required|matches[new_password]');

            if ($this->form_validation->run() == false) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('profile');
            } else {
                if (DEMO) {
                    $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));
                $change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));

                if ($change) {
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                    $this->logout('m');
                } else {
                    $this->session->set_flashdata('error', $this->ion_auth->errors());
                    redirect('profile');
                }
            }
        }

        $this->data['featured_products'] = $this->shop_model->getFeaturedProducts();
        $this->data['country'] = $this->site->getallCountry();
        $this->data['customer'] = $this->site->getCompanyByID($this->session->userdata('company_id'));
        $this->data['user'] = $this->site->getUser();
        $this->data['page_title'] = lang('profile');
        $this->data['page_desc'] = $this->shop_settings->description;
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->page_construct('user/profile', $this->data);
    }

    public function sendOTP($company_id, $identifier, $medium)
    {

        $otp = mt_rand(100000, 999999);

        $otp_data = [
            'medium' => $medium,
            'identifier' => $identifier,
            'otp' => $otp,
            'userid' => $company_id,
            'date_updated' => date('Y-m-d h:i:s')
        ];

        $opt_id = $this->shop_model->addOTPData($otp_data);

        if ($opt_id) {
            $attachment = null;
            $message = 'Your One Time Password for Avenzur.com is ' . $otp;

            if ($medium == 'email') {
                $this->sma->send_email($identifier, 'OTP Verification', $message, null, null, $attachment, ['fabbas@pharma.com.sa'], ['faisalabbas67@gmail.com']);
                echo json_encode(['status' => 'success', 'message' => 'OTP sent to email']);
            } else {
                $whatsapp_sent = $this->sma->send_whatsapp_msg($identifier, $otp);
                $whatsapp_data = json_decode($whatsapp_sent, true);

                /*if ($whatsapp_data && isset($whatsapp_data['messageId'])) {
                    echo json_encode(['status' => 'success', 'message' => 'OTP sent to whatsapp']);
                } else {
                    $sms_sent = $this->sma->send_sms($identifier, $otp);
                }*/

                //$sms_sent = $this->sma->send_sms($identifier, $otp);

                // uncomment below lines
                $message_to_send = 'Your OTP verification code is '.$otp;
                $sms_sent = $this->sma->send_sms_new($identifier, $message_to_send);
                echo $sms_sent;

                //echo json_encode(['status' => 'success', 'message' => 'OTP sent to whatsapp']);
            }

            return true;
        } else {
            return false;
        }
    }

    public function mobile_verify_otp()
    {
        $this->form_validation->set_rules('identifier_input', lang('Mobile'), 'required');

        if ($this->form_validation->run('') == true) {
            $identity = strtolower($this->input->post('identifier_input'));
            $opt_part1 = strtolower($this->input->post('opt_part1'));
            $opt_part2 = strtolower($this->input->post('opt_part2'));
            $opt_part3 = strtolower($this->input->post('opt_part3'));
            $opt_part4 = strtolower($this->input->post('opt_part4'));
            $opt_part5 = strtolower($this->input->post('opt_part5'));
            $opt_part6 = strtolower($this->input->post('opt_part6'));

            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true) {
            $company_data = $this->shop_model->getUniqueCustomer('mobile', $identity);
            $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';

            if ($company_data) {

                $otp = $opt_part1 . $opt_part2 . $opt_part3 . $opt_part4 . $opt_part5 . $opt_part6;

                $validate = $this->shop_model->validate_otp($identity, $otp);
                if ($validate) {
                    $is_verified = $this->shop_model->verify_success_mobile($company_data->id);
                    if ($is_verified) {
                        $this->session->set_flashdata('message', 'Mobile verified successfully');
                        redirect('profile');
                    } else {
                        $this->session->set_flashdata('message', 'Mobile verification failed');
                        redirect('profile');
                    }
                } else {
                    $this->session->set_flashdata('message', 'OTP verification failed');
                    redirect('profile');
                }
            } else {
                $this->session->set_flashdata('message', 'Customer data not found');
                redirect('profile');
            }
        }
    }

    public function register_otp()
    {
        $this->form_validation->set_rules('identifier_input', lang('Email or Mobile'), 'required');

        if ($this->form_validation->run('') == true) {
            $identity = strtolower($this->input->post('identifier_input'));
            $opt_part1 = strtolower($this->input->post('opt_part1'));
            $opt_part2 = strtolower($this->input->post('opt_part2'));
            $opt_part3 = strtolower($this->input->post('opt_part3'));
            $opt_part4 = strtolower($this->input->post('opt_part4'));
            $opt_part5 = strtolower($this->input->post('opt_part5'));
            $opt_part6 = strtolower($this->input->post('opt_part6'));

            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true) {
            if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
                $type = 'email';
                $company_data = $this->shop_model->getUniqueCustomer($type, $identity);
            } else {
                $type = 'mobile';
                $company_data = $this->shop_model->getUniqueCustomer($type, str_replace("+966", "", $identity));
            }
            //print_r($company_data);exit;
            if ($company_data) {

                $otp = $opt_part1 . $opt_part2 . $opt_part3 . $opt_part4 . $opt_part5 . $opt_part6;
                //echo $identity.$otp;exit;
                $validate = $this->shop_model->validate_otp($identity, $otp);
                if ($validate) {
                    if ($this->form_validation->run('auth/login') == true) {
                        $remember = true;
                        if (!empty($company_data->email)) {
                            $login_column = $company_data->email;
                        } else {
                            $login_column = $company_data->phone;
                        }
                        //echo $login_column;exit;
                        $this->shop_model->activate_user($login_column);

                        if ($this->ion_auth->login($login_column, '12345', $remember)) {
                            // login success
                            if ($type == 'mobile') {
                                $this->shop_model->updateCompany($company_data->id, ['mobile_verified' => 1]);
                            }

                            if ($this->Settings->mmode) {
                                if (!$this->ion_auth->in_group('owner')) {
                                    $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                                    redirect('logout');
                                }
                            }

                            $cart_contents = $this->cart->contents();
                            if ($cart_contents) {
                                redirect('cart/checkout');
                            } else {
                                $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                                redirect($referrer);
                            }
                        } else {
                            $this->session->set_flashdata('error', $this->ion_auth->errors());
                            //redirect('login');
                            $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                            redirect($referrer);
                        }
                    } else {
                        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                        $this->data['message'] = $m ? lang('password_changed') : $this->session->flashdata('message');
                        $this->data['page_title'] = lang('login');
                        $this->data['page_desc'] = $this->shop_settings->description;
                        //$this->data['country'] = $this->shop_model->getallCountryR();
                        //$this->data['country_code'] = $country_code;
                        $this->data['all_categories'] = $this->shop_model->getAllCategories();

                        $this->page_construct('user/login', $this->data);

                    }
                } else {
                    $this->session->set_flashdata('error', 'OTP verification failed');
                    //redirect('login');
                    $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                    redirect($referrer);
                }

            } else {
                $this->session->set_flashdata('error', 'Data not found in system');
                $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                redirect($referrer);
                //redirect('login');
            }
        } else {
            $this->page_construct('user/login', $this->data);
        }
    }

    public function login_otp()
    {
        $this->form_validation->set_rules('identifier_input', lang('Email or Mobile'), 'required');

        if ($this->form_validation->run('') == true) {
            $identity = strtolower($this->input->post('identifier_input'));
            $opt_part1 = strtolower($this->input->post('opt_part1'));
            $opt_part2 = strtolower($this->input->post('opt_part2'));
            $opt_part3 = strtolower($this->input->post('opt_part3'));
            $opt_part4 = strtolower($this->input->post('opt_part4'));
            $opt_part5 = strtolower($this->input->post('opt_part5'));
            $opt_part6 = strtolower($this->input->post('opt_part6'));

            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true) {
            if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
                $type = 'email';
                $company_data = $this->shop_model->getUniqueCustomer($type, $identity);
            } else {
                $type = 'mobile';
                $company_data = $this->shop_model->getUniqueCustomer($type, $identity);
            }
            
            if ($company_data) {

                $otp = $opt_part1 . $opt_part2 . $opt_part3 . $opt_part4 . $opt_part5 . $opt_part6;

                $validate = $this->shop_model->validate_otp($identity, $otp);
                if ($validate) {
                    if ($this->form_validation->run('auth/login') == true) {
                        $remember = true;

                        // $user_data = $this->shop_model->getUserByEmail($company_data->email);
                        // if ($user_data->active == 0) {
                        //     $this->shop_model->activate_user($company_data->email);
                        // }

                        /* New changes as login failed after signup */

                        if (!empty($company_data->email)) {
                            $login_column = $company_data->email;
                        } else {
                            $login_column = $company_data->phone;
                        }

                        $this->shop_model->activate_user($login_column);

                        /* Changes End */
                        if ($this->ion_auth->login($login_column, '12345', $remember)) {
                            if ($this->Settings->mmode) {
                                if (!$this->ion_auth->in_group('owner')) {
                                    $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                                    //redirect('logout');
                                    echo json_encode(['status' => 'success', 'redirect' => base_url() . 'logout']);
                                }
                            }
                            // login success
                            if ($type == 'mobile') {
                                $this->shop_model->updateCompany($company_data->id, ['mobile_verified' => 1]);
                            }

                            $this->session->set_flashdata('message', $this->ion_auth->messages());

                            $cart_contents = $this->cart->contents();
                            if ($cart_contents) {
                                //redirect('cart/checkout');
                                header('Content-Type: application/json');
                                echo json_encode(['status' => 'success', 'redirect' => base_url() . 'cart/checkout']);
                            } else {
                                $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : base_url();
                                //redirect($referrer);
                                header('Content-Type: application/json');
                                echo json_encode(['status' => 'success', 'redirect' => $referrer]);
                            }

                        } else {
                            header('Content-Type: application/json');
                            //echo json_encode(['status' => 'error', 'message' => $this->ion_auth->errors()]);
                            echo json_encode(['status' => 'error', 'message' => 'login error']);
                            // $this->session->set_flashdata('error', $this->ion_auth->errors());
                            // $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                            // redirect($referrer);
                        }
                    } else {
                        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                        $this->data['message'] = $m ? lang('password_changed') : $this->session->flashdata('message');
                        $this->data['page_title'] = lang('login');
                        $this->data['page_desc'] = $this->shop_settings->description;
                        //$this->data['country'] = $this->shop_model->getallCountryR();
                        //$this->data['country_code'] = $country_code;
                        $this->data['all_categories'] = $this->shop_model->getAllCategories();

                        $this->page_construct('user/login', $this->data);

                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP!']);
                    // $this->session->set_flashdata('error', 'OTP verification failed');
                    // $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                    // redirect($referrer);
                    //redirect('login');
                }

            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Data not found in system!']);
                // $this->session->set_flashdata('error', 'Data not found in system');
                // $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                // redirect($referrer);
                //redirect('login');
            }
        } else {
            $this->page_construct('user/login', $this->data);
        }
    }

    public function login()
    {
        $this->form_validation->set_rules('identity', lang('Email or Mobile'), 'required');

        if ($this->form_validation->run('') == true) {
            $identity = strtolower($this->input->post('identity'));

            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true) {
            if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
                $type = 'email';
                $company_data = $this->shop_model->getUniqueCustomer($type, $identity);
            } else {
                $type = 'mobile';
                $company_data = $this->shop_model->getUniqueCustomer($type, $identity);
            }

            if ($company_data) {
                 $this->sendOTP($company_data->id, $identity, $type);

            } else {
                //register
                $company_data = [
                    'group_id' => 3,
                    'group_name' => 'customer',
                    'customer_group_id' => (!empty($customer_group)) ? $customer_group->id : null,
                    'customer_group_name' => (!empty($customer_group)) ? $customer_group->name : null,
                    'price_group_id' => (!empty($price_group)) ? $price_group->id : null,
                    'price_group_name' => (!empty($price_group)) ? $price_group->name : null,
                    'sequence_code' => $this->sequenceCode->generate('CUS', 5)
                ];
                $this->addNewCustomer($type, $company_data );
            }

        } else {
            $this->page_construct('user/login', $this->data);
        }
    }

    public function addNewCustomer($type, $company_data)
    {
        $username = strtolower($this->input->post('identity'));
        $email = strtolower($this->input->post('identity'));

        if ($type == 'email') {
            $company_data['email'] = $this->input->post('identity');
        } elseif ($type == 'mobile') {
            $company_data['phone'] = $this->input->post('identity');
        }

        $company_id = $this->shop_model->addUniqueCustomer($company_data);

        $additional_data = [
            'gender' => 'male',
            'company_id' => $company_id,
            'group_id' => 3,
        ];
        $this->load->library('ion_auth');

        $this->ion_auth->register($username, '12345', $email, $additional_data, false, false);

        if ($this->form_validation->run() == true) {
            $this->sendOTP($company_id, $email, $type);

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Something went wrong!']);
        }
    }


    /*public function verify_phone(){
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->shop_model->getCompanyByID($company_id);

        if($company_data->mobile_verified == 0){
            $mobile = $company_data->phone;

            $otp_sent = $this->sendOTP($company_id, $mobile, 'mobile');

        }else{
            echo json_encode(['status' => 'error', 'message' => 'Mobile already verified']);
        }

    }*/

    public function set_shipping_phone()
    {
        $address_id = $this->input->get('address_id');
        $mobile_number = $this->input->get('mobile_number');

        if ($address_id == 'default') {
            $verified_data = $this->shop_model->get_company_details($this->session->userdata('company_id'));
        } else {
            $verified_data = $this->shop_model->get_activate_phone($this->session->userdata('company_id'), $mobile_number, $address_id);
        }

        $this->session->set_userdata('changed_address', $verified_data);

        echo json_encode(['status' => 'success', 'message' => 'Address changed success']);
    }

    public function verify_phone_otp()
    {
        $this->form_validation->set_rules('identifier_input', lang('Mobile'), 'required');
        if ($this->form_validation->run('') == true) {
            $identity = strtolower($this->input->post('identifier_input'));
            $opt_part1 = strtolower($this->input->post('opt_part1'));
            $opt_part2 = strtolower($this->input->post('opt_part2'));
            $opt_part3 = strtolower($this->input->post('opt_part3'));
            $opt_part4 = strtolower($this->input->post('opt_part4'));
            $opt_part5 = strtolower($this->input->post('opt_part5'));
            $opt_part6 = strtolower($this->input->post('opt_part6'));

            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true) {

            $otp = $opt_part1 . $opt_part2 . $opt_part3 . $opt_part4 . $opt_part5 . $opt_part6;

            $validate = $this->shop_model->validate_otp($identity, $otp);
            if ($validate) {

                if ($this->input->post('change_phone')) {
                    $address_id = $this->input->post('selected_add_id');
                    $verified_data = $this->shop_model->get_activate_phone($this->session->userdata('company_id'), $identity, $address_id);
                    if ($this->shop_model->activate_phone($this->session->userdata('company_id'), $identity, $address_id)) {
                        $this->session->set_userdata('changed_address', $verified_data);
                    }
                }

                echo json_encode(['status' => 'success', 'message' => 'Otp verified']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Otp verification failed']);
            }

        }
    }

    public function activate_phone()
    {
        $this->form_validation->set_rules('mobile_number', lang('Mobile'), 'required');
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->shop_model->getCompanyByID($company_id);

        $mobile = $this->input->get('mobile_number');

        if ($this->shop_model->activate_phone($company_id, $mobile)) {
            echo json_encode(['status' => 'success', 'message' => 'Mobile activated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Mobile activation failed']);
        }
    }

    public function verify_phone()
    {
        $this->form_validation->set_rules('mobile_number', lang('Mobile'), 'required');

        $company_id = $this->session->userdata('company_id');
        $company_data = $this->shop_model->getCompanyByID($company_id);
        //get customer verified numbers
        $verify_phone_numbers = $this->shop_model->getCustomerVerifiedNumbers();
       
        if ($company_data) {
            if ($this->input->post('mobile_number')) {
                $mobile = $this->input->post('mobile_number');
            } else {
                $mobile = $this->input->get('mobile_number');
            }

            if (in_array($mobile, $verify_phone_numbers)) {
                echo json_encode(['status' => 'verified', 'message' => 'Already Verified']);
                exit;
            }

            $otp_sent = $this->sendOTP($company_id, $mobile, 'mobile');
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User data does not exist']);
        }
    }

    public function register()
    {
        if ($this->shop_settings->private) {
            redirect('/login');
        }

        $this->form_validation->set_rules('email', lang('email_address'), 'required');

        if ($this->form_validation->run('') == true) {
            ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
            $email = strtolower($this->input->post('email'));
            $username = strtolower($this->input->post('email'));

            $customer_group = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
            $price_group = $this->shop_model->getPriceGroup($this->Settings->price_group);

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $type = 'email';
                $company_found = $this->shop_model->getUniqueCustomer('email', $email);
            } else {
                $type = 'mobile';
                $company_found = $this->shop_model->getUniqueCustomer('mobile', str_replace("+966", "", $email));
            }

            if ($company_found) {
                if (!empty($company_found->email)) {
                    $user_data = $this->shop_model->getUserByEmail($company_found->email);
                } else {
                    $user_data = $this->shop_model->getUserByEmail($company_found->phone);
                }

                $this->load->library('ion_auth');

                if ($this->form_validation->run() == true) {

                    $this->sendOTP($company_found->id, $email, $type);

                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Register Validation Failed']);
                }

            } else if ($type == 'email' || $type == 'mobile') {
                $company_data = [
                    'group_id' => 3,
                    'group_name' => 'customer',
                    'customer_group_id' => (!empty($customer_group)) ? $customer_group->id : null,
                    'customer_group_name' => (!empty($customer_group)) ? $customer_group->name : null,
                    'price_group_id' => (!empty($price_group)) ? $price_group->id : null,
                    'price_group_name' => (!empty($price_group)) ? $price_group->name : null,
                    'sequence_code' => $this->sequenceCode->generate('CUS', 5)
                ];

                if ($type == 'email') {
                    $company_data['email'] = $this->input->post('email');
                } elseif ($type == 'mobile') {
                    $company_data['phone'] = $this->input->post('email');
                }

                //$company_id = $this->shop_model->addCustomer($company_data);
                $company_id = $this->shop_model->addUniqueCustomer($company_data);

                $additional_data = [
                    'gender' => 'male',
                    'company_id' => $company_id,
                    'group_id' => 3,
                ];
                $this->load->library('ion_auth');

                $this->ion_auth->register($username, '12345', $email, $additional_data, false, false);

                if ($this->form_validation->run() == true) {
                    $this->sendOTP($company_id, $email, $type);

                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Email Validation Failed']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data not found in system']);
            }

        }

    }

    public function reset_password($code = null)
    {
        if (!SHOP) {
            redirect('admin/auth/reset_password/' . $code);
        }
        if (!$code) {
            $this->session->set_flashdata('error', lang('page_not_found'));
            redirect('/');
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            //required|min_length[5]|max_length[20]|matches[password_confirm]
            $this->form_validation->set_rules('new', lang('password'), 'required|min_length[5]|max_length[20]|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', lang('confirm_password'), 'required');

            if ($this->form_validation->run() == false) {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['message'] = $this->session->flashdata('message');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = [
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'class' => 'form-control',
                    'required' => 'required',
                    'pattern' => '.{5,20}',
                    'data-fv-regexp-message' => lang('pasword_hint'),
                    'placeholder' => lang('new_password'),
                ];
                $this->data['new_password_confirm'] = [
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'class' => 'form-control',
                    'required' => 'required',
                    'data-fv-identical' => 'true',
                    'data-fv-identical-field' => 'new',
                    'data-fv-identical-message' => lang('pw_not_same'),
                    'placeholder' => lang('confirm_password'),
                ];
                $this->data['user_id'] = [
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                ];
                $this->data['code'] = $code;
                $this->data['identity_label'] = $user->email;
                $this->data['page_title'] = lang('reset_password');
                $this->data['page_desc'] = '';
                $this->page_construct('user/reset_password', $this->data);
            } else {
                // do we have a valid request?
                if ($user->id != $this->input->post('user_id')) {
                    $this->ion_auth->clear_forgotten_password_code($code);
                    redirect('notify/csrf');
                } else {
                    // finally change the password
                    $identity = $user->email;

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));
                    if ($change) {
                        //if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        redirect('login');
                    } else {
                        $this->session->set_flashdata('error', $this->ion_auth->errors());
                        redirect('reset_password/' . $code);
                    }
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect('/');
        }
    }

    public function notify_me()
    {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Get the post data
            $notify_email = $this->input->post('notify_email');
            $product_input = $this->input->post('product_input');

            // Validate email
            if (empty($notify_email) || !filter_var($notify_email, FILTER_VALIDATE_EMAIL)) {
                return $this->sma->send_json(['status' => 'error', 'color' => '#FF5252', 'message' => 'Invalid or empty email address.']);
            }

            if (empty($product_input) || !is_numeric($product_input) || $product_input <= 0) {
                return $this->sma->send_json(['status' => 'error', 'color' => '#FF5252', 'message' => 'Please select product.']);
            }

            // Check if the email already exists for the given product_id
            $existing_data = $this->Shop_model->get_notify_data($notify_email, $product_input);
            if ($existing_data > 0) {
                return $this->sma->send_json(['status' => 'info', 'color' => '#2196F3', 'message' => 'Email already added for this product.']);
            }

            // Insert into the database
            $data_to_insert = [
                'email' => $notify_email,
                'product_id' => $product_input,
                'date_created' => date('Y-m-d H:i:s')
                // Add other fields as needed
            ];

            $insert_result = $this->Shop_model->insert_notify_data($data_to_insert);

            if ($insert_result) {
                return $this->sma->send_json(['status' => 'success', 'color' => '#4CAF50', 'message' => 'Successfully saved!']);
            } else {
                return $this->sma->send_json(['status' => 'error', 'color' => '#FF5252', 'message' => 'Failed to save data.']);
            }
        }

        // Handle non-POST requests if needed
        return $this->sma->send_json(['status' => 'error', 'color' => '#FF5252', 'message' => 'Invalid request.']);
    }

}
