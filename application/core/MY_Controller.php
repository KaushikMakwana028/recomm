<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * 
 * Base controller that all admin controllers extend
 * Handles authentication, loads common libraries and helpers
 */
class MY_Controller extends CI_Controller 
{
    protected $data = [];
    protected $admin_data = [];

    public function __construct()
    {
        parent::__construct();
        
        // Load common libraries
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('pagination');
        $this->load->library('upload');
        $this->load->library('email');
        
        // Load common helpers
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('security');
        $this->load->helper('text');
        $this->load->helper('date');
        $this->load->helper('file');
        
        // Load General Model
        $this->load->model('General_model', 'gm');
        
        // Set timezone
        date_default_timezone_set('Asia/Kolkata');
        
        // Check authentication
        $this->checkAuth();
        
        // Common data for all views
        $this->data['site_name'] = 'Recomm Admin';
        $this->data['admin_data'] = $this->admin_data;
    }

    /**
     * Check if admin is logged in
     */
    private function checkAuth()
    {
        // Get current controller and method
        $controller = $this->router->fetch_class();
        $method = $this->router->fetch_method();
        
        // Controllers that don't require authentication
        $public_controllers = ['login'];
        
        // Skip authentication for public controllers
        if (in_array(strtolower($controller), $public_controllers)) {
            return;
        }
        
        // Debug: Log all session data
        $all_session = $this->session->all_userdata();
        log_message('info', 'Checking auth - Session data: ' . print_r($all_session, true));
        
        // Get admin_logged_in value
        $admin_logged_in = $this->session->userdata('admin_logged_in');
        log_message('info', 'admin_logged_in value: ' . var_export($admin_logged_in, true));
        log_message('info', 'admin_logged_in type: ' . gettype($admin_logged_in));
        
        // Check if admin is logged in - Accept both TRUE and 1
        if ($admin_logged_in !== TRUE && $admin_logged_in !== 1 && $admin_logged_in !== '1') {
            log_message('info', 'Auth failed - admin_logged_in is not set correctly');
            $this->session->set_flashdata('message_type', 'warning');
            $this->session->set_flashdata('message', 'Please login to continue');
            redirect('login');
            exit;
        }
        
        // Get admin data from session
        $admin_id = $this->session->userdata('admin_id');
        
        if ($admin_id) {
            $admin = $this->gm->getById('users', $admin_id);
            
            if ($admin && $admin->is_active == 1) {
                $this->admin_data = $admin;
                log_message('info', 'Auth successful for user: ' . $admin->name);
            } else {
                // Admin inactive or not found
                log_message('info', 'Admin inactive or not found');
                $this->session->sess_destroy();
                redirect('login');
                exit;
            }
        } else {
            log_message('info', 'No admin_id in session');
            redirect('login');
            exit;
        }
    }

    /**
     * Set page title
     */
    protected function setPageTitle($title)
    {
        $this->data['page_title'] = $title . ' - ' . $this->data['site_name'];
    }

    /**
     * Load admin view with layout
     */
    protected function loadView($view, $data = [])
    {
        $data = array_merge($this->data, $data);
        
        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view($view, $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * JSON Response
     */
    protected function jsonResponse($status = true, $message = '', $data = [])
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Upload file
     */
    protected function uploadFile($field_name, $upload_path, $allowed_types = 'jpg|jpeg|png|gif')
    {
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload($field_name)) {
            return $this->upload->data();
        }
        
        return false;
    }

    /**
     * Set flashdata message
     */
    protected function setMessage($type, $message)
    {
        $this->session->set_flashdata('message_type', $type);
        $this->session->set_flashdata('message', $message);
    }

    /**
     * Generate slug from string
     */
    protected function generateSlug($string)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
        return $slug;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax()
    {
        return $this->input->is_ajax_request();
    }

    /**
     * Get POST data
     */
    protected function post($key = null, $xss_clean = true)
    {
        if ($key === null) {
            return $this->input->post(null, $xss_clean);
        }
        return $this->input->post($key, $xss_clean);
    }

    /**
     * Get GET data
     */
    protected function get($key = null, $xss_clean = true)
    {
        if ($key === null) {
            return $this->input->get(null, $xss_clean);
        }
        return $this->input->get($key, $xss_clean);
    }
}