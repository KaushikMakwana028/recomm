<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller 
{
  public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('General_model', 'gm');
        
      
        $method = $this->router->fetch_method();
        if ($method !== 'logout') {
            // If already logged in, redirect to dashboard
            if ($this->session->userdata('admin_logged_in') === TRUE || $this->session->userdata('admin_logged_in') === 1) {
                redirect('dashboard');
            }
        }
    }

    /**
     * Login page
     */
    public function index()
    {
        $this->load->view('auth/login');
    }

    /**
     * Send OTP to mobile
     */
    public function send_otp()
    {
        if ($this->input->is_ajax_request()) {
            $mobile = $this->input->post('mobile', true);
            
            // Validate mobile number
            if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Please enter a valid 10-digit mobile number'
                ]);
                return;
            }
            
            // Check if user exists and is admin
            $user = $this->gm->getOne('users', ['mobile' => $mobile, 'role' => 'admin']);
            
            if (!$user) {
                echo json_encode([
                    'status' => false,
                    'message' => 'No admin account found with this mobile number'
                ]);
                return;
            }
            
            if ($user->is_active != 1) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Your account is inactive. Please contact support.'
                ]);
                return;
            }
            
            // Generate OTP (in development, always use 123456)
            $otp = '123456';
            
            // Delete old OTPs for this mobile
            $this->gm->delete('otp_verification', ['mobile' => $mobile]);
            
            // Save OTP to database
            $otp_data = [
                'mobile' => $mobile,
                'otp' => $otp,
                'is_verified' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
            ];
            
            $this->gm->insert('otp_verification', $otp_data);
            
            // Send OTP via SMS (in development mode, it will not send actual SMS)
            $sms_sent = $this->send_otp_via_sms($mobile, $otp);
            
            echo json_encode([
                'status' => true,
                'message' => 'OTP sent successfully to your mobile number',
                'dev_otp' => $otp // Remove this in production
            ]);
        }
    }

    /**
     * Verify OTP and login
     */
    public function verify_otp()
    {
        if ($this->input->is_ajax_request()) {
            $mobile = $this->input->post('mobile', true);
            $otp = $this->input->post('otp', true);
            
            // Validate inputs
            if (empty($mobile) || empty($otp)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Mobile number and OTP are required'
                ]);
                return;
            }
            
            // Check OTP
            $otp_record = $this->gm->getOne('otp_verification', [
                'mobile' => $mobile,
                'otp' => $otp,
                'is_verified' => 0
            ]);
            
            if (!$otp_record) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid OTP'
                ]);
                return;
            }
            
            // Check if OTP expired
            if (strtotime($otp_record->expires_at) < time()) {
                echo json_encode([
                    'status' => false,
                    'message' => 'OTP has expired. Please request a new one.'
                ]);
                return;
            }
            
            // Get user
            $user = $this->gm->getOne('users', ['mobile' => $mobile, 'role' => 'admin']);
            
            if (!$user) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User not found'
                ]);
                return;
            }
            
            // Mark OTP as verified
            $this->gm->update('otp_verification', 
                ['is_verified' => 1], 
                ['id' => $otp_record->id]
            );
            
            // Set session data - DON'T destroy session first
            $session_data = [
                'admin_id' => (int)$user->id,
                'admin_name' => $user->name,
                'admin_mobile' => $user->mobile,
                'admin_email' => $user->email,
                'admin_role' => $user->role,
                'admin_logged_in' => 1  // Use integer 1 for consistency
            ];
            
            // Set all session data at once
            $this->session->set_userdata($session_data);
            
            // Mark session as set (additional flag)
            $this->session->mark_as_flash('login_success');
            $this->session->keep_flashdata('login_success');
            
            // Log for debugging
            log_message('info', 'Session created for user: ' . $user->mobile);
            log_message('info', 'Session data: ' . print_r($session_data, true));
            log_message('info', 'All session after set: ' . print_r($this->session->all_userdata(), true));
            
            echo json_encode([
                'status' => true,
                'message' => 'Login successful',
                'redirect' => base_url('dashboard')
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->sess_destroy();
        // print_r($_SESSION);
        // die;
        redirect('login');
    }

    /**
     * Send OTP via SMS (Development Mode)
     */
    private function send_otp_via_sms($mobile, $otp)
    {
        // In development mode, always return true
        // In production, integrate with SMS gateway
        
        // For development, log OTP to file
        log_message('info', "OTP for {$mobile}: {$otp}");
        
        return true;
    }
}