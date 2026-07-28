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

        // Load models and libraries
        $this->load->model('General_model');
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form']);

        // Check and prepare DB tables if missing
        $this->check_cart_table();
        $this->check_wishlist_table();

        // CORS Headers
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
        header("Content-Type: application/json; charset=UTF-8");

        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    // ==========================================
    // 1. REGISTRATION FLOW
    // ==========================================

    /**
     * Send OTP for user registration
     */
    public function register_send_otp()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $name   = trim($input_data['name'] ?? '');
        $mobile = trim($input_data['mobile'] ?? '');
        $email  = trim($input_data['email'] ?? '');

        // Validation
        if (empty($name) || empty($mobile) || empty($email)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Name, Mobile, and Email are required',
                    'data' => null
                ]));
        }

        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid mobile number. Must be exactly 10 digits.',
                    'data' => null
                ]));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid email address',
                    'data' => null
                ]));
        }

        // Check if user already exists
        $exists_mobile = $this->db
            ->where('mobile', $mobile)
            ->get('users')
            ->row();

        if ($exists_mobile) {
            return $this->output
                ->set_status_header(409)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 409,
                    'message' => 'Mobile number already registered',
                    'data' => null
                ]));
        }

        $exists_email = $this->db
            ->where('email', $email)
            ->get('users')
            ->row();

        if ($exists_email) {
            return $this->output
                ->set_status_header(409)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 409,
                    'message' => 'Email address already registered',
                    'data' => null
                ]));
        }

        // Setup static OTP for development
        $otp = '123456';

        // Delete old registration OTPs for this mobile
        $this->db
            ->where('mobile', $mobile)
            ->delete('user_registration_otps');

        // Insert new OTP record
        $this->db->insert('user_registration_otps', [
            'mobile' => $mobile,
            'name' => $name,
            'email' => $email,
            'otp' => $otp,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Registration OTP sent successfully',
                'dev_otp' => $otp
            ]));
    }

    /**
     * Verify registration OTP and create user
     */
    public function verify_register_otp()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $mobile = trim($input_data['mobile'] ?? '');
        $otp    = trim($input_data['otp'] ?? '');

        // Validation
        if (empty($mobile) || empty($otp)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Mobile and OTP are required',
                    'data' => null
                ]));
        }

        // Fetch OTP row
        $otp_row = $this->db
            ->where('mobile', $mobile)
            ->where('otp', $otp)
            ->order_by('id', 'DESC')
            ->get('user_registration_otps')
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

        // Check if expired
        if (strtotime($otp_row->expires_at) < time()) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'OTP expired',
                    'data' => null
                ]));
        }

        // Check again if user was registered in the meantime
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
                    'message' => 'Mobile number already registered',
                    'data' => null
                ]));
        }

        // Insert new user
        $user_data = [
            'name' => $otp_row->name,
            'mobile' => $otp_row->mobile,
            'email' => $otp_row->email,
            'role' => 'user',
            'is_active' => 1,
            'created_on' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('users', $user_data);
        $user_id = $this->db->insert_id();

        // Get fresh user record
        $user = $this->db
            ->where('id', $user_id)
            ->get('users')
            ->row();

        // Clean up OTP row
        $this->db
            ->where('mobile', $mobile)
            ->delete('user_registration_otps');

        // Generate JWT token
        $token = $this->generate_jwt($user);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Registration successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email ?? '',
                        'mobile' => $user->mobile,
                        'role' => $user->role ?? 'user',
                        'is_active' => $user->is_active ?? 1,
                        'created_on' => $user->created_on ?? null
                    ]
                ]
            ]));
    }

    // ==========================================
    // 2. LOGIN & AUTHENTICATION FLOW
    // ==========================================

    /**
     * Send OTP for user login
     */
    public function login_send_otp()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $mobile = trim($input_data['mobile'] ?? '');

        // Validation
        if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Valid 10-digit mobile number is required',
                    'data' => null
                ]));
        }

        // Check if user exists
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

        // Check if account is active
        if (isset($user->is_active) && $user->is_active == 0) {
            return $this->output
                ->set_status_header(403)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 403,
                    'message' => 'Your account is not active',
                    'data' => null
                ]));
        }

        // Setup static OTP for development
        $otp = '123456';

        // Delete old OTPs for this mobile
        $this->db
            ->where('mobile', $mobile)
            ->delete('user_login_otps');

        // Insert new OTP
        $this->db->insert('user_login_otps', [
            'mobile' => $mobile,
            'otp' => $otp,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'OTP sent successfully',
                'masked_mobile' => '******' . substr($mobile, -4),
                'dev_otp' => $otp
            ]));
    }

    /**
     * Verify OTP and return JWT token
     */
    public function verify_login_otp()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $mobile = trim($input_data['mobile'] ?? '');
        $otp    = trim($input_data['otp'] ?? '');

        // Validation
        if (empty($mobile) || empty($otp)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Mobile and OTP are required',
                    'data' => null
                ]));
        }

        // Check if user exists
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

        // Check OTP row
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

        // Check if OTP expired
        if (strtotime($otp_row->expires_at) < time()) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'OTP expired',
                    'data' => null
                ]));
        }

        // Delete used OTP
        $this->db
            ->where('mobile', $mobile)
            ->delete('user_login_otps');

        // Generate JWT Token
        $token = $this->generate_jwt($user);

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
                        'email' => $user->email ?? '',
                        'mobile' => $user->mobile,
                        'role' => $user->role ?? 'user',
                        'is_active' => $user->is_active ?? 1,
                        'created_on' => $user->created_on ?? null
                    ]
                ]
            ]));
    }

    /**
     * Google Login (Future Implementation Placeholder)
     * Receives a Google token, simulates validation, and generates a JWT.
     */
    public function google_login()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $google_token = trim($input_data['google_token'] ?? '');
        $email = trim($input_data['email'] ?? '');
        $name = trim($input_data['name'] ?? '');

        if (empty($google_token)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Google ID Token is required',
                    'data' => null
                ]));
        }

        // In the future: Validate $google_token with Google API Client SDK.
        // For now, we mock/stub the verification using the provided email or a default one.
        if (empty($email)) {
            $email = 'google.user@example.com';
            $name = 'Google User';
        }

        // Look up user by email
        $user = $this->db
            ->where('email', $email)
            ->get('users')
            ->row();

        if (!$user) {
            // Auto-register user if they do not exist
            $mobile = trim($input_data['mobile'] ?? '0000000000');
            $insert_data = [
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'role' => 'user',
                'is_active' => 1,
                'created_on' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('users', $insert_data);
            $user_id = $this->db->insert_id();
            $user = $this->db->where('id', $user_id)->get('users')->row();
        }

        // Generate JWT Token
        $token = $this->generate_jwt($user);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Google Login successful (Mocked)',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email ?? '',
                        'mobile' => $user->mobile,
                        'role' => $user->role ?? 'user',
                        'is_active' => $user->is_active ?? 1,
                        'created_on' => $user->created_on ?? null
                    ]
                ]
            ]));
    }

    /**
     * Guest Login (Optional)
     * Generates a temporary guest token with 'guest' role so clients can browse.
     */
    public function guest_login()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Create a guest user structure
        $guest_user = new stdClass();
        $guest_user->id = -rand(100000, 999999); // Unique negative ID for guest cart partition
        $guest_user->name = 'Guest ' . rand(1000, 9999);
        $guest_user->email = 'guest@example.com';
        $guest_user->mobile = '0000000000';
        $guest_user->role = 'guest';
        $guest_user->is_active = 1;
        $guest_user->created_on = date('Y-m-d H:i:s');

        $token = $this->generate_jwt($guest_user);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Guest session initiated',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $guest_user->id,
                        'name' => $guest_user->name,
                        'role' => $guest_user->role
                    ]
                ]
            ]));
    }

    // ==========================================
    // 3. CART MANAGEMENT FLOW
    // ==========================================

    /**
     * Get a specific product's row inside the user's cart
     */
    public function get_cart_row($product_id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        if ($product_id === null) {
            $product_id = $this->input->get('product_id', true);
        }

        if (empty($product_id)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        $this->db->select('cart_items.id as cart_item_id, cart_items.quantity, products.id as product_id, products.name as product_name, products.price, products.sale_price, products.image, products.sku');
        $this->db->from('cart_items');
        $this->db->join('products', 'products.id = cart_items.product_id', 'inner');
        $this->db->where('cart_items.user_id', $user_id);
        $this->db->where('cart_items.product_id', $product_id);

        $item = $this->db->get()->row();

        if (!$item) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not in cart',
                    'data' => null
                ]));
        }

        $item->price = floatval($item->price);
        $item->sale_price = $item->sale_price !== null ? floatval($item->sale_price) : null;
        $active_price = $item->sale_price !== null ? $item->sale_price : $item->price;
        $item->item_total = $active_price * $item->quantity;
        $item->image_url = !empty($item->image) ? base_url($item->image) : null;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Cart item retrieved successfully',
                'data' => $item
            ]));
    }

    /**
     * Get simple quantities and totals summary of the cart
     */
    public function get_cart_summary()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->select('cart_items.quantity, products.price, products.sale_price');
        $this->db->from('cart_items');
        $this->db->join('products', 'products.id = cart_items.product_id', 'inner');
        $this->db->where('cart_items.user_id', $user_id);
        $this->db->where('products.is_active', 1);

        $items = $this->db->get()->result();

        $total_amount = 0.00;
        $total_items = count($items);
        $total_quantity = 0;

        foreach ($items as $item) {
            $price = floatval($item->price);
            $sale_price = $item->sale_price !== null ? floatval($item->sale_price) : null;
            $active_price = $sale_price !== null ? $sale_price : $price;

            $total_amount += $active_price * $item->quantity;
            $total_quantity += $item->quantity;
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Cart summary retrieved successfully',
                'data' => [
                    'total_items' => $total_items,
                    'total_quantity' => $total_quantity,
                    'total_amount' => $total_amount
                ]
            ]));
    }

    /**
     * Add a product to the user's cart (creates/updates row)
     */
    public function add_to_cart()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? 0);
        $quantity = intval($input_data['quantity'] ?? 1);

        if ($product_id <= 0 || $quantity <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid product ID or quantity',
                    'data' => null
                ]));
        }

        // Verify product is active
        $product = $this->db->get_where('products', ['id' => $product_id, 'is_active' => 1])->row();
        if (!$product) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not found or inactive',
                    'data' => null
                ]));
        }

        // Check if item already exists in cart
        $existing = $this->db->get_where('cart_items', ['user_id' => $user_id, 'product_id' => $product_id])->row();
        if ($existing) {
            $new_quantity = $existing->quantity + $quantity;
            $this->db->where('id', $existing->id)->update('cart_items', [
                'quantity' => $new_quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->insert('cart_items', [
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Item added to cart successfully',
                'data' => null
            ]));
    }

    /**
     * Get the full list of products in the cart with user's details and total amounts
     */
    public function get_cart()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->select('cart_items.id as cart_item_id, cart_items.quantity, cart_items.created_at as added_at, products.id as product_id, products.name as product_name, products.price, products.sale_price, products.sku, products.image, categories.name as category_name');
        $this->db->from('cart_items');
        $this->db->join('products', 'products.id = cart_items.product_id', 'inner');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        $this->db->where('cart_items.user_id', $user_id);
        $this->db->where('products.is_active', 1);
        $this->db->order_by('cart_items.id', 'ASC');

        $items = $this->db->get()->result();

        $total_amount = 0.00;
        $total_items = count($items);
        $total_quantity = 0;

        foreach ($items as $item) {
            $item->price = floatval($item->price);
            $item->sale_price = $item->sale_price !== null ? floatval($item->sale_price) : null;
            $active_price = $item->sale_price !== null ? $item->sale_price : $item->price;

            $item->item_total = $active_price * $item->quantity;
            $total_amount += $item->item_total;
            $total_quantity += $item->quantity;

            $item->image_url = !empty($item->image) ? base_url($item->image) : null;
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'items' => $items,
                    'summary' => [
                        'total_items' => $total_items,
                        'total_quantity' => $total_quantity,
                        'total_amount' => $total_amount
                    ]
                ]
            ]));
    }

    /**
     * Update absolute product quantity in the cart
     */
    public function update_cart_quantity()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? 0);
        $quantity = intval($input_data['quantity'] ?? 0);

        if ($product_id <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        if ($quantity <= 0) {
            // Delete product row from cart
            $this->db->delete('cart_items', ['user_id' => $user_id, 'product_id' => $product_id]);
            $message = 'Item removed from cart';
        } else {
            // Check if product exists in cart
            $existing = $this->db->get_where('cart_items', ['user_id' => $user_id, 'product_id' => $product_id])->row();
            if (!$existing) {
                // Verify product is active first
                $product = $this->db->get_where('products', ['id' => $product_id, 'is_active' => 1])->row();
                if (!$product) {
                    return $this->output
                        ->set_status_header(404)
                        ->set_output(json_encode([
                            'status' => false,
                            'code' => 404,
                            'message' => 'Product not found or inactive',
                            'data' => null
                        ]));
                }

                $this->db->insert('cart_items', [
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->where('id', $existing->id)->update('cart_items', [
                    'quantity' => $quantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $message = 'Cart quantity updated successfully';
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => $message,
                'data' => null
            ]));
    }

    /**
     * Remove a single product from the user's cart
     */
    public function remove_from_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->ensureMethod('POST');
        }

        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? $this->input->get('product_id', true));

        if ($product_id <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        $this->db->delete('cart_items', ['user_id' => $user_id, 'product_id' => $product_id]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Item removed from cart successfully',
                'data' => null
            ]));
    }

    /**
     * Delete all items currently in the user's cart
     */
    public function clear_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->ensureMethod('POST');
        }

        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->delete('cart_items', ['user_id' => $user_id]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Cart cleared successfully',
                'data' => null
            ]));
    }

    // ==========================================
    // 4. WISHLIST MANAGEMENT FLOW
    // ==========================================

    /**
     * Add a product to the user's wishlist
     */
    public function add_to_wishlist()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? 0);
        $quantity = intval($input_data['quantity'] ?? 1);

        if ($product_id <= 0 || $quantity <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Invalid product ID or quantity',
                    'data' => null
                ]));
        }

        // Verify product is active
        $product = $this->db->get_where('products', ['id' => $product_id, 'is_active' => 1])->row();
        if (!$product) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not found or inactive',
                    'data' => null
                ]));
        }

        // Check if item already exists in wishlist
        $existing = $this->db->get_where('wishlist_items', ['user_id' => $user_id, 'product_id' => $product_id])->row();
        if ($existing) {
            $new_quantity = $existing->quantity + $quantity;
            $this->db->where('id', $existing->id)->update('wishlist_items', [
                'quantity' => $new_quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->insert('wishlist_items', [
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Item added to wishlist successfully',
                'data' => null
            ]));
    }

    /**
     * Get the full list of products in the user's wishlist
     */
    public function get_wishlist()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->select('wishlist_items.id as wishlist_item_id, wishlist_items.quantity, wishlist_items.created_at as added_at, products.id as product_id, products.name as product_name, products.price, products.sale_price, products.sku, products.image, categories.name as category_name');
        $this->db->from('wishlist_items');
        $this->db->join('products', 'products.id = wishlist_items.product_id', 'inner');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        $this->db->where('wishlist_items.user_id', $user_id);
        $this->db->where('products.is_active', 1);
        $this->db->order_by('wishlist_items.id', 'ASC');

        $items = $this->db->get()->result();

        $total_amount = 0.00;
        $total_items = count($items);
        $total_quantity = 0;

        foreach ($items as $item) {
            $item->price = floatval($item->price);
            $item->sale_price = $item->sale_price !== null ? floatval($item->sale_price) : null;
            $active_price = $item->sale_price !== null ? $item->sale_price : $item->price;

            $item->item_total = $active_price * $item->quantity;
            $total_amount += $item->item_total;
            $total_quantity += $item->quantity;

            $item->image_url = !empty($item->image) ? base_url($item->image) : null;
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Wishlist retrieved successfully',
                'data' => [
                    'items' => $items,
                    'summary' => [
                        'total_items' => $total_items,
                        'total_quantity' => $total_quantity,
                        'total_amount' => $total_amount
                    ]
                ]
            ]));
    }

    /**
     * Get unique count, quantity and total cost summary of user's wishlist
     */
    public function get_wishlist_summary()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->select('wishlist_items.quantity, products.price, products.sale_price');
        $this->db->from('wishlist_items');
        $this->db->join('products', 'products.id = wishlist_items.product_id', 'inner');
        $this->db->where('wishlist_items.user_id', $user_id);
        $this->db->where('products.is_active', 1);

        $items = $this->db->get()->result();

        $total_amount = 0.00;
        $total_items = count($items);
        $total_quantity = 0;

        foreach ($items as $item) {
            $price = floatval($item->price);
            $sale_price = $item->sale_price !== null ? floatval($item->sale_price) : null;
            $active_price = $sale_price !== null ? $sale_price : $price;

            $total_amount += $active_price * $item->quantity;
            $total_quantity += $item->quantity;
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Wishlist summary retrieved successfully',
                'data' => [
                    'total_items' => $total_items,
                    'total_quantity' => $total_quantity,
                    'total_amount' => $total_amount
                ]
            ]));
    }

    /**
     * Update product quantity inside the user's wishlist
     */
    public function update_wishlist_quantity()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? 0);
        $quantity = intval($input_data['quantity'] ?? 0);

        if ($product_id <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        if ($quantity <= 0) {
            // Delete product row from wishlist
            $this->db->delete('wishlist_items', ['user_id' => $user_id, 'product_id' => $product_id]);
            $message = 'Item removed from wishlist';
        } else {
            // Check if product exists in wishlist
            $existing = $this->db->get_where('wishlist_items', ['user_id' => $user_id, 'product_id' => $product_id])->row();
            if (!$existing) {
                // Verify product is active first
                $product = $this->db->get_where('products', ['id' => $product_id, 'is_active' => 1])->row();
                if (!$product) {
                    return $this->output
                        ->set_status_header(404)
                        ->set_output(json_encode([
                            'status' => false,
                            'code' => 404,
                            'message' => 'Product not found or inactive',
                            'data' => null
                        ]));
                }

                $this->db->insert('wishlist_items', [
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->where('id', $existing->id)->update('wishlist_items', [
                    'quantity' => $quantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $message = 'Wishlist quantity updated successfully';
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => $message,
                'data' => null
            ]));
    }

    /**
     * Remove a single product from the user's wishlist
     */
    public function remove_from_wishlist()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->ensureMethod('POST');
        }

        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? $this->input->get('product_id', true));

        if ($product_id <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        $this->db->delete('wishlist_items', ['user_id' => $user_id, 'product_id' => $product_id]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Item removed from wishlist successfully',
                'data' => null
            ]));
    }

    /**
     * Delete all items currently in the user's wishlist
     */
    public function clear_wishlist()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->ensureMethod('POST');
        }

        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->delete('wishlist_items', ['user_id' => $user_id]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Wishlist cleared successfully',
                'data' => null
            ]));
    }

    /**
     * Move/Copy all wishlist items into the shopping cart and empty the wishlist
     */
    public function add_all_to_cart()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Fetch all items currently in wishlist
        $wishlist_items = $this->db->get_where('wishlist_items', ['user_id' => $user_id])->result();

        if (empty($wishlist_items)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Wishlist is empty',
                    'data' => null
                ]));
        }

        foreach ($wishlist_items as $item) {
            // Verify if product is active first
            $product = $this->db->get_where('products', ['id' => $item->product_id, 'is_active' => 1])->row();
            if (!$product) {
                continue; // Skip inactive/deleted products
            }

            // Check if already in cart
            $existing_cart = $this->db->get_where('cart_items', ['user_id' => $user_id, 'product_id' => $item->product_id])->row();
            if ($existing_cart) {
                $this->db->where('id', $existing_cart->id)->update('cart_items', [
                    'quantity' => $existing_cart->quantity + $item->quantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert('cart_items', [
                    'user_id' => $user_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Clear user's wishlist
        $this->db->delete('wishlist_items', ['user_id' => $user_id]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'All wishlist items moved to cart successfully',
                'data' => null
            ]));
    }

    // ==========================================
    // 5. CORE APPLICATION FEATURES
    // ==========================================

    /**
     * Home API: Fetches active categories and products with resolved full image/gallery URLs
     */
    public function home()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Optional search or category filter in query parameters
        $search = trim($this->input->get('search', true));
        $category_id = trim($this->input->get('category_id', true));

        // 1. Fetch Categories
        $categories_query = $this->db->where('is_active', 1);
        $categories = $categories_query->get('categories')->result();

        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;
        }

        // 2. Fetch Products
        $this->db->select('products.*, categories.name as category_name');
        $this->db->from('products');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        $this->db->where('products.is_active', 1);

        if (!empty($category_id)) {
            $this->db->where('products.category_id', $category_id);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('products.name', $search);
            $this->db->or_like('products.sku', $search);
            $this->db->group_end();
        }

        $this->db->order_by('products.id', 'DESC');
        
        // Default home limit if no active filters
        if (empty($search) && empty($category_id)) {
            $this->db->limit(20);
        }

        $products = $this->db->get()->result();

        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;

            // Handle gallery images
            $prod->gallery_urls = [];
            if (!empty($prod->gallery)) {
                $gallery_images = json_decode($prod->gallery, true);
                if (is_array($gallery_images)) {
                    foreach ($gallery_images as $g_img) {
                        $prod->gallery_urls[] = base_url($g_img);
                    }
                }
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Home data fetched successfully',
                'data' => [
                    'categories' => $categories,
                    'products' => $products
                ]
            ]));
    }

    /**
     * Get list of all active categories (No JWT token needed)
     */
    public function get_category_list()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $categories = $this->db
            ->where('is_active', 1)
            ->order_by('id', 'ASC')
            ->get('categories')
            ->result();

        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Categories fetched successfully',
                'data' => $categories
            ]));
    }

    /**
     * Get details of a single active category (No JWT token needed)
     */
    public function get_category_detail($id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if ($id === null) {
            $id = $this->input->get('id', true);
        }

        if (empty($id)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Category ID is required',
                    'data' => null
                ]));
        }

        $category = $this->db
            ->where('id', $id)
            ->where('is_active', 1)
            ->get('categories')
            ->row();

        if (!$category) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Category not found or inactive',
                    'data' => null
                ]));
        }

        $category->image_url = !empty($category->image) ? base_url($category->image) : null;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Category details fetched successfully',
                'data' => $category
            ]));
    }

    /**
     * Get active products by category ID (No JWT token needed)
     */
    public function get_products_by_category($category_id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if ($category_id === null) {
            $category_id = $this->input->get('category_id', true);
        }

        if (empty($category_id)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Category ID is required',
                    'data' => null
                ]));
        }

        // Fetch active products belonging to the category
        $products = $this->db
            ->select('products.*, categories.name as category_name')
            ->from('products')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.category_id', $category_id)
            ->where('products.is_active', 1)
            ->order_by('products.id', 'DESC')
            ->get()
            ->result();

        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;

            // Handle gallery images
            $prod->gallery_urls = [];
            if (!empty($prod->gallery)) {
                $gallery_images = json_decode($prod->gallery, true);
                if (is_array($gallery_images)) {
                    foreach ($gallery_images as $g_img) {
                        $prod->gallery_urls[] = base_url($g_img);
                    }
                }
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Products fetched successfully',
                'data' => $products
            ]));
    }

    /**
     * Get list of all active products (No JWT token needed)
     */
    public function get_product_list()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $search = trim($this->input->get('search', true));

        $this->db->select('products.*, categories.name as category_name');
        $this->db->from('products');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        $this->db->where('products.is_active', 1);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('products.name', $search);
            $this->db->or_like('products.sku', $search);
            $this->db->group_end();
        }

        $this->db->order_by('products.id', 'DESC');
        $products = $this->db->get()->result();

        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;

            // Handle gallery images
            $prod->gallery_urls = [];
            if (!empty($prod->gallery)) {
                $gallery_images = json_decode($prod->gallery, true);
                if (is_array($gallery_images)) {
                    foreach ($gallery_images as $g_img) {
                        $prod->gallery_urls[] = base_url($g_img);
                    }
                }
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Products fetched successfully',
                'data' => $products
            ]));
    }

    /**
     * Get details of a single active product (No JWT token needed)
     */
    public function get_product_detail($id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if ($id === null) {
            $id = $this->input->get('id', true);
        }

        if (empty($id)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Product ID is required',
                    'data' => null
                ]));
        }

        $product = $this->db
            ->select('products.*, categories.name as category_name')
            ->from('products')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.id', $id)
            ->where('products.is_active', 1)
            ->get()
            ->row();

        if (!$product) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Product not found or inactive',
                    'data' => null
                ]));
        }

        $product->image_url = !empty($product->image) ? base_url($product->image) : null;

        // Handle gallery images
        $product->gallery_urls = [];
        if (!empty($product->gallery)) {
            $gallery_images = json_decode($product->gallery, true);
            if (is_array($gallery_images)) {
                foreach ($gallery_images as $g_img) {
                    $product->gallery_urls[] = base_url($g_img);
                }
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Product details fetched successfully',
                'data' => $product
            ]));
    }

    /**
     * Get user profile details
     */
    public function get_profile()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Extract token
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || !isset($decoded->data->id)) {
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

        // Fetch user from DB
        $user = $this->db
            ->select('id, name, mobile, email, address, profile_image, role, is_active, created_on')
            ->where('id', $user_id)
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

        // Prepend base_url to profile_image if exists
        $user->profile_image_url = !empty($user->profile_image) ? base_url($user->profile_image) : null;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Profile retrieved successfully',
                'data' => $user
            ]));
    }

    /**
     * Update user profile (supports partial updates)
     */
    public function update_profile()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Extract token
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || !isset($decoded->data->id)) {
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

        // Fetch current user details
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

        $update_data = [];

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        // Only add fields if they are sent in request (supporting partial updates)
        if (isset($input_data['name'])) {
            $update_data['name'] = trim($input_data['name']);
        }

        if (isset($input_data['address'])) {
            $update_data['address'] = trim($input_data['address']);
        }

        if (isset($input_data['email'])) {
            $email = trim($input_data['email']);
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Invalid email address',
                        'data' => null
                    ]));
            }

            // Check if email already in use
            $exists_email = $this->db
                ->where('email', $email)
                ->where('id !=', $user_id)
                ->get('users')
                ->row();
            if ($exists_email) {
                return $this->output
                    ->set_status_header(409)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 409,
                        'message' => 'Email address already in use by another account',
                        'data' => null
                    ]));
            }

            $update_data['email'] = $email;
        }

        if (isset($input_data['mobile'])) {
            $mobile = trim($input_data['mobile']);
            if (!preg_match('/^[0-9]{10}$/', $mobile)) {
                return $this->output
                    ->set_status_header(422)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 422,
                        'message' => 'Invalid mobile number. Must be exactly 10 digits.',
                        'data' => null
                    ]));
            }

            // Check if mobile already in use
            $exists_mobile = $this->db
                ->where('mobile', $mobile)
                ->where('id !=', $user_id)
                ->get('users')
                ->row();
            if ($exists_mobile) {
                return $this->output
                    ->set_status_header(409)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 409,
                        'message' => 'Mobile number already in use by another account',
                        'data' => null
                    ]));
            }

            $update_data['mobile'] = $mobile;
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
                if (!empty($user->profile_image) && file_exists(FCPATH . $user->profile_image)) {
                    @unlink(FCPATH . $user->profile_image);
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

        // Check if there is anything to update
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

        // Fetch updated user
        $updated_user = $this->db
            ->select('id, name, mobile, email, address, profile_image, role, is_active, created_on')
            ->where('id', $user_id)
            ->get('users')
            ->row();

        $updated_user->profile_image_url = !empty($updated_user->profile_image) ? base_url($updated_user->profile_image) : null;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Profile updated successfully',
                'data' => $updated_user
            ]));
    }

    /**
     * Test endpoint to verify JWT token validation works
     */
    public function test_token()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || !isset($decoded->data->id)) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Token is valid',
                'data' => $decoded->data
            ]));
    }

    // ==========================================
    // 6. LOGOUT & SESSION TERMINATION
    // ==========================================

    /**
     * Logout and blacklist the current active JWT
     */
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

        if (!$decoded || !isset($decoded->data->id)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Invalid token',
                    'data' => null
                ]));
        }

        // Blacklist JWT
        $expiry = date('Y-m-d H:i:s', $decoded->exp);

        if ($this->db->table_exists('token_blacklist')) {
            $this->db->insert('token_blacklist', [
                'token' => $token,
                'expires_at' => $expiry
            ]);
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Logout successful',
                'data' => null
            ]));
    }

    // ==========================================
    // 7. PRIVATE HELPER METHODS
    // ==========================================

    private function check_cart_table()
    {
        if (!$this->db->table_exists('cart_items')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `cart_items` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `product_id` INT NOT NULL,
                    `quantity` INT NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    KEY `user_id` (`user_id`),
                    KEY `product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }

    private function check_wishlist_table()
    {
        if (!$this->db->table_exists('wishlist_items')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `wishlist_items` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `product_id` INT NOT NULL,
                    `quantity` INT NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    KEY `user_id` (`user_id`),
                    KEY `product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }

    private function authenticate()
    {
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $decoded = $this->verify_jwt($token);

        if (!$decoded || !isset($decoded->data->id)) {
            $response = [
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null
            ];

            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->_display();
            exit;
        }

        return $decoded->data->id;
    }

    private function ensureMethod($method)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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

    private function generate_jwt($user)
    {
        $payload = [
            'iss' => base_url(),
            'iat' => time(),
            'exp' => time() + (10 * 365 * 24 * 60 * 60), // Valid for 10 years
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? '',
                'mobile' => $user->mobile,
                'role' => $user->role ?? 'user',
                'is_admin' => false
            ]
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
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

            // Check blacklist if table exists
            if ($this->db->table_exists('token_blacklist')) {
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
}