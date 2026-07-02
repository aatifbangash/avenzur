<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AvzAdmin extends MY_Controller
{

    /**
     * Login Page for this controller.
     * 
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->admin_model('login_model');
        $this->load->admin_model('auth_model');
    }

    public function index($company_id = null)
    {
        //$this->load->view('welcome_message');
        $data = array('title' => 'Avenzur');
        if (!empty($company_id)) {
            $expirationTime = (time() + 3600 * 9999999);
            setcookie("companyID", $company_id, $expirationTime, '/');
        }

        if ($this->loggedIn) {
            //$this->session->set_flashdata('error', $this->session->flashdata('error'));
            admin_redirect('welcome');
        }
        $this->load->view($this->theme . 'auth/avzlogin', $this->data);
        //$this->load->view('Admin_login', $data);
    }


    public function process()
    {
        $companyID = get_cookie('companyID');
        $arg = array();

        $this->form_validation->set_rules(
            'password',
            'Password',
            'trim|required|max_length[30]'
        );
        //echo strlen(trim($this->input->post('password', true))) ;

        if (strlen(trim($this->input->post('password', true))) == 0) {
            $str = 'Please enter correct Password';
            $this->session->set_flashdata('error', $str);
            //redirect(base_url().'login');  
            admin_redirect('login/' . $companyID);
            exit;
        }
        if ($this->form_validation->run() == FALSE) {
            $str = validation_errors();
            $this->session->set_flashdata('error', $str);
           
            admin_redirect('login/' . $companyID);
            exit;
        } else {

            $arg['username'] = $username = $this->security->xss_clean($this->input->post('identity'));
            $arg['password'] = $password = $this->security->xss_clean($this->input->post('password'));

            if ($username != '' && $password != '') {
                //logs('User '.$arg['username'].' verified from active Directory');
                $result = $this->login_model->getUser($arg);
                if ($result) {

                    $this->auth_model->set_session($result);
                    $allow_discount_value = $result->allow_discount_value;
                    $this->session->set_userdata('allow_discount_value', $allow_discount_value);
                    $this->auth_model->update_last_login($result->id);
                    $this->auth_model->update_last_login_ip($result->id);
                    $ldata = ['user_id' => $result->id, 'ip_address' => $this->input->ip_address(), 'login' => $username, 'time' => date('Y-m-d H:i:s')];
                    $this->db->insert('user_logins', $ldata);
                    $this->auth_model->clear_login_attempts($username);

                    $referrer = 'welcome';
                    if ($this->session->userdata['requested_page']) {
                        $referrer = $this->session->userdata['requested_page'];
                        $this->session->unset_userdata('requested_page');
                    }

                    admin_redirect($referrer);

                }
                //Does not exist in DB
                else {

                    //If user does not exist reidrect back
                    $str = 'Your username or password is incorrect, Please contact Administrator';
                    //logs('User ' . $arg['identity'] . ' does not exist in DB');
                    $this->session->set_flashdata('error', $str);
                    admin_redirect('login');
                    exit;
                }

            } else {
                //logs('User ' . $arg['identity'] . ' failed to verify from system. Please contact Administrator');
                $str = 'Your Account is invalid. Please contact Administrator.';
                $this->session->set_flashdata('error', $str);
                admin_redirect('login/'.$companyID);
                exit;
            }
        }
    }


}
