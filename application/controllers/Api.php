<?php

defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

use \Firebase\JWT\Key;

require_once FCPATH . 'vendor/autoload.php';



class Api extends CI_Controller
{


    private $jwt_secret = 'a0d5f8e9c2b7a6d1c4e3f98b19d2a4f6c9f7a31bc9e2d6f81d845a47b8f92c4e';




    public function __construct()
    {
        parent::__construct();

        // =========================
        // LOAD LIBRARIES / HELPERS
        // =========================
        $this->load->model('General_model');
        $this->load->library('upload');


        $this->load->library([
            'session',
            'email',
            'form_validation'
        ]);

        $this->load->helper([
            'url',
            'form'
        ]);

        // =========================
        // CORS HEADERS
        // =========================
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
        header("Content-Type: application/json; charset=UTF-8");

        // =========================
        // HANDLE PREFLIGHT REQUEST
        // =========================
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

            http_response_code(200);
            exit();
        }
    }

    public function register_send_otp()
    {
        $this->ensureMethod('POST');

        $this->output->set_content_type('application/json');

        $input = json_decode($this->input->raw_input_stream, true);

        $name      = trim($input['name'] ?? '');
        $mobile    = trim($input['mobile'] ?? '');
        $email     = trim($input['email'] ?? '');
        $shop_name = trim($input['shop_name'] ?? '');

        if ($name == '' || $mobile == '' || $shop_name == '') {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Name, Mobile and Shop Name are required.'
                ]));
        }

        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid mobile number.'
                ]));
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid email.'
                ]));
        }

        $exists = $this->db
            ->where('mobile', $mobile)
            ->get('users')
            ->row();

        if ($exists) {
            return $this->output
                ->set_status_header(409)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 409,
                    'message' => 'Mobile number already registered.'
                ]));
        }

        $otp = '123456';

        $this->db->where('mobile', $mobile)->delete('user_registration_otps');

        $this->db->insert('user_registration_otps', [
            'mobile' => $mobile,
            'name' => $name,
            'email' => $email ?: NULL,
            'shop_name' => $shop_name,
            'otp' => $otp,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        //$this->send_otp_via_sms($mobile,$otp);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'OTP sent successfully.',

            ]));
    }
    public function verify_register_otp()
    {
        $this->ensureMethod('POST');

        $this->output->set_content_type('application/json');

        $input = json_decode($this->input->raw_input_stream, true);

        $mobile = trim($input['mobile'] ?? '');
        $otp = trim($input['otp'] ?? '');

        if ($mobile == '' || $otp == '') {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Mobile and OTP are required.'
                ]));
        }

        $otpRow = $this->db
            ->where('mobile', $mobile)
            ->where('otp', $otp)
            ->order_by('id', 'DESC')
            ->get('user_registration_otps')
            ->row();

        if (!$otpRow) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Invalid OTP.'
                ]));
        }

        if (strtotime($otpRow->expires_at) < time()) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'OTP expired.'
                ]));
        }

        $userData = [
            'name' => $otpRow->name,
            'mobile' => $otpRow->mobile,
            'email' => $otpRow->email,
            'store_name' => $otpRow->shop_name,
            'role' => 'vendor',
            'is_active' => 1,
            'created_on' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('users', $userData);

        $userId = $this->db->insert_id();

        $user = $this->db
            ->where('id', $userId)
            ->get('users')
            ->row();

        $this->db->where('mobile', $mobile)->delete('user_registration_otps');

        $token = $this->generate_jwt($user);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Registration successful.',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'shop_name' => $user->store_name,
                        'role' => $user->role
                    ]
                ]
            ]));
    }
    public function verify_login_otp()
    {
        $this->ensureMethod('POST');

        $this->output->set_content_type('application/json');

        $input_data = json_decode($this->input->raw_input_stream, true);

        $mobile = trim($input_data['mobile'] ?? '');
        $otp    = trim($input_data['otp'] ?? '');

        // =========================
        // VALIDATION
        // =========================
        if (
            empty($mobile) ||
            empty($otp)
        ) {

            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Mobile and OTP are required',
                    'data' => null
                ]));
        }

        // =========================
        // CHECK USER
        // =========================
        $user = $this->db
            ->where('mobile', $mobile)
            ->get('users')
            ->row();

        if (!$user) {

            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'User not found',
                    'data' => null
                ]));
        }

        // =========================
        // CHECK OTP
        // =========================
        $otp_row = $this->db
            ->where('mobile', $mobile)
            ->where('otp', $otp)
            ->order_by('id', 'DESC')
            ->get('user_login_otps')
            ->row();

        if (!$otp_row) {

            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Invalid OTP',
                    'data' => null
                ]));
        }

        if (
            strtotime($otp_row->expires_at) < time()
        ) {

            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'OTP expired',
                    'data' => null
                ]));
        }

        // =========================
        // DELETE OTP
        // =========================
        $this->db
            ->where('mobile', $mobile)
            ->delete('user_login_otps');

        // =========================
        // JWT TOKEN
        // =========================
        $token = $this->generate_jwt($user);

        // =========================
        // RESPONSE
        // =========================
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'role' => $user->role,
                        'isActive' => $user->is_active,
                        'created_at' => $user->created_on
                    ]
                ]
            ]));
    }
    public function login_send_otp()
    {
        $this->ensureMethod('POST');

        $this->output->set_content_type('application/json');

        $input_data = json_decode($this->input->raw_input_stream, true);

        $mobile = trim($input_data['mobile'] ?? '');

        // =========================
        // VALIDATION
        // =========================
        if (
            empty($mobile) ||
            !preg_match('/^[0-9]{10}$/', $mobile)
        ) {

            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Valid mobile number is required',
                    'data' => null
                ]));
        }

        // =========================
        // CHECK USER
        // =========================
        $user = $this->db
            ->where('mobile', $mobile)
            ->get('users')
            ->row();

        if (!$user) {

            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'User not found',
                    'data' => null
                ]));
        }

        // =========================
        // ACCOUNT STATUS
        // =========================
        if ($user->is_active == 0) {

            return $this->output
                ->set_status_header(403)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 403,
                    'message' => 'Your account is not active',
                    'data' => null
                ]));
        }

        // =========================
        // STATIC OTP
        // =========================
        $otp = '123456';

        // delete old otp
        $this->db
            ->where('mobile', $mobile)
            ->delete('user_login_otps');

        // insert new otp
        $this->db->insert('user_login_otps', [
            'mobile' => $mobile,
            'otp' => $otp,
            'expires_at' => date(
                'Y-m-d H:i:s',
                strtotime('+10 minutes')
            ),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // later enable SMS
        // $this->send_otp_via_sms($mobile, $otp);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'OTP sent successfully',
                'masked_mobile' => '******' . substr($mobile, -4)

                // // Uncomment during testing
                // ,'otp' => $otp
            ]));
    }

    public function get_profile($id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        if (!$id) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'User ID is required',
                'data' => null
            ]));
        }

        $user = $this->db
            ->select('id, name, mobile, email, store_name, owner_name, gst_number, contact_number, address, opening_time, closing_time, is_holiday, profile_image, store_photo, role, is_active, created_on, account_holder_name, bank_name, account_number, ifsc_code, account_type, branch_name')
            ->where('id', $id)
            ->get('users')
            ->row();

        if (!$user) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'User not found',
                'data' => null
            ]));
        }

        // Only allow self profile (admin in vendor token won't pass this unless id matches)
        if ((int)$decoded->data->id !== (int)$id) {
            return $this->output->set_status_header(403)->set_output(json_encode([
                'status' => false,
                'code' => 403,
                'message' => 'You can only access your own profile',
                'data' => null
            ]));
        }

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Profile fetched successfully',
            'data' => [
                'id' => (int)$user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'shop_name' => $user->store_name,
                'owner_name' => $user->owner_name,
                'gst_number' => $user->gst_number,
                'contact_number' => $user->contact_number,
                'address' => $user->address,
                'opening_time' => $user->opening_time,
                'closing_time' => $user->closing_time,
                'is_holiday' => (int)$user->is_holiday,
                'profile_image' => !empty($user->profile_image) ? base_url($user->profile_image) : null,
                'store_photo' => !empty($user->store_photo) ? base_url($user->store_photo) : null,
                'role' => $user->role,
                'is_active' => (int)$user->is_active,
                'created_at' => $user->created_on,
                'account_holder_name' => $user->account_holder_name,
                'bank_name' => $user->bank_name,
                'account_number' => $user->account_number,
                'ifsc_code' => $user->ifsc_code,
                'account_type' => $user->account_type,
                'branch_name' => $user->branch_name
            ]
        ]));
    }

    public function update_profile()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $user_id = $decoded->data->id;

        // Check if user exists
        $user = $this->db->where('id', $user_id)->get('users')->row();

        if (!$user) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'User not found',
                    'data' => null
                ]));
        }

        // Prepare update data array (only fields that are sent)
        $update_data = [];

        // Text fields
        if ($this->input->post('name')) {
            $update_data['name'] = trim($this->input->post('name'));
        }

        if ($this->input->post('email')) {
            $email = trim($this->input->post('email'));
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Invalid email format',
                        'data' => null
                    ]));
            }
            $update_data['email'] = $email;
        }

        if ($this->input->post('shop_name')) {
            $update_data['store_name'] = trim($this->input->post('shop_name'));
        }

        if ($this->input->post('owner_name')) {
            $update_data['owner_name'] = trim($this->input->post('owner_name'));
        }

        if ($this->input->post('gst_number')) {
            $update_data['gst_number'] = trim($this->input->post('gst_number'));
        }

        if ($this->input->post('contact_number')) {
            $update_data['contact_number'] = trim($this->input->post('contact_number'));
        }

        if ($this->input->post('address')) {
            $update_data['address'] = trim($this->input->post('address'));
        }

        if ($this->input->post('opening_time')) {
            $update_data['opening_time'] = $this->input->post('opening_time');
        }

        if ($this->input->post('closing_time')) {
            $update_data['closing_time'] = $this->input->post('closing_time');
        }
        if ($this->input->post('is_holiday') !== null) {
            $val = $this->input->post('is_holiday');
            $update_data['is_holiday'] = in_array(strtolower((string)$val), ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
        }

        // Bank detail fields
        if ($this->input->post('account_holder_name')) {
            $update_data['account_holder_name'] = trim($this->input->post('account_holder_name'));
        }

        if ($this->input->post('bank_name')) {
            $update_data['bank_name'] = trim($this->input->post('bank_name'));
        }

        if ($this->input->post('account_number')) {
            $update_data['account_number'] = trim($this->input->post('account_number'));
        }

        if ($this->input->post('ifsc_code')) {
            $update_data['ifsc_code'] = strtoupper(trim($this->input->post('ifsc_code')));
        }

        if ($this->input->post('account_type')) {
            $account_type = trim($this->input->post('account_type'));
            $allowed_account_types = ['current', 'savings', 'business', 'cash_credit', 'overdraft', 'joint'];
            if (!in_array($account_type, $allowed_account_types, true)) {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Invalid account type',
                        'data' => null
                    ]));
            }
            $update_data['account_type'] = $account_type;
        }

        if ($this->input->post('branch_name')) {
            $update_data['branch_name'] = trim($this->input->post('branch_name'));
        }

        // Handle profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $upload_path = './assets/uploads/users/';

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('profile_image')) {
                // Delete old image
                if ($user->profile_image && file_exists($user->profile_image)) {
                    unlink($user->profile_image);
                }

                $upload_data = $this->upload->data();
                $update_data['profile_image'] = 'assets/uploads/users/' . $upload_data['file_name'];
            } else {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => $this->upload->display_errors('', ''),
                        'data' => null
                    ]));
            }
        }

        // Handle store photo upload
        if (!empty($_FILES['store_photo']['name'])) {
            $upload_path = './assets/uploads/stores/';

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('store_photo')) {
                // Delete old store photo
                if ($user->store_photo && file_exists($user->store_photo)) {
                    unlink($user->store_photo);
                }

                $upload_data = $this->upload->data();
                $update_data['store_photo'] = 'assets/uploads/stores/' . $upload_data['file_name'];
            } else {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => $this->upload->display_errors('', ''),
                        'data' => null
                    ]));
            }
        }

        // Check if any data to update
        if (empty($update_data)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'No data to update',
                    'data' => null
                ]));
        }

        // Update database
        $this->db->where('id', $user_id)->update('users', $update_data);

        // Get updated user
        $updated_user = $this->db
            ->select('id, name, mobile, email, store_name, owner_name, gst_number, contact_number, address, opening_time, closing_time, is_holiday, profile_image, store_photo, role, is_active, created_on, account_holder_name, bank_name, account_number, ifsc_code, account_type, branch_name')
            ->where('id', $user_id)
            ->get('users')
            ->row();

        // Success response
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => (int)$updated_user->id,
                    'name' => $updated_user->name,
                    'mobile' => $updated_user->mobile,
                    'email' => $updated_user->email,
                    'store_name' => $updated_user->store_name,
                    'owner_name' => $updated_user->owner_name,
                    'gst_number' => $updated_user->gst_number,
                    'contact_number' => $updated_user->contact_number,
                    'address' => $updated_user->address,
                    'opening_time' => $updated_user->opening_time,
                    'closing_time' => $updated_user->closing_time,
                    'is_holiday' => (int)$updated_user->is_holiday,
                    'profile_image' => $updated_user->profile_image ? base_url($updated_user->profile_image) : null,
                    'store_photo' => $updated_user->store_photo ? base_url($updated_user->store_photo) : null,
                    'role' => $updated_user->role,
                    'is_active' => (int)$updated_user->is_active,
                    'created_at' => $updated_user->created_on,
                    'account_holder_name' => $updated_user->account_holder_name,
                    'bank_name' => $updated_user->bank_name,
                    'account_number' => $updated_user->account_number,
                    'ifsc_code' => $updated_user->ifsc_code,
                    'account_type' => $updated_user->account_type,
                    'branch_name' => $updated_user->branch_name
                ]
            ]));
    }

    public function categories()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        // Get categories (only active)
        $categories = $this->db
            ->select('id, name, slug, description, image, parent_id, created_on')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('categories')
            ->result();

        // Format response
        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => (int)$category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image ? base_url($category->image) : null,
                'parent_id' => (int)$category->parent_id,
                'created_at' => $category->created_on
            ];
        }

        // Success response
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Categories fetched successfully',
                'data' => $data
            ]));
    }
    public function get_products()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $vendor_id = $decoded->data->id;

        // Get parameters
        $type = $this->input->get('type'); // 'admin' or 'vendor'
        $search = trim($this->input->get('search') ?? '');
        $category_id = $this->input->get('category_id');

        // ====================================
        // CASE 1: Get VENDOR's OWN products
        // ====================================
        if ($type === 'vendor') {

            $this->db->select('vp.*, p.image as admin_image, c.name as category_name');
            $this->db->from('vendor_products vp');
            $this->db->join('products p', 'p.id = vp.product_id', 'left');
            $this->db->join('categories c', 'c.id = vp.category_id', 'left');
            $this->db->where('vp.vendor_id', $vendor_id);
            $this->db->where('vp.is_active', 1);

            // Search filter
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('vp.product_name', $search);
                $this->db->or_like('vp.brand', $search);
                $this->db->or_like('vp.description', $search);
                $this->db->group_end();
            }

            // Category filter
            if (!empty($category_id)) {
                $this->db->where('vp.category_id', $category_id);
            }

            $products = $this->db
                ->order_by('vp.product_name', 'ASC')
                ->get()
                ->result();

            // Format response
            $data = [];
            foreach ($products as $product) {
                // Determine image source
                $product_image = null;
                if ($product->product_id !== null) {
                    // Admin product - use admin image
                    $product_image = $product->admin_image ? base_url($product->admin_image) : null;
                } else {
                    // Vendor's own product - use vendor image
                    $product_image = $product->image ? base_url($product->image) : null;
                }

                $data[] = [
                    'id' => (int)$product->id,
                    'product_id' => $product->product_id ? (int)$product->product_id : null,
                    'product_name' => $product->product_name,
                    'brand' => $product->brand,
                    'category_id' => (int)$product->category_id,
                    'category_name' => $product->category_name,
                    'unit' => $product->unit,
                    'mrp' => (float)$product->mrp,
                    'selling_price' => (float)$product->selling_price,
                    'stock' => (int)$product->stock,
                    'description' => $product->description,
                    'image' => $product_image,
                    'is_own_product' => $product->product_id === null,
                    'added_on' => $product->added_on
                ];
            }

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Your products fetched successfully',
                    'data' => $data
                ]));
        }

        // ====================================
        // CASE 2: Get ADMIN products (default)
        // ====================================
        else {

            $this->db->select('p.id, p.name, p.slug, p.description, p.image, p.price, p.sale_price, p.sku, p.category_id, c.name as category_name');
            $this->db->from('products p');
            $this->db->join('categories c', 'c.id = p.category_id', 'left');
            $this->db->where('p.is_active', 1);
            $this->db->where('p.created_by', 'admin');

            // Search filter
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('p.name', $search);
                $this->db->or_like('p.sku', $search);
                $this->db->or_like('p.description', $search);
                $this->db->group_end();
            }

            // Category filter
            if (!empty($category_id)) {
                $this->db->where('p.category_id', $category_id);
            }

            $products = $this->db
                ->order_by('p.name', 'ASC')
                ->limit(50)
                ->get()
                ->result();

            // Format response
            $data = [];
            foreach ($products as $product) {
                $data[] = [
                    'id' => (int)$product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'image' => $product->image ? base_url($product->image) : null,
                    'price' => (float)$product->price,
                    'sale_price' => $product->sale_price ? (float)$product->sale_price : null,
                    'sku' => $product->sku,
                    'category_id' => (int)$product->category_id,
                    'category_name' => $product->category_name
                ];
            }

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Admin products fetched successfully',
                    'data' => $data
                ]));
        }
    }
    public function product_details($id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $vendor_id = $decoded->data->id;

        // Validate ID
        if (!$id) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        // Get product details
        $product = $this->db
            ->select('vp.*, p.image as admin_image, c.name as category_name')
            ->from('vendor_products vp')
            ->join('products p', 'p.id = vp.product_id', 'left')
            ->join('categories c', 'c.id = vp.category_id', 'left')
            ->where('vp.id', $id)
            ->where('vp.vendor_id', $vendor_id)
            ->get()
            ->row();

        if (!$product) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not found or access denied',
                    'data' => null
                ]));
        }

        // Determine image source
        $product_image = null;
        if ($product->product_id !== null) {
            // Admin product - use admin image
            $product_image = $product->admin_image ? base_url($product->admin_image) : null;
        } else {
            // Vendor's own product - use vendor image
            $product_image = $product->image ? base_url($product->image) : null;
        }

        // Check if it's own product
        $is_own = $product->product_id === null;

        // Format response
        $data = [
            'id' => (int)$product->id,
            'product_id' => $product->product_id ? (int)$product->product_id : null,
            'product_name' => $product->product_name,
            'brand' => $product->brand,
            'category_id' => (int)$product->category_id,
            'category_name' => $product->category_name,
            'unit' => $product->unit,
            'mrp' => (float)$product->mrp,
            'selling_price' => (float)$product->selling_price,
            'stock' => (int)$product->stock,
            'description' => $product->description,
            'image' => $product_image,
            'is_active' => (int)$product->is_active,
            'is_own' => $is_own,
            'can_edit_image' => $is_own,
            'added_on' => $product->added_on,
            'updated_on' => $product->updated_on
        ];

        // Success response
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Product details fetched successfully',
                'data' => $data
            ]));
    }
    public function add_vendor_product()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $vendor_id = $decoded->data->id;

        // Get form data
        $product_id = $this->input->post('product_id'); // If selecting admin product
        $product_name = trim($this->input->post('product_name'));
        $brand = trim($this->input->post('brand'));
        $category_id = $this->input->post('category_id');
        $unit = trim($this->input->post('unit'));
        $mrp = $this->input->post('mrp');
        $selling_price = $this->input->post('selling_price');
        $stock = $this->input->post('stock');
        $description = trim($this->input->post('description') ?? '');


        // Basic validation
        if (empty($product_name)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product name is required',
                    'data' => null
                ]));
        }

        if (empty($category_id)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Category is required',
                    'data' => null
                ]));
        }

        if (empty($mrp) || $mrp <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'MRP is required',
                    'data' => null
                ]));
        }

        if (empty($selling_price) || $selling_price <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Selling price is required',
                    'data' => null
                ]));
        }

        // CASE 1: Vendor selecting admin product
        if (!empty($product_id)) {

            // Check if admin product exists
            $admin_product = $this->db
                ->where('id', $product_id)
                ->where('created_by', 'admin')
                ->where('is_active', 1)
                ->get('products')
                ->row();

            if (!$admin_product) {
                return $this->output
                    ->set_status_header(404)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 404,
                        'message' => 'Product not found',
                        'data' => null
                    ]));
            }

            // Check if vendor already added this product
            $existing = $this->db
                ->where('vendor_id', $vendor_id)
                ->where('product_id', $product_id)
                ->get('vendor_products')
                ->row();

            if ($existing) {
                return $this->output
                    ->set_status_header(409)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 409,
                        'message' => 'You have already added this product',
                        'data' => null
                    ]));
            }

            // Insert vendor product (image from admin product)
            $insert_data = [
                'vendor_id' => $vendor_id,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'brand' => $brand,
                'category_id' => $category_id,
                'unit' => $unit,
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'stock' => $stock ?: 0,
                'description' => $description,
                'image' => NULL, // Will use admin product image
                'is_active' => 1,
                'added_on' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('vendor_products', $insert_data);
            $vendor_product_id = $this->db->insert_id();

            // Get inserted product with admin image
            $vendor_product = $this->db
                ->select('vp.*, p.image as admin_image, c.name as category_name')
                ->from('vendor_products vp')
                ->join('products p', 'p.id = vp.product_id', 'left')
                ->join('categories c', 'c.id = vp.category_id', 'left')
                ->where('vp.id', $vendor_product_id)
                ->get()
                ->row();

            $product_image = $vendor_product->admin_image ? base_url($vendor_product->admin_image) : null;

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Product added successfully',
                    'data' => [
                        'id' => (int)$vendor_product->id,
                        'product_id' => (int)$vendor_product->product_id,
                        'product_name' => $vendor_product->product_name,
                        'brand' => $vendor_product->brand,
                        'category_id' => (int)$vendor_product->category_id,
                        'category_name' => $vendor_product->category_name,
                        'unit' => $vendor_product->unit,
                        'mrp' => (float)$vendor_product->mrp,
                        'selling_price' => (float)$vendor_product->selling_price,
                        'stock' => (int)$vendor_product->stock,
                        'description' => $vendor_product->description,
                        'image' => $product_image
                    ]
                ]));
        }

        // CASE 2: Vendor adding NEW product (not in admin list)
        else {

            // Image is required for new product
            if (empty($_FILES['image']['name'])) {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Product image is required',
                        'data' => null
                    ]));
            }

            // Handle image upload
            $upload_path = './assets/uploads/vendor_products/';

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('image')) {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => $this->upload->display_errors('', ''),
                        'data' => null
                    ]));
            }

            $upload_data = $this->upload->data();
            $image = 'assets/uploads/vendor_products/' . $upload_data['file_name'];

            // Insert new product
            $insert_data = [
                'vendor_id' => $vendor_id,
                'product_id' => NULL,
                'product_name' => $product_name,
                'brand' => $brand,
                'category_id' => $category_id,
                'unit' => $unit,
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'stock' => $stock ?: 0,
                'description' => $description,
                'image' => $image,
                'is_active' => 1,
                'added_on' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('vendor_products', $insert_data);
            $vendor_product_id = $this->db->insert_id();

            // Get inserted product
            $vendor_product = $this->db
                ->select('vp.*, c.name as category_name')
                ->from('vendor_products vp')
                ->join('categories c', 'c.id = vp.category_id', 'left')
                ->where('vp.id', $vendor_product_id)
                ->get()
                ->row();

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'New product added successfully',
                    'data' => [
                        'id' => (int)$vendor_product->id,
                        'product_id' => null,
                        'product_name' => $vendor_product->product_name,
                        'brand' => $vendor_product->brand,
                        'category_id' => (int)$vendor_product->category_id,
                        'category_name' => $vendor_product->category_name,
                        'unit' => $vendor_product->unit,
                        'mrp' => (float)$vendor_product->mrp,
                        'selling_price' => (float)$vendor_product->selling_price,
                        'stock' => (int)$vendor_product->stock,
                        'description' => $vendor_product->description,
                        'image' => base_url($vendor_product->image)
                    ]
                ]));
        }
    }


    public function delete_vendor_product($id = null)
    {
        $this->ensureMethod('DELETE');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $vendor_id = $decoded->data->id;

        // Validate ID
        if (!$id) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        // Check if product exists and belongs to vendor
        $vendor_product = $this->db
            ->where('id', $id)
            ->where('vendor_id', $vendor_id)
            ->get('vendor_products')
            ->row();

        if (!$vendor_product) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not found or access denied',
                    'data' => null
                ]));
        }

        // Delete image only if it's vendor's own product
        if ($vendor_product->product_id === null && $vendor_product->image) {
            if (file_exists($vendor_product->image)) {
                unlink($vendor_product->image);
            }
        }

        // Delete product
        $this->db->where('id', $id)->delete('vendor_products');

        // Success response
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Product deleted successfully',
                'data' => null
            ]));
    }
    public function edit_vendor_product($id = null)
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Get token from header
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Verify JWT
        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $vendor_id = $decoded->data->id;

        // Validate ID
        if (!$id) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        // Check if product exists and belongs to vendor
        $vendor_product = $this->db
            ->where('id', $id)
            ->where('vendor_id', $vendor_id)
            ->get('vendor_products')
            ->row();

        if (!$vendor_product) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not found or access denied',
                    'data' => null
                ]));
        }

        // Prepare update data (only fields that are sent)
        $update_data = [];

        if ($this->input->post('product_name')) {
            $update_data['product_name'] = trim($this->input->post('product_name'));
        }

        if ($this->input->post('brand')) {
            $update_data['brand'] = trim($this->input->post('brand'));
        }

        if ($this->input->post('category_id')) {
            $update_data['category_id'] = $this->input->post('category_id');
        }

        if ($this->input->post('unit')) {
            $update_data['unit'] = trim($this->input->post('unit'));
        }

        if ($this->input->post('mrp')) {
            $update_data['mrp'] = $this->input->post('mrp');
        }

        if ($this->input->post('selling_price')) {
            $update_data['selling_price'] = $this->input->post('selling_price');
        }

        if ($this->input->post('stock') !== null && $this->input->post('stock') !== '') {
            $update_data['stock'] = $this->input->post('stock');
        }

        if ($this->input->post('description')) {
            $update_data['description'] = trim($this->input->post('description'));
        }

        // Handle image upload
        // LOGIC: Only allow image change if product_id is NULL (vendor's own product)
        if (!empty($_FILES['image']['name'])) {

            // Check if this is admin's product
            if ($vendor_product->product_id !== null) {
                return $this->output
                    ->set_status_header(403)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 403,
                        'message' => 'Cannot change image for admin products',
                        'data' => null
                    ]));
            }

            // This is vendor's own product, allow image change
            $upload_path = './assets/uploads/vendor_products/';

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('image')) {
                // Delete old image
                if ($vendor_product->image && file_exists($vendor_product->image)) {
                    unlink($vendor_product->image);
                }

                $upload_data = $this->upload->data();
                $update_data['image'] = 'assets/uploads/vendor_products/' . $upload_data['file_name'];
            } else {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => $this->upload->display_errors('', ''),
                        'data' => null
                    ]));
            }
        }

        // Check if any data to update
        if (empty($update_data)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'No data to update',
                    'data' => null
                ]));
        }

        // Update product
        $this->db->where('id', $id)->update('vendor_products', $update_data);

        // Get updated product
        $updated_product = $this->db
            ->select('vp.*, p.image as admin_image, c.name as category_name')
            ->from('vendor_products vp')
            ->join('products p', 'p.id = vp.product_id', 'left')
            ->join('categories c', 'c.id = vp.category_id', 'left')
            ->where('vp.id', $id)
            ->get()
            ->row();

        // Determine image to show
        $product_image = null;
        if ($updated_product->product_id !== null) {
            // Admin product - use admin image
            $product_image = $updated_product->admin_image ? base_url($updated_product->admin_image) : null;
        } else {
            // Vendor's own product - use vendor image
            $product_image = $updated_product->image ? base_url($updated_product->image) : null;
        }

        // Success response
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Product updated successfully',
                'data' => [
                    'id' => (int)$updated_product->id,
                    'product_id' => $updated_product->product_id ? (int)$updated_product->product_id : null,
                    'product_name' => $updated_product->product_name,
                    'brand' => $updated_product->brand,
                    'category_id' => (int)$updated_product->category_id,
                    'category_name' => $updated_product->category_name,
                    'unit' => $updated_product->unit,
                    'mrp' => (float)$updated_product->mrp,
                    'selling_price' => (float)$updated_product->selling_price,
                    'stock' => (int)$updated_product->stock,
                    'description' => $updated_product->description,
                    'image' => $product_image,
                    'is_own_product' => $updated_product->product_id === null
                ]
            ]));
    }
    public function get_inventory()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // 1. Auth Check
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode(['status' => false, 'code' => 401, 'message' => 'Unauthorized', 'data' => null]));
        }

        $vendor_id = $decoded->data->id;
        $search = trim($this->input->get('search') ?? '');
        $low_stock_limit = 5; // Define threshold for "Low Stock" (e.g., <= 5)

        // 2. Fetch Products
        $this->db->select('vp.*, c.name as category_name');
        $this->db->from('vendor_products vp');
        $this->db->join('categories c', 'c.id = vp.category_id', 'left');
        $this->db->where('vp.vendor_id', $vendor_id);
        $this->db->where('vp.is_active', 1);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('vp.product_name', $search);
            $this->db->or_like('vp.brand', $search);
            $this->db->group_end();
        }

        $products = $this->db->order_by('vp.product_name', 'ASC')->get()->result();

        // 3. Process Data & Stats
        $stats = ['total' => 0, 'in_stock' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
        $alerts = ['low_stock_products' => [], 'out_of_stock_products' => []];
        $data = [];

        foreach ($products as $p) {
            $stats['total']++;

            // Determine Status
            $status = 'in_stock';
            if ($p->stock == 0) {
                $status = 'out_of_stock';
                $stats['out_of_stock']++;
                $alerts['out_of_stock_products'][] = $p->product_name . ' (' . $p->stock . ' ' . $p->unit . ')';
            } elseif ($p->stock <= $low_stock_limit) {
                $status = 'low_stock';
                $stats['low_stock']++;
                $alerts['low_stock_products'][] = $p->product_name . ' (' . $p->stock . ' ' . $p->unit . ')';
            } else {
                $stats['in_stock']++;
            }

            // Determine Image (Admin product image vs Vendor uploaded image)
            $image_url = null;
            if ($p->product_id !== null) {
                // It's an admin product, fetch admin image
                $admin_img = $this->db->select('image')->where('id', $p->product_id)->get('products')->row();
                if ($admin_img && $admin_img->image) $image_url = base_url($admin_img->image);
            } else {
                // It's vendor's own product
                if ($p->image) $image_url = base_url($p->image);
            }

            $data[] = [
                'id' => (int)$p->id,
                'product_name' => $p->product_name,
                'brand' => $p->brand,
                'category_name' => $p->category_name,
                'stock' => (int)$p->stock,
                'unit' => $p->unit,
                'mrp' => (float)$p->mrp,
                'selling_price' => (float)$p->selling_price,
                'image' => $image_url,
                'status' => $status // 'in_stock', 'low_stock', 'out_of_stock'
            ];
        }

        // 4. Response
        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Inventory fetched successfully',
            'data' => [
                'stats' => $stats,
                'alerts' => $alerts,
                'products' => $data
            ]
        ]));
    }

    public function update_stock()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Auth
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        $vendor_id = (int)$decoded->data->id;

        // Accept JSON body
        $payload = json_decode($this->input->raw_input_stream, true) ?: [];

        $product_id = $payload['product_id'] ?? null; // vendor_products.id
        $new_stock  = $payload['stock'] ?? null;

        // Validation
        if (!is_numeric($product_id) || !is_numeric($new_stock) || (int)$new_stock < 0) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'Product ID and valid stock quantity are required',
                'data' => null
            ]));
        }

        $product_id = (int)$product_id;
        $new_stock  = (int)$new_stock;

        // Ownership check
        $product = $this->db
            ->where('id', $product_id)
            ->where('vendor_id', $vendor_id)
            ->get('vendor_products')
            ->row();

        if (!$product) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Product not found',
                'data' => null
            ]));
        }

        // Update
        $this->db->where('id', $product_id)->update('vendor_products', ['stock' => $new_stock]);

        // Status label (same as inventory logic)
        $status = 'in_stock';
        if ($new_stock == 0) $status = 'out_of_stock';
        elseif ($new_stock <= 10) $status = 'low_stock';

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Stock updated successfully',
            'data' => [
                'id' => $product_id,
                'product_name' => $product->product_name,
                'stock' => $new_stock,
                'unit' => $product->unit,
                'status' => $status
            ]
        ]));
    }
    public function get_offers()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        $vendor_id = $decoded->data->id;

        $offers = $this->db->where('vendor_id', $vendor_id)->order_by('id', 'DESC')->get('vendor_offers')->result();

        $total = count($offers);
        $active = 0;
        $inactive = 0;
        $data = [];

        foreach ($offers as $offer) {
            if ($offer->is_active == 1) $active++;
            else $inactive++;

            $data[] = [
                'id' => (int)$offer->id,
                'name' => $offer->name,
                'offer_type' => $offer->offer_type,
                'offer_type_label' => $offer->offer_type == 'flat' ? 'Flat Discount' : 'Percentage Discount',
                'discount_value' => (float)$offer->discount_value,
                'discount_label' => $offer->offer_type == 'flat' ? '₹' . $offer->discount_value : $offer->discount_value . '%',
                'min_amount' => (float)$offer->min_amount,
                'start_date' => $offer->start_date,
                'end_date' => $offer->end_date,
                'is_active' => (int)$offer->is_active,
                'status_label' => $offer->is_active ? 'Active' : 'Inactive'
            ];
        }

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Offers fetched successfully',
            'data' => [
                'stats' => ['total' => $total, 'active' => $active, 'inactive' => $inactive],
                'offers' => $data
            ]
        ]));
    }
    public function add_offer()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        $vendor_id = $decoded->data->id;
        $json = json_decode(file_get_contents('php://input'), true);

        $name            = trim($json['name'] ?? '');
        $offer_type      = trim($json['offer_type'] ?? '');
        $discount_value  = $json['discount_value'] ?? '';
        $min_amount      = $json['min_amount'] ?? '';
        $start_date      = $json['start_date'] ?? '';
        $end_date        = $json['end_date'] ?? '';
        $is_active       = !empty($json['is_active']) ? 1 : 0;
        // Validation
        if (empty($name) || empty($offer_type) || empty($discount_value) || empty($min_amount) || empty($start_date) || empty($end_date)) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'All fields are required',
                'data' => null
            ]));
        }

        if (!in_array($offer_type, ['flat', 'percentage'])) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'Invalid offer type',
                'data' => null
            ]));
        }

        if ($discount_value <= 0 || $min_amount <= 0) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'Discount and minimum amount must be greater than 0',
                'data' => null
            ]));
        }

        if (strtotime($start_date) >= strtotime($end_date)) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'End date must be after start date',
                'data' => null
            ]));
        }

        $data = [
            'vendor_id' => $vendor_id,
            'name' => $name,
            'offer_type' => $offer_type,
            'discount_value' => (float)$discount_value,
            'min_amount' => (float)$min_amount,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_active' => $is_active,
            'created_on' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('vendor_offers', $data);
        $offer_id = $this->db->insert_id();

        $offer = $this->db->where('id', $offer_id)->get('vendor_offers')->row();

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Offer created successfully',
            'data' => [
                'id' => (int)$offer->id,
                'name' => $offer->name,
                'offer_type' => $offer->offer_type,
                'offer_type_label' => $offer->offer_type == 'flat' ? 'Flat Discount' : 'Percentage Discount',
                'discount_value' => (float)$offer->discount_value,
                'discount_label' => $offer->offer_type == 'flat' ? '₹' . $offer->discount_value : $offer->discount_value . '%',
                'min_amount' => (float)$offer->min_amount,
                'start_date' => $offer->start_date,
                'end_date' => $offer->end_date,
                'is_active' => (int)$offer->is_active,
                'status_label' => $offer->is_active ? 'Active' : 'Inactive'
            ]
        ]));
    }
    public function edit_offer($id = null)
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Authorization
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        $vendor_id = $decoded->data->id;

        if (empty($id)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Offer ID is required',
                    'data' => null
                ]));
        }

        $offer = $this->db
            ->where('id', $id)
            ->where('vendor_id', $vendor_id)
            ->get('vendor_offers')
            ->row();

        if (!$offer) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Offer not found',
                    'data' => null
                ]));
        }

        // Read JSON Body
        $json = json_decode(file_get_contents('php://input'), true);

        if (!is_array($json)) {
            $json = [];
        }

        $update_data = [];

        // Name
        if (array_key_exists('name', $json)) {

            $name = trim($json['name']);

            if ($name == '') {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Name cannot be empty',
                        'data' => null
                    ]));
            }

            $update_data['name'] = $name;
        }

        // Offer Type
        if (array_key_exists('offer_type', $json)) {

            $offer_type = trim($json['offer_type']);

            if (!in_array($offer_type, ['flat', 'percentage'])) {

                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Invalid offer type',
                        'data' => null
                    ]));
            }

            $update_data['offer_type'] = $offer_type;
        }

        // Discount
        if (array_key_exists('discount_value', $json)) {

            $discount = (float)$json['discount_value'];

            if ($discount <= 0) {

                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Discount must be greater than 0',
                        'data' => null
                    ]));
            }

            $update_data['discount_value'] = $discount;
        }

        // Minimum Amount
        if (array_key_exists('min_amount', $json)) {

            $min = (float)$json['min_amount'];

            if ($min <= 0) {

                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Minimum amount must be greater than 0',
                        'data' => null
                    ]));
            }

            $update_data['min_amount'] = $min;
        }

        // Start Date
        if (array_key_exists('start_date', $json)) {
            $update_data['start_date'] = $json['start_date'];
        }

        // End Date
        if (array_key_exists('end_date', $json)) {
            $update_data['end_date'] = $json['end_date'];
        }

        // Validate Dates
        $start_date = isset($update_data['start_date'])
            ? $update_data['start_date']
            : $offer->start_date;

        $end_date = isset($update_data['end_date'])
            ? $update_data['end_date']
            : $offer->end_date;

        if (strtotime($start_date) >= strtotime($end_date)) {

            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'End date must be after start date',
                    'data' => null
                ]));
        }

        // Status
        if (array_key_exists('is_active', $json)) {

            $update_data['is_active'] = $json['is_active'] ? 1 : 0;
        }

        if (empty($update_data)) {

            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'No data to update',
                    'data' => null
                ]));
        }

        $this->db->where('id', $id);
        $this->db->update('vendor_offers', $update_data);

        $updated_offer = $this->db
            ->where('id', $id)
            ->get('vendor_offers')
            ->row();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Offer updated successfully',
                'data' => [
                    'id' => (int)$updated_offer->id,
                    'name' => $updated_offer->name,
                    'offer_type' => $updated_offer->offer_type,
                    'offer_type_label' => $updated_offer->offer_type == 'flat'
                        ? 'Flat Discount'
                        : 'Percentage Discount',
                    'discount_value' => (float)$updated_offer->discount_value,
                    'discount_label' => $updated_offer->offer_type == 'flat'
                        ? '₹' . $updated_offer->discount_value
                        : $updated_offer->discount_value . '%',
                    'min_amount' => (float)$updated_offer->min_amount,
                    'start_date' => $updated_offer->start_date,
                    'end_date' => $updated_offer->end_date,
                    'is_active' => (int)$updated_offer->is_active,
                    'status_label' => $updated_offer->is_active ? 'Active' : 'Inactive'
                ]
            ]));
    }
    public function delete_offer($id = null)
    {
        $this->ensureMethod('DELETE');
        $this->output->set_content_type('application/json');

        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        $vendor_id = $decoded->data->id;

        if (!$id) {
            return $this->output->set_status_header(422)->set_output(json_encode([
                'status' => false,
                'code' => 422,
                'message' => 'Offer ID is required',
                'data' => null
            ]));
        }

        $offer = $this->db->where('id', $id)->where('vendor_id', $vendor_id)->get('vendor_offers')->row();

        if (!$offer) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Offer not found',
                'data' => null
            ]));
        }

        $this->db->where('id', $id)->delete('vendor_offers');

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Offer deleted successfully',
            'data' => null
        ]));
    }

    public function send_otp_via_sms($mobileNo, $otp)
    {

        $message = "Hi $mobileNo\n\nYour Verification OTP is $otp Do not share this OTP with anyone for security reasons.\n\nRegards\nOMKARENT";



        $params = [

            'user' => 'Fitcketsp',

            'key' => '81a6b2f99cXX',

            'mobile' => '91' . $mobileNo,

            'message' => $message,

            'senderid' => 'OENTER',

            'accusage' => '1',

            'entityid' => '1401487200000053882',

            'tempid' => '1407168611506367587'

        ];



        $url = 'http://mobicomm.dove-sms.com/submitsms.jsp?' . http_build_query($params);



        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);



        if (curl_errno($ch)) {

            log_message('error', 'OTP SMS cURL Error: ' . curl_error($ch));

            curl_close($ch);

            return false;
        }



        curl_close($ch);

        log_message('info', "OTP sent to $mobileNo. Response: $response");

        // echo "<pre>";

        // print_r($response);

        // exit;

        // redirect('provider/dashboard');



        return $response;
    }

    private function ensureMethod($method)
    {
        // Skip OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== $method) {

            $response = [
                'status' => false,
                'code' => 405,
                'message' => 'Method Not Allowed. Use ' . $method,
                'data' => null
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode($response));

            exit($this->output->_display());
        }
    }
    public function logout()
    {
        $this->ensureMethod('POST');

        $this->output->set_content_type('application/json');

        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || empty($decoded->data->id)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Invalid token',
                    'data' => null
                ]));
        }

        $user_id = $decoded->data->id;




        // Blacklist JWT
        $expiry = date('Y-m-d H:i:s', $decoded->exp);

        $this->db->insert('token_blacklist', [
            'token' => $token,
            'expires_at' => $expiry
        ]);



        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Logout successful',
                'data' => null
            ]));
    }

    public function get_vendor_orders()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Auth
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        $vendor_id = (int)$decoded->data->id;
        $status_filter = trim($this->input->get('status', true) ?? '');

        // Query orders containing this vendor's products
        $this->db->select('orders.*, users.name as customer_name, users.mobile as customer_mobile');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id', 'left');
        $this->db->join('order_items', 'order_items.order_id = orders.id', 'inner');
        $this->db->where('order_items.vendor_id', $vendor_id);

        if ($status_filter !== '') {
            $db_status = $status_filter;
            // Map accepted -> confirmed
            if ($status_filter === 'accepted') $db_status = 'confirmed';
            // Map rejected -> cancelled
            if ($status_filter === 'rejected') $db_status = 'cancelled';
            $this->db->where('orders.status', $db_status);
        }

        $this->db->group_by('orders.id');
        $this->db->order_by('orders.id', 'DESC');
        $orders = $this->db->get()->result();

        $order_list = [];
        foreach ($orders as $order) {
            // Get specific items for this vendor
            $items = $this->db
                ->where('order_id', $order->id)
                ->where('vendor_id', $vendor_id)
                ->get('order_items')
                ->result();

            $vendor_total = 0.00;
            $vendor_items = [];
            foreach ($items as $item) {
                $item_image_url = !empty($item->product_image) ? base_url($item->product_image) : '';
                $item_total = ($item->price * $item->quantity) + ($item->gst_amount ?? 0);
                $vendor_total += $item_total;
                $vendor_items[] = [
                    'product_name' => $item->product_name,
                    'quantity' => (int)$item->quantity,
                    'price' => (float)$item->price,
                    'image' => $item_image_url
                ];
            }

            // Get delivery address
            $address = $this->db->get_where('user_addresses', ['id' => $order->address_id])->row();
            $address_str = $address ? "{$address->address_line1}, {$address->city} - {$address->pincode}" : '';

            $vendor_display_status = $order->status;
            if ($order->status === 'confirmed') $vendor_display_status = 'accepted';

            $order_list[] = [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'status' => $vendor_display_status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'total_amount' => round($vendor_total, 2),
                'created_at' => $order->created_at,
                'customer_name' => $order->customer_name ?? 'Unknown',
                'customer_mobile' => $order->customer_mobile ?? '',
                'address' => $address_str,
                'items' => $vendor_items
            ];
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Vendor orders fetched successfully.',
                'data' => $order_list
            ]));
    }

    public function update_vendor_order_status()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Auth
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);
        if (!$decoded || empty($decoded->data->id)) {
            return $this->output->set_status_header(401)->set_output(json_encode([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ]));
        }

        $vendor_id = (int)$decoded->data->id;

        $input_data = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $order_id = intval($input_data['order_id'] ?? 0);
        $status = trim($input_data['status'] ?? ''); // 'accepted', 'rejected', 'packed', 'out_for_delivery', 'delivered', 'cancelled'
        $remarks = trim($input_data['remarks'] ?? '');

        if ($order_id <= 0 || empty($status)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Order ID and Status are required',
                    'data' => null
                ]));
        }

        // Check if order contains items belonging to this vendor
        $has_items = $this->db->get_where('order_items', ['order_id' => $order_id, 'vendor_id' => $vendor_id])->row();
        if (!$has_items) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Order not found for this vendor',
                    'data' => null
                ]));
        }

        // Get the order
        $order = $this->db->get_where('orders', ['id' => $order_id])->row();
        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Order not found',
                    'data' => null
                ]));
        }

        // Map status
        $db_status = $status;
        if ($status === 'accepted') $db_status = 'confirmed';
        if ($status === 'rejected') $db_status = 'cancelled';

        $valid_db_statuses = ['pending', 'confirmed', 'packed', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($db_status, $valid_db_statuses)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid status value',
                    'data' => null
                ]));
        }

        // If it's already in the target status, do nothing
        if ($order->status === $db_status) {
            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Order is already in the requested status',
                    'data' => null
                ]));
        }

        $this->db->trans_begin();

        $update_fields = [
            'status' => $db_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Cash on delivery payment update on delivery
        if ($db_status === 'delivered') {
            if (strtolower($order->payment_method) === 'cod' && $order->payment_status === 'pending') {
                $update_fields['payment_status'] = 'paid';
            }
        }

        // Update orders table
        $this->db->where('id', $order_id)->update('orders', $update_fields);

        // Auto-restore stock if cancelled (and wasn't already cancelled)
        if ($db_status === 'cancelled' && $order->status !== 'cancelled') {
            $this->restore_stock_for_order($order_id, $vendor_id);
        }

        // Log status history
        $this->insert_status_history($order_id, $db_status, $remarks ?: 'Updated by vendor', 'vendor');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 500,
                    'message' => 'Failed to update order status',
                    'data' => null
                ]));
        }

        $this->db->trans_commit();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Order status updated successfully',
                'data' => null
            ]));
    }

    private function verify_jwt($token)
    {
        if (empty($token)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Authorization header missing or invalid',
                    'data' => null
                ]))
                ->_display();
            exit;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));

            $query = $this->db->get_where('token_blacklist', ['token' => $token]);
            if ($query->num_rows() > 0) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 400,
                        'message' => 'Token has been invalidated. Please log in again.',
                        'data' => null
                    ]))
                    ->_display();
                exit;
            }

            return $decoded;
        } catch (Exception $e) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Invalid token: ' . $e->getMessage(),
                    'data' => null
                ]))
                ->_display();
            exit;
        }
    }


    private function generate_jwt($user)
    {
        $payload = [
            'iss' => base_url(),
            'iat' => time(),
            'exp' => time() + (10 * 365 * 24 * 60 * 60), // ✅ Valid for 10 years
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? '',
                'mobile' => $user->mobile,
                'role' => $user->role,
                'is_admin' => false

            ]
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    private function insert_status_history(int $order_id, string $status, string $remarks, string $changed_by): void
    {
        $this->db->insert('order_status_history', [
            'order_id'   => $order_id,
            'status'     => $status,
            'remarks'    => $remarks,
            'changed_by' => $changed_by,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_vendor_stats()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Skip auth check if admin session is active
        $admin_logged_in = $this->session->userdata('admin_logged_in');
        if ($admin_logged_in !== TRUE && $admin_logged_in !== 1 && $admin_logged_in !== '1') {
            // Require JWT for API requests if not logged in as admin session
            $authHeader = $this->input->get_request_header('Authorization', TRUE);
            $token = null;
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
            $decoded = $this->verify_jwt($token);
            if (!$decoded || empty($decoded->data->id)) {
                return $this->output
                    ->set_status_header(401)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 401,
                        'message' => 'Unauthorized',
                        'data' => null
                    ]));
            }
        }

        // Fetch stats
        $vendors = $this->db
            ->select('id, name, store_name')
            ->where('role', 'vendor')
            ->get('users')
            ->result_array();

        $stats = [];
        foreach ($vendors as $v) {
            // Calculate revenue
            $revenue = $this->db
                ->select('SUM(oi.subtotal + oi.gst_amount) as total')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id')
                ->where('oi.vendor_id', $v['id'])
                ->where('o.status !=', 'cancelled')
                ->get()
                ->row()
                ->total ?: 0.00;

            // Calculate sold products
            $products_sold = $this->db
                ->select_sum('quantity')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id')
                ->where('oi.vendor_id', $v['id'])
                ->where('o.status !=', 'cancelled')
                ->get()
                ->row()
                ->quantity ?: 0;

            // Calculate orders count
            $orders_count = $this->db
                ->select('COUNT(DISTINCT oi.order_id) as count')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id')
                ->where('oi.vendor_id', $v['id'])
                ->where('o.status !=', 'cancelled')
                ->get()
                ->row()
                ->count ?: 0;

            $stats[] = [
                'vendor_id' => (int)$v['id'],
                'name' => $v['name'] ?: $v['store_name'] ?: 'Vendor #' . $v['id'],
                'store_name' => $v['store_name'] ?: $v['name'],
                'revenue' => (float)$revenue,
                'products_sold' => (int)$products_sold,
                'orders_count' => (int)$orders_count
            ];
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Vendor statistics retrieved successfully',
                'data' => $stats
            ]));
    }

    private function restore_stock_for_order(int $order_id, int $vendor_id): void
    {
        $items = $this->db
            ->where('order_id', $order_id)
            ->where('vendor_id', $vendor_id)
            ->get('order_items')
            ->result();
        foreach ($items as $item) {
            $this->db->set('stock', 'stock + ' . (int) $item->quantity, false)
                ->where('id', $item->product_id)
                ->update('vendor_products');
        }
    }
}
