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
        $this->ensure_address_table();
        $this->ensure_orders_table();
        $this->ensure_order_items_table();
        $this->ensure_order_status_history_table();
        $this->ensure_products_table();

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
     * Get a specific product's row inside the user's cart (product_id maps to vendor_products.id)
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

        $this->db->select('cart_items.id as cart_item_id, cart_items.quantity, vp.id as product_id, vp.product_name as product_name, vp.mrp as price, vp.selling_price as sale_price, vp.image as image');
        $this->db->from('cart_items');
        $this->db->join('vendor_products vp', 'vp.id = cart_items.product_id', 'inner');
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

        $this->db->select('cart_items.quantity, vp.mrp as price, vp.selling_price as sale_price');
        $this->db->from('cart_items');
        $this->db->join('vendor_products vp', 'vp.id = cart_items.product_id', 'inner');
        $this->db->where('cart_items.user_id', $user_id);
        $this->db->where('vp.is_active', 1);

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
     * Add a product (vendor_products.id) to the user's cart
     */
    public function add_to_cart()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $product_id = intval($input_data['product_id'] ?? 0); // Maps to vendor_products.id
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

        // Verify product is active in vendor_products
        $product = $this->db->get_where('vendor_products', ['id' => $product_id, 'is_active' => 1])->row();
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
     * Get the full list of products in the cart (joined with vendor_products)
     */
    public function get_cart()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $this->db->select('cart_items.id as cart_item_id, cart_items.quantity, cart_items.created_at as added_at, vp.id as product_id, vp.product_name as product_name, vp.mrp as price, vp.selling_price as sale_price, COALESCE(NULLIF(vp.image, ""), p.image) as image, categories.name as category_name');
        $this->db->from('cart_items');
        $this->db->join('vendor_products vp', 'vp.id = cart_items.product_id', 'inner');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->join('categories', 'categories.id = vp.category_id', 'left');
        $this->db->where('cart_items.user_id', $user_id);
        $this->db->where('vp.is_active', 1);
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
                $product = $this->db->get_where('vendor_products', ['id' => $product_id, 'is_active' => 1])->row();
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
        $product = $this->db->get_where('vendor_products', ['id' => $product_id, 'is_active' => 1])->row();
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

        $this->db->select('wishlist_items.id as wishlist_item_id, wishlist_items.quantity, wishlist_items.created_at as added_at, vp.id as product_id, vp.product_name as product_name, vp.mrp as price, vp.selling_price as sale_price, COALESCE(NULLIF(vp.image, ""), p.image) as image, categories.name as category_name');
        $this->db->from('wishlist_items');
        $this->db->join('vendor_products vp', 'vp.id = wishlist_items.product_id', 'inner');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->join('categories', 'categories.id = vp.category_id', 'left');
        $this->db->where('wishlist_items.user_id', $user_id);
        $this->db->where('vp.is_active', 1);
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

        $this->db->select('wishlist_items.quantity, vp.mrp as price, vp.selling_price as sale_price');
        $this->db->from('wishlist_items');
        $this->db->join('vendor_products vp', 'vp.id = wishlist_items.product_id', 'inner');
        $this->db->where('wishlist_items.user_id', $user_id);
        $this->db->where('vp.is_active', 1);

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
                $product = $this->db->get_where('vendor_products', ['id' => $product_id, 'is_active' => 1])->row();
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
            $product = $this->db->get_where('vendor_products', ['id' => $item->product_id, 'is_active' => 1])->row();
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
    // 5. ADDRESS MANAGEMENT FLOW
    // ==========================================

    /**
     * Get list of user addresses
     */
    public function get_addresses()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_address_table();

        $rows = $this->db
            ->where('user_id', $user_id)
            ->order_by('is_default', 'DESC')
            ->order_by('id', 'DESC')
            ->get('user_addresses')
            ->result();

        $addresses = [];
        foreach ($rows as $row) {
            $addresses[] = $this->format_address($row);
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Addresses fetched successfully.',
                'data' => [
                    'total_addresses' => count($addresses),
                    'addresses'       => $addresses,
                ]
            ]));
    }

    /**
     * Save a new user address
     */
    public function save_address()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_address_table();

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $full_name     = trim($input_data['full_name'] ?? '');
        $mobile        = preg_replace('/[^0-9]/', '', $input_data['mobile'] ?? '');
        $mobile        = substr($mobile, -10);
        $address_line1 = trim($input_data['address_line1'] ?? '');
        $address_line2 = trim($input_data['address_line2'] ?? '');
        $landmark      = trim($input_data['landmark'] ?? '');
        $city          = trim($input_data['city'] ?? '');
        $state         = trim($input_data['state'] ?? '');
        $pincode       = trim($input_data['pincode'] ?? '');
        $country       = trim($input_data['country'] ?? 'India');
        $is_default    = (int)($input_data['is_default'] ?? 0);

        if ($full_name === '' || $mobile === '' || $address_line1 === '' || $city === '' || $state === '' || $pincode === '') {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'full_name, mobile, address_line1, city, state and pincode are required.',
                    'data' => null
                ]));
        }

        if (!is_numeric($mobile) || strlen($mobile) !== 10) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Mobile number must be exactly 10 digits.',
                    'data' => null
                ]));
        }

        if ($is_default === 1) {
            $this->db->where('user_id', $user_id)->update('user_addresses', ['is_default' => 0]);
        }

        $this->db->insert('user_addresses', [
            'user_id'       => $user_id,
            'full_name'     => $full_name,
            'mobile'        => $mobile,
            'address_line1' => $address_line1,
            'address_line2' => $address_line2 ?: null,
            'landmark'      => $landmark ?: null,
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $pincode,
            'country'       => $country ?: 'India',
            'is_default'    => $is_default,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $address_id = $this->db->insert_id();
        $address = $this->db->get_where('user_addresses', ['id' => $address_id, 'user_id' => $user_id])->row();

        return $this->output
            ->set_status_header(201)
            ->set_output(json_encode([
                'status' => true,
                'code' => 201,
                'message' => 'Address saved successfully.',
                'data' => $this->format_address($address)
            ]));
    }

    /**
     * Update user address (supports partial updates)
     */
    public function update_address()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_address_table();

        // Parse input (supports JSON and form-data)
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $address_id = intval($input_data['address_id'] ?? 0);

        if ($address_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'address_id is required.',
                    'data' => null
                ]));
        }

        $address = $this->db->get_where('user_addresses', [
            'id'      => $address_id,
            'user_id' => $user_id,
        ])->row();

        if (!$address) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Address not found.',
                    'data' => null
                ]));
        }

        $update_data = [];

        foreach (['full_name', 'address_line1', 'address_line2', 'landmark', 'city', 'state', 'pincode', 'country'] as $field) {
            if (array_key_exists($field, $input_data)) {
                $update_data[$field] = trim($input_data[$field]);
            }
        }

        if (array_key_exists('mobile', $input_data)) {
            $mobile = preg_replace('/[^0-9]/', '', $input_data['mobile']);
            $mobile = substr($mobile, -10);
            if (!is_numeric($mobile) || strlen($mobile) !== 10) {
                return $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 400,
                        'message' => 'Mobile number must be exactly 10 digits.',
                        'data' => null
                    ]));
            }
            $update_data['mobile'] = $mobile;
        }

        if (array_key_exists('is_default', $input_data)) {
            $update_data['is_default'] = (int) ($input_data['is_default'] ?? 0);
            if ((int) $update_data['is_default'] === 1) {
                $this->db
                    ->where('user_id', $user_id)
                    ->where('id !=', $address_id)
                    ->update('user_addresses', ['is_default' => 0]);
            }
        }

        if (empty($update_data)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'No address data provided for update.',
                    'data' => null
                ]));
        }

        $update_data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where(['id' => $address_id, 'user_id' => $user_id])->update('user_addresses', $update_data);
        $updated_address = $this->db->get_where('user_addresses', ['id' => $address_id, 'user_id' => $user_id])->row();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Address updated successfully.',
                'data' => $this->format_address($updated_address)
            ]));
    }

    /**
     * Delete an address
     */
    public function delete_address()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_address_table();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $address_id = intval($input_data['address_id'] ?? 0);

        if ($address_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'address_id is required.',
                    'data' => null
                ]));
        }

        $address = $this->db->get_where('user_addresses', [
            'id'      => $address_id,
            'user_id' => $user_id,
        ])->row();

        if (!$address) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Address not found.',
                    'data' => null
                ]));
        }

        $this->db->where(['id' => $address_id, 'user_id' => $user_id])->delete('user_addresses');

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Address deleted successfully.',
                'data' => null
            ]));
    }

    // ==========================================
    // 6. ORDERS FLOW
    // ==========================================

    /**
     * Place order (Supports COD and Razorpay Online methods)
     */
    public function place_order()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->check_cart_table();
        $this->ensure_address_table();
        $this->ensure_orders_table();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $address_id     = (int) ($input_data['address_id'] ?? 0);
        $payment_method = strtolower($input_data['payment_method'] ?? 'cod');
        $notes          = trim($input_data['notes'] ?? '');
        $delivery_charge = floatval($input_data['delivery_charge'] ?? 0.00);

        if ($address_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Please select a delivery address.',
                    'data' => null
                ]));
        }

        $address = $this->db->get_where('user_addresses', [
            'id'      => $address_id,
            'user_id' => $user_id,
        ])->row();

        if (!$address) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Delivery address not found.',
                    'data' => null
                ]));
        }

        if (!in_array($payment_method, ['cod', 'online'], true)) {
            $payment_method = 'cod';
        }

        $cart_summary = $this->get_cart_summary_for_order($user_id);

        if (empty($cart_summary['items'])) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Your cart is empty.',
                    'data' => null
                ]));
        }

        // Validate stock and availability
        foreach ($cart_summary['items'] as $item) {
            if (!$item['is_available']) {
                return $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 400,
                        'message' => $item['name'] . ' is currently unavailable.',
                        'data' => null
                    ]));
            }
            if ($item['quantity'] > $item['stock_quantity']) {
                return $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 400,
                        'message' => 'Insufficient stock for ' . $item['name'] . '. Available: ' . $item['stock_quantity'],
                        'data' => null
                    ]));
            }
        }

        $subtotal     = $cart_summary['subtotal'];
        $total_gst    = $cart_summary['total_gst'];
        $discount     = 0.00;
        $total_amount = $subtotal + $total_gst + $delivery_charge - $discount;
        $order_number = 'GMB' . date('Ymd') . strtoupper(substr(uniqid(), -6));

        /* ---------------- ONLINE PAYMENT ---------------- */
        if ($payment_method === 'online') {
            $key_id     = trim((string) $this->config->item('razorpay_key_id')) ?: 'rzp_test_dummy';
            $key_secret = trim((string) $this->config->item('razorpay_key_secret')) ?: 'dummy_secret';
            $currency   = trim((string) $this->config->item('razorpay_currency')) ?: 'INR';

            $gateway = $this->create_razorpay_order([
                'amount'   => (int) round($total_amount * 100),
                'currency' => $currency,
                'receipt'  => 'order_' . $user_id . '_' . time(),
                'notes'    => ['user_id' => (string) $user_id],
            ], $key_id, $key_secret);

            if (empty($gateway['status'])) {
                return $this->output
                    ->set_status_header(502)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 502,
                        'message' => $gateway['message'] ?? 'Unable to create payment order.',
                        'data' => null
                    ]));
            }

            $this->db->trans_begin();

            $this->db->insert('orders', [
                'user_id'           => $user_id,
                'address_id'        => $address_id,
                'order_number'      => $order_number,
                'subtotal'          => $subtotal,
                'gst_amount'        => $total_gst,
                'delivery_charge'   => $delivery_charge,
                'discount'          => $discount,
                'total_amount'      => $total_amount,
                'total_items'       => $cart_summary['total_quantity'],
                'payment_method'    => 'online',
                'payment_status'    => 'pending',
                'razorpay_order_id' => $gateway['data']['id'],
                'status'            => 'pending',
                'notes'             => $notes,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            $order_id = $this->db->insert_id();
            $this->insert_order_items($order_id, $cart_summary['items']);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return $this->output
                    ->set_status_header(500)
                    ->set_output(json_encode([
                        'status' => false,
                        'code' => 500,
                        'message' => 'Failed to initiate order. Please try again.',
                        'data' => null
                    ]));
            }

            $this->db->trans_commit();

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Razorpay order created. Complete the payment to confirm.',
                    'data' => [
                        'order_id'          => $order_id,
                        'order_number'      => $order_number,
                        'amount'            => $total_amount,
                        'currency'          => $currency,
                        'key_id'            => $key_id,
                        'gst_amount'        => $total_gst,
                        'delivery_charge'   => $delivery_charge,
                        'razorpay_order_id' => $gateway['data']['id'],
                    ]
                ]));
        }

        /* ---------------- COD ---------------- */
        $this->db->trans_begin();

        $this->db->insert('orders', [
            'user_id'         => $user_id,
            'address_id'      => $address_id,
            'order_number'    => $order_number,
            'subtotal'        => $subtotal,
            'gst_amount'      => $total_gst,
            'delivery_charge' => $delivery_charge,
            'discount'        => $discount,
            'total_amount'    => $total_amount,
            'total_items'     => $cart_summary['total_quantity'],
            'payment_method'  => 'cod',
            'payment_status'  => 'pending',
            'status'          => 'pending',
            'notes'           => $notes,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        $order_id = $this->db->insert_id();
        $this->insert_order_items($order_id, $cart_summary['items']);
        $this->reduce_stock($cart_summary['items']);
        $this->insert_status_history($order_id, 'pending', 'Order placed successfully', 'system');
        $this->db->where('user_id', $user_id)->delete('cart_items');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 500,
                    'message' => 'Failed to place order. Please try again.',
                    'data' => null
                ]));
        }

        $this->db->trans_commit();

        return $this->output
            ->set_status_header(201)
            ->set_output(json_encode([
                'status' => true,
                'code' => 201,
                'message' => 'Order placed successfully.',
                'data' => $this->format_order_full($order_id, $user_id)
            ]));
    }

    /**
     * Verify payment status for online orders
     */
    public function verify_order_payment()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_orders_table();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $razorpay_order_id   = trim($input_data['razorpay_order_id'] ?? '');
        $razorpay_payment_id = trim($input_data['razorpay_payment_id'] ?? '');
        $razorpay_signature  = trim($input_data['razorpay_signature'] ?? '');

        if ($razorpay_order_id === '' || $razorpay_payment_id === '' || $razorpay_signature === '') {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Missing payment verification details.',
                    'data' => null
                ]));
        }

        $order = $this->db->get_where('orders', [
            'user_id'           => $user_id,
            'razorpay_order_id' => $razorpay_order_id,
        ])->row();

        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Order not found for verification.',
                    'data' => null
                ]));
        }

        $key_secret          = trim((string) $this->config->item('razorpay_key_secret')) ?: 'dummy_secret';
        $expected_signature  = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $key_secret);

        if (!hash_equals($expected_signature, $razorpay_signature)) {
            $this->db->where('id', $order->id)->update('orders', [
                'payment_status' => 'failed',
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Payment verification failed.',
                    'data' => null
                ]));
        }

        $this->db->trans_begin();

        $this->db->where('id', $order->id)->update('orders', [
            'payment_status'      => 'paid',
            'status'              => 'confirmed',
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature'  => $razorpay_signature,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        // Reduce stock now that payment is confirmed
        $items = $this->db->where('order_id', $order->id)->get('order_items')->result_array();
        $this->reduce_stock($items);

        $this->insert_status_history($order->id, 'confirmed', 'Payment received successfully', 'system');
        $this->db->where('user_id', $user_id)->delete('cart_items');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 500,
                    'message' => 'Payment received, but order could not be confirmed. Please contact support.',
                    'data' => null
                ]));
        }

        $this->db->trans_commit();

        // Load Shiprocket if library exists
        if (file_exists(APPPATH . 'libraries/Shiprocket.php')) {
            try {
                $this->load->library('shiprocket');
                $this->shiprocket->create_order($order->id);
            } catch (Exception $e) {
                // Ignore Shiprocket load errors during testing
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Payment verified. Order confirmed.',
                'data' => $this->format_order_full($order->id, $user_id)
            ]));
    }

    /**
     * Get list of customer orders with pagination and status filters
     */
    public function get_orders()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_orders_table();

        $current_page   = max(1, (int) ($this->input->get('page') ?? 1));
        $items_per_page = 10;
        $offset         = ($current_page - 1) * $items_per_page;
        $status_filter  = trim($this->input->get('status') ?? '');
        $valid_statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'];

        $this->db->where('user_id', $user_id);
        if ($status_filter !== '' && in_array($status_filter, $valid_statuses, true)) {
            $this->db->where('status', $status_filter);
        }
        $total_orders = $this->db->count_all_results('orders');

        $this->db->where('user_id', $user_id);
        if ($status_filter !== '' && in_array($status_filter, $valid_statuses, true)) {
            $this->db->where('status', $status_filter);
        }
        $orders = $this->db->order_by('id', 'DESC')->limit($items_per_page, $offset)->get('orders')->result();

        $order_list = array_map(function ($order) {
            $first_item = $this->db->where('order_id', $order->id)->limit(1)->get('order_items')->row();

            $image_url = $first_item && !empty($first_item->product_image)
                ? $this->get_product_image_url($first_item->product_image)
                : '';

            return [
                'order_id'          => (int) $order->id,
                'order_number'      => $order->order_number,
                'status'            => $order->status,
                'payment_method'    => $order->payment_method,
                'payment_status'    => $order->payment_status,
                'total_amount'      => (float) $order->total_amount,
                'total_items'       => (int) $order->total_items,
                'first_item_name'   => $first_item->product_name ?? '',
                'first_item_image'  => $image_url,
                'created_at'        => $order->created_at,
            ];
        }, $orders);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Orders fetched successfully.',
                'data' => [
                    'total_orders'   => $total_orders,
                    'current_page'   => $current_page,
                    'items_per_page' => $items_per_page,
                    'total_pages'    => (int) ceil($total_orders / $items_per_page),
                    'orders'         => $order_list,
                ]
            ]));
    }

    /**
     * Get detailed structure of a single customer order
     */
    public function get_order_details($order_id = 0)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_orders_table();

        $order_id = intval($order_id ?: $this->input->get('order_id', true));

        if ($order_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'A valid order ID is required.',
                    'data' => null
                ]));
        }

        $order = $this->format_order_full($order_id, $user_id);

        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Order not found.',
                    'data' => null
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Order details fetched successfully.',
                'data' => $order
            ]));
    }

    /**
     * Cancel order
     */
    public function cancel_order()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_orders_table();

        // Parse input
        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $order_id = intval($input_data['order_id'] ?? 0);
        $reason   = trim($input_data['reason'] ?? '');

        if ($order_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'A valid order ID is required.',
                    'data' => null
                ]));
        }

        $order = $this->db->get_where('orders', [
            'id'      => $order_id,
            'user_id' => $user_id,
        ])->row();

        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Order not found.',
                    'data' => null
                ]));
        }

        $cancellable_statuses = ['pending', 'confirmed'];
        if (!in_array($order->status, $cancellable_statuses, true)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Order cannot be cancelled. Current status: ' . $order->status,
                    'data' => null
                ]));
        }

        $this->db->trans_begin();

        $this->db->where('id', $order_id)->update('orders', [
            'status'        => 'cancelled',
            'cancelled_by'  => 'user',
            'cancel_reason' => $reason,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        // Only restore stock if it was deducted
        if ($order->payment_method === 'cod' || $order->payment_status === 'paid') {
            $this->restore_stock_for_order($order_id);
        }

        $this->insert_status_history($order_id, 'cancelled', $reason !== '' ? $reason : 'Cancelled by user', 'user');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 500,
                    'message' => 'Failed to cancel order. Please try again.',
                    'data' => null
                ]));
        }

        $this->db->trans_commit();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Order cancelled successfully.',
                'data' => [
                    'order_id'     => $order_id,
                    'order_number' => $order->order_number,
                    'status'       => 'cancelled',
                ]
            ]));
    }

    // ==========================================
    // 7. VENDOR ORDERS MANAGEMENT FLOW
    // ==========================================

    /**
     * Get vendor-specific orders
     */
    public function get_vendor_orders()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        // Authenticate vendor
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        $decoded = $this->verify_jwt($token);
        if (!$decoded || !isset($decoded->data->id) || $decoded->data->role !== 'vendor') {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized or not a vendor',
                    'data' => null
                ]));
        }
        $vendor_id = $decoded->data->id;

        $status_filter = trim($this->input->get('status') ?? '');

        // Query orders that have items for this vendor
        $this->db->select('orders.*, users.name as customer_name, users.mobile as customer_mobile');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id', 'left');
        $this->db->join('order_items', 'order_items.order_id = orders.id', 'inner');
        $this->db->where('order_items.vendor_id', $vendor_id);

        if ($status_filter !== '') {
            $db_status = $status_filter;
            if ($status_filter === 'accepted') $db_status = 'confirmed';
            $this->db->where('orders.status', $db_status);
        }

        $this->db->group_by('orders.id');
        $this->db->order_by('orders.id', 'DESC');
        $orders = $this->db->get()->result();

        $order_list = [];
        foreach ($orders as $order) {
            // Get vendor-specific items for this order
            $items = $this->db
                ->where('order_id', $order->id)
                ->where('vendor_id', $vendor_id)
                ->get('order_items')
                ->result();

            $vendor_total = 0.00;
            $vendor_items = [];
            foreach ($items as $item) {
                $item->product_image = !empty($item->product_image) ? base_url($item->product_image) : '';
                $item_total = ($item->price * $item->quantity) + $item->gst_amount;
                $vendor_total += $item_total;
                $vendor_items[] = [
                    'product_name' => $item->product_name,
                    'quantity' => (int)$item->quantity,
                    'price' => (float)$item->price,
                    'image' => $item->product_image
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
                'customer_name' => $order->customer_name,
                'customer_mobile' => $order->customer_mobile,
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

    /**
     * Update order status by vendor (accept/reject)
     */
    public function update_vendor_order_status()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        // Authenticate vendor
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        $token = null;
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        $decoded = $this->verify_jwt($token);
        if (!$decoded || !isset($decoded->data->id) || $decoded->data->role !== 'vendor') {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized or not a vendor',
                    'data' => null
                ]));
        }
        $vendor_id = $decoded->data->id;

        $input_data = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $order_id = intval($input_data['order_id'] ?? 0);
        $status = trim($input_data['status'] ?? ''); // 'accepted', 'rejected', 'processing', 'packed', 'out_for_delivery', 'delivered'
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

        // Check if order contains vendor items
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

        // Map status
        $db_status = $status;
        if ($status === 'accepted') $db_status = 'confirmed';
        if ($status === 'rejected') $db_status = 'cancelled';

        $this->db->where('id', $order_id)->update('orders', [
            'status' => $db_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log status history
        $this->insert_status_history($order_id, $db_status, $remarks ?: 'Updated by vendor', 'vendor');

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Order status updated successfully',
                'data' => null
            ]));
    }

    // ==========================================
    // 8. CORE APPLICATION FEATURES (BROWSE VENDOR PRODUCTS)
    // ==========================================

    /**
     * Home API: Fetches active categories and products from vendor_products table
     */
    public function home()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $search = trim($this->input->get('search', true));
        $category_id = trim($this->input->get('category_id', true));

        // 1. Fetch Categories
        $categories = $this->db->where('is_active', 1)->get('categories')->result();
        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;
        }

        // 2. Fetch Products from vendor_products
        $this->get_vendor_products_query();
        $this->db->where('vp.is_active', 1);

        if (!empty($category_id)) {
            $this->db->where('vp.category_id', $category_id);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('vp.product_name', $search);
            $this->db->or_like('vp.brand', $search);
            $this->db->group_end();
        }

        $this->db->order_by('vp.id', 'DESC');
        if (empty($search) && empty($category_id)) {
            $this->db->limit(20);
        }

        $products = $this->db->get()->result();

        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;
            $prod->gallery_urls = [];
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
     * Get active products by category ID from vendor_products table (No JWT token needed)
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

        $this->get_vendor_products_query();
        $this->db->where('vp.category_id', $category_id);
        $this->db->where('vp.is_active', 1);
        $this->db->order_by('vp.id', 'DESC');

        $products = $this->db->get()->result();

        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;
            $prod->gallery_urls = [];
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
     * Get list of all active products from vendor_products table (No JWT token needed)
     */
    public function get_product_list()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $search = trim($this->input->get('search', true));

        $this->get_vendor_products_query();
        $this->db->where('vp.is_active', 1);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('vp.product_name', $search);
            $this->db->or_like('vp.brand', $search);
            $this->db->group_end();
        }

        $this->db->order_by('vp.id', 'DESC');
        $products = $this->db->get()->result();

        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;
            $prod->gallery_urls = [];
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
     * Get details of a single active product from vendor_products table (No JWT token needed)
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

        $this->get_vendor_products_query();
        $this->db->where('vp.id', $id);
        $this->db->where('vp.is_active', 1);
        $product = $this->db->get()->row();

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
        $product->store_photo_url = !empty($product->store_photo) ? base_url($product->store_photo) : null;
        $product->gallery_urls = [];

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
     * Search categories (No JWT token needed)
     */
    public function search_categories()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $search = trim($this->input->get('search', true) ?? $this->input->get('q', true) ?? $this->input->get('query', true) ?? '');
        $parent_id = $this->input->get('parent_id', true);
        $is_active = $this->input->get('is_active', true);
        $sort_by = trim($this->input->get('sort_by', true) ?? 'name_asc');
        $page = max(1, (int)($this->input->get('page', true) ?? 1));
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? 10)));
        $offset = ($page - 1) * $limit;

        // Base query conditions setup
        $this->db->group_start();
        $this->db->where('1=1', null, false);
        $this->db->group_end();

        // Search query filter
        if (!empty($search)) {
            $keywords = array_filter(explode(' ', $search));
            $this->db->group_start();
            foreach ($keywords as $word) {
                $word = trim($word);
                if ($word !== '') {
                    $this->db->group_start();
                    $this->db->like('name', $word);
                    $this->db->or_like('description', $word);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();
        }

        // Parent ID filter
        if ($parent_id !== null && $parent_id !== '') {
            $this->db->where('parent_id', (int)$parent_id);
        }

        // Active status filter (default to active = 1)
        if ($is_active !== null && $is_active !== '') {
            $this->db->where('is_active', (int)$is_active ? 1 : 0);
        } else {
            $this->db->where('is_active', 1);
        }

        // Count total results (false parameter preserves query builder state)
        $total_records = $this->db->count_all_results('categories', false);

        // Apply sorting
        switch ($sort_by) {
            case 'name_desc':
                $this->db->order_by('name', 'DESC');
                break;
            case 'id_asc':
                $this->db->order_by('id', 'ASC');
                break;
            case 'id_desc':
                $this->db->order_by('id', 'DESC');
                break;
            case 'name_asc':
            default:
                $this->db->order_by('name', 'ASC');
                break;
        }

        // Apply pagination limit & offset
        $this->db->limit($limit, $offset);
        $categories = $this->db->get()->result();

        // Map absolute image URLs
        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;
        }

        $total_pages = ceil($total_records / $limit);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Categories searched successfully',
                'data' => [
                    'categories' => $categories,
                    'pagination' => [
                        'total_records' => (int)$total_records,
                        'current_page' => $page,
                        'limit' => $limit,
                        'total_pages' => $total_pages
                    ]
                ]
            ]));
    }

    /**
     * Search products with filtering and facets (No JWT token needed)
     */
    public function search_products()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $search = trim($this->input->get('search', true) ?? $this->input->get('q', true) ?? $this->input->get('query', true) ?? '');
        $category_param = $this->input->get('category_id', true);
        $brand_param = $this->input->get('brand', true);
        $min_price = $this->input->get('min_price', true);
        $max_price = $this->input->get('max_price', true);
        $in_stock = $this->input->get('in_stock', true);
        $vendor_id = $this->input->get('vendor_id', true);
        $sort_by = trim($this->input->get('sort_by', true) ?? 'relevance');
        $page = max(1, (int)($this->input->get('page', true) ?? 1));
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? 10)));
        $offset = ($page - 1) * $limit;

        // Parse category filter (supports comma-separated list or array)
        $category_ids = [];
        if (!empty($category_param)) {
            if (is_array($category_param)) {
                $category_ids = array_map('intval', $category_param);
            } else {
                $category_ids = array_filter(array_map('intval', explode(',', $category_param)));
            }
        }

        // Parse brand filter (supports comma-separated list or array)
        $brands = [];
        if (!empty($brand_param)) {
            if (is_array($brand_param)) {
                $brands = array_map('trim', $brand_param);
            } else {
                $brands = array_filter(array_map('trim', explode(',', $brand_param)));
            }
        }

        // -------------------------------------------------------------
        // Step 1: Compute facets (matching categories, brands, price range)
        // based on the search keyword before applying specific filters
        // -------------------------------------------------------------
        $this->db->select('vp.category_id, c.name as category_name, vp.brand, vp.selling_price');
        $this->db->from('vendor_products vp');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->join('categories c', 'c.id = vp.category_id', 'left');
        $this->db->join('users u', 'u.id = vp.vendor_id', 'left');
        $this->db->where('vp.is_active', 1);

        if ($vendor_id !== null && $vendor_id !== '') {
            $this->db->where('vp.vendor_id', (int)$vendor_id);
        }

        if (!empty($search)) {
            $keywords = array_filter(explode(' ', $search));
            foreach ($keywords as $word) {
                $word = trim($word);
                if ($word !== '') {
                    $this->db->group_start();
                    $this->db->like('vp.product_name', $word);
                    $this->db->or_like('vp.brand', $word);
                    $this->db->or_like('vp.description', $word);
                    $this->db->or_like('c.name', $word);
                    $this->db->or_like('u.store_name', $word);
                    $this->db->group_end();
                }
            }
        }

        $facet_rows = $this->db->get()->result();

        $facet_categories = [];
        $facet_brands = [];
        $facet_min_price = null;
        $facet_max_price = null;

        foreach ($facet_rows as $row) {
            // Category facet
            if (!empty($row->category_id)) {
                $cat_id = (int)$row->category_id;
                if (!isset($facet_categories[$cat_id])) {
                    $facet_categories[$cat_id] = [
                        'id' => $cat_id,
                        'name' => $row->category_name ?? 'Uncategorized',
                        'count' => 0
                    ];
                }
                $facet_categories[$cat_id]['count']++;
            }

            // Brand facet
            if (!empty($row->brand)) {
                $b_name = trim($row->brand);
                if (!isset($facet_brands[$b_name])) {
                    $facet_brands[$b_name] = [
                        'name' => $b_name,
                        'count' => 0
                    ];
                }
                $facet_brands[$b_name]['count']++;
            }

            // Price range facet
            $price = (float)$row->selling_price;
            if ($facet_min_price === null || $price < $facet_min_price) {
                $facet_min_price = $price;
            }
            if ($facet_max_price === null || $price > $facet_max_price) {
                $facet_max_price = $price;
            }
        }

        // -------------------------------------------------------------
        // Step 2: Fetch products with filters, sorting, and pagination
        // -------------------------------------------------------------
        $this->get_vendor_products_query();
        $this->db->where('vp.is_active', 1);

        // Search term conditions
        if (!empty($search)) {
            $keywords = array_filter(explode(' ', $search));
            foreach ($keywords as $word) {
                $word = trim($word);
                if ($word !== '') {
                    $this->db->group_start();
                    $this->db->like('vp.product_name', $word);
                    $this->db->or_like('vp.brand', $word);
                    $this->db->or_like('vp.description', $word);
                    $this->db->or_like('c.name', $word);
                    $this->db->or_like('u.store_name', $word);
                    $this->db->group_end();
                }
            }
        }

        // Category filter
        if (!empty($category_ids)) {
            $this->db->where_in('vp.category_id', $category_ids);
        }

        // Brand filter
        if (!empty($brands)) {
            $this->db->where_in('vp.brand', $brands);
        }

        // Price range filters
        if ($min_price !== null && $min_price !== '') {
            $this->db->where('vp.selling_price >=', (float)$min_price);
        }
        if ($max_price !== null && $max_price !== '') {
            $this->db->where('vp.selling_price <=', (float)$max_price);
        }

        // Stock filter
        if ($in_stock !== null && ($in_stock == 1 || $in_stock === 'true')) {
            $this->db->where('vp.stock >', 0);
        }

        // Vendor filter
        if ($vendor_id !== null && $vendor_id !== '') {
            $this->db->where('vp.vendor_id', (int)$vendor_id);
        }

        // Count total results matching filters (false parameter preserves query builder state)
        $total_records = $this->db->count_all_results('', false);

        // Sorting
        switch ($sort_by) {
            case 'price_low_to_high':
                $this->db->order_by('vp.selling_price', 'ASC');
                break;
            case 'price_high_to_low':
                $this->db->order_by('vp.selling_price', 'DESC');
                break;
            case 'name_asc':
                $this->db->order_by('vp.product_name', 'ASC');
                break;
            case 'name_desc':
                $this->db->order_by('vp.product_name', 'DESC');
                break;
            case 'newest':
                $this->db->order_by('vp.id', 'DESC');
                break;
            case 'relevance':
            default:
                // Default sorting order
                $this->db->order_by('vp.id', 'DESC');
                break;
        }

        // Pagination limit & offset
        $this->db->limit($limit, $offset);
        $products = $this->db->get()->result();

        // Process absolute image and gallery URLs
        foreach ($products as $prod) {
            $prod->image_url = !empty($prod->image) ? base_url($prod->image) : null;
            $prod->store_photo_url = !empty($prod->store_photo) ? base_url($prod->store_photo) : null;
            $prod->gallery_urls = [];
            
            // If global product table has a gallery, pull it
            if (!empty($prod->product_id)) {
                $g_query = $this->db->select('gallery')->where('id', $prod->product_id)->get('products')->row();
                if ($g_query && !empty($g_query->gallery)) {
                    $gallery_images = json_decode($g_query->gallery, true);
                    if (is_array($gallery_images)) {
                        foreach ($gallery_images as $img) {
                            $prod->gallery_urls[] = base_url($img);
                        }
                    }
                }
            }
        }

        $total_pages = ceil($total_records / $limit);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Products searched successfully',
                'data' => [
                    'products' => $products,
                    'pagination' => [
                        'total_records' => (int)$total_records,
                        'current_page' => $page,
                        'limit' => $limit,
                        'total_pages' => $total_pages
                    ],
                    'filters' => [
                        'categories' => array_values($facet_categories),
                        'brands' => array_values($facet_brands),
                        'price_range' => [
                            'min' => $facet_min_price !== null ? (float)$facet_min_price : 0,
                            'max' => $facet_max_price !== null ? (float)$facet_max_price : 0
                        ]
                    ]
                ]
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
    // 9. LOGOUT & SESSION TERMINATION
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
    // 10. PRIVATE HELPER METHODS
    // ==========================================

    private function get_vendor_products_query()
    {
        $this->db->select('vp.id, vp.vendor_id, vp.product_id, vp.product_name as name, vp.brand, vp.category_id, vp.unit, vp.mrp as price, vp.selling_price as sale_price, vp.stock, vp.description, COALESCE(vp.image, p.image) as image, vp.is_active, c.name as category_name, u.name as vendor_name, u.store_name, u.address as store_address, u.contact_number as store_contact, u.opening_time as store_opening_time, u.closing_time as store_closing_time, u.store_photo');
        $this->db->from('vendor_products vp');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->join('categories c', 'c.id = vp.category_id', 'left');
        $this->db->join('users u', 'u.id = vp.vendor_id', 'left');
    }

    private function insert_order_items(int $order_id, array $items): void
    {
        foreach ($items as $item) {
            $this->db->insert('order_items', [
                'order_id'      => $order_id,
                'product_id'    => $item['product_id'], // vendor_products.id
                'product_name'  => $item['name'],
                'product_image' => $item['image'],
                'price'         => $item['price'],
                'gst_percent'   => $item['gst_percent'],
                'gst_amount'    => $item['gst_amount'],
                'quantity'      => $item['quantity'],
                'subtotal'      => $item['line_total'], // Price × Qty (before GST)
                'vendor_id'     => $item['vendor_id'],
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function reduce_stock(array $items): void
    {
        foreach ($items as $item) {
            // Deduct stock exclusively in vendor_products
            $this->db->set('stock', 'stock - ' . (int) $item['quantity'], false)
                ->where('id', $item['product_id']) // product_id is vendor_products.id
                ->update('vendor_products');
        }
    }

    private function restore_stock_for_order(int $order_id): void
    {
        $items = $this->db->where('order_id', $order_id)->get('order_items')->result();
        foreach ($items as $item) {
            // Restore stock exclusively in vendor_products
            $this->db->set('stock', 'stock + ' . (int) $item->quantity, false)
                ->where('id', $item->product_id) // product_id is vendor_products.id
                ->update('vendor_products');
        }
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

    private function create_razorpay_order(array $payload, string $key_id, string $key_secret): array
    {
        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $key_id . ':' . $key_secret,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response    = curl_exec($ch);
        $curl_error  = curl_error($ch);
        $status_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['status' => false, 'message' => $curl_error ?: 'Unable to connect to Razorpay.'];
        }

        $decoded = json_decode($response, true);

        if ($status_code < 200 || $status_code >= 300 || !is_array($decoded)) {
            $message = 'Unable to create Razorpay order.';
            if (is_array($decoded) && !empty($decoded['error']['description'])) {
                $message = (string) $decoded['error']['description'];
            }
            return ['status' => false, 'message' => $message];
        }

        return ['status' => true, 'data' => $decoded];
    }

    private function format_order_full(int $order_id, int $user_id)
    {
        // Get order details
        $order = $this->db->get_where('orders', [
            'id'      => $order_id,
            'user_id' => $user_id,
        ])->row_array();

        if (!$order) {
            return null;
        }

        // Get order items
        $items = $this->db
            ->select('id, product_id, product_name, product_image, price, gst_percent, gst_amount, quantity, subtotal, vendor_id')
            ->where('order_id', $order_id)
            ->get('order_items')
            ->result_array();

        foreach ($items as &$item) {
            $item['product_image'] = $this->get_product_image_url($item['product_image']);
            $item['id'] = (int) $item['id'];
            $item['product_id'] = (int) $item['product_id'];
            $item['vendor_id'] = (int) $item['vendor_id'];
            $item['price'] = (float) $item['price'];
            $item['gst_percent'] = (float) $item['gst_percent'];
            $item['gst_amount'] = (float) $item['gst_amount'];
            $item['quantity'] = (int) $item['quantity'];
            $item['subtotal'] = (float) $item['subtotal'];
            $item['total_amount'] = round($item['subtotal'] + $item['gst_amount'], 2);
        }

        // Get delivery address
        $delivery_address = $this->db->get_where('user_addresses', [
            'id' => $order['address_id'],
        ])->row_array();

        // Get status history
        $status_history = $this->db
            ->where('order_id', $order_id)
            ->order_by('created_at', 'DESC')
            ->get('order_status_history')
            ->result_array();

        return [
            'order_id'           => (int) $order['id'],
            'order_number'       => $order['order_number'],
            'status'             => $order['status'],
            'payment_method'     => $order['payment_method'],
            'payment_status'     => $order['payment_status'],
            'subtotal'           => (float) $order['subtotal'],
            'gst_amount'         => (float) $order['gst_amount'],
            'delivery_charge'    => (float) $order['delivery_charge'],
            'expected_delivery' => $order['expected_delivery_date'] ?? null,
            'discount'           => (float) $order['discount'],
            'total_amount'       => (float) $order['total_amount'],
            'total_items'        => (int) $order['total_items'],
            'notes'              => $order['notes'] ?? '',
            'cancel_reason'      => $order['cancel_reason'] ?? null,
            'cancelled_by'       => $order['cancelled_by'] ?? null,
            'created_at'         => $order['created_at'],
            'updated_at'         => $order['updated_at'],
            'awb_code'           => $order['awb_code'] ?? null,
            'courier_name'       => $order['courier_name'] ?? null,
            'tracking_status'    => $order['tracking_status'] ?? null,
            'pickup_scheduled'   => (bool) ($order['pickup_scheduled'] ?? false),
            'invoice_url'        => $order['invoice_url'] ?? null,
            'items'              => $items,
            'delivery_address'   => $delivery_address,
            'status_history'     => $status_history,
        ];
    }

    private function get_cart_summary_for_order($user_id)
    {
        $this->db->select('cart_items.quantity, vp.id as product_id, vp.product_name as name, vp.mrp as price, vp.selling_price as sale_price, COALESCE(NULLIF(vp.image, ""), p.image) as image, vp.is_active, vp.stock as stock_quantity, vp.vendor_id');
        $this->db->from('cart_items');
        $this->db->join('vendor_products vp', 'vp.id = cart_items.product_id', 'inner');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->where('cart_items.user_id', $user_id);
        $cart_items = $this->db->get()->result_array();

        $items = [];
        $subtotal = 0.00;
        $total_gst = 0.00;
        $total_quantity = 0;

        foreach ($cart_items as $ci) {
            $price = floatval($ci['price']);
            $sale_price = $ci['sale_price'] !== null ? floatval($ci['sale_price']) : null;
            $active_price = $sale_price !== null ? $sale_price : $price;

            $gst_percent = 0.00;
            $gst_amount = 0.00;
            $line_total = $active_price * $ci['quantity'];

            $subtotal += $line_total;
            $total_gst += $gst_amount;
            $total_quantity += $ci['quantity'];

            $is_available = (int)$ci['is_active'] === 1;

            $items[] = [
                'product_id' => (int)$ci['product_id'],
                'name' => $ci['name'],
                'image' => $ci['image'],
                'price' => $active_price,
                'gst_percent' => $gst_percent,
                'gst_amount' => $gst_amount,
                'quantity' => (int)$ci['quantity'],
                'line_total' => $line_total,
                'is_available' => $is_available,
                'stock_quantity' => intval($ci['stock_quantity']),
                'vendor_id' => intval($ci['vendor_id'])
            ];
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_gst' => $total_gst,
            'total_quantity' => $total_quantity
        ];
    }

    private function get_product_image_url($image)
    {
        return !empty($image) ? base_url($image) : '';
    }

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

    private function ensure_address_table()
    {
        if (!$this->db->table_exists('user_addresses')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `user_addresses` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `full_name` VARCHAR(100) NOT NULL,
                    `mobile` VARCHAR(15) NOT NULL,
                    `address_line1` VARCHAR(255) NOT NULL,
                    `address_line2` VARCHAR(255) DEFAULT NULL,
                    `landmark` VARCHAR(150) DEFAULT NULL,
                    `city` VARCHAR(100) NOT NULL,
                    `state` VARCHAR(100) NOT NULL,
                    `pincode` VARCHAR(10) NOT NULL,
                    `country` VARCHAR(100) DEFAULT 'India',
                    `is_default` TINYINT(1) DEFAULT 0,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }

    private function format_address($address): array
    {
        return [
            'id'            => (int) $address->id,
            'full_name'     => $address->full_name ?? '',
            'mobile'        => $address->mobile ?? '',
            'address_line1' => $address->address_line1 ?? '',
            'address_line2' => $address->address_line2 ?? '',
            'landmark'      => $address->landmark ?? '',
            'city'          => $address->city ?? '',
            'state'         => $address->state ?? '',
            'pincode'       => $address->pincode ?? '',
            'country'       => $address->country ?? 'India',
            'is_default'    => (int) ($address->is_default ?? 0),
            'created_at'    => $address->created_at ?? null,
            'updated_at'    => $address->updated_at ?? null,
        ];
    }

    private function ensure_orders_table()
    {
        if (!$this->db->field_exists('address_id', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `address_id` INT DEFAULT NULL AFTER `user_id`");
        }
        if (!$this->db->field_exists('subtotal', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `subtotal` DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$this->db->field_exists('gst_amount', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `gst_amount` DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$this->db->field_exists('delivery_charge', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `delivery_charge` DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$this->db->field_exists('discount', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `discount` DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$this->db->field_exists('total_items', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `total_items` INT DEFAULT 0");
        }
        if (!$this->db->field_exists('notes', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `notes` TEXT DEFAULT NULL");
        }
        if (!$this->db->field_exists('cancel_reason', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `cancel_reason` TEXT DEFAULT NULL");
        }
        if (!$this->db->field_exists('cancelled_by', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `cancelled_by` VARCHAR(50) DEFAULT NULL");
        }
        if (!$this->db->field_exists('expected_delivery_date', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `expected_delivery_date` DATE DEFAULT NULL");
        }
        if (!$this->db->field_exists('razorpay_order_id', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `razorpay_order_id` VARCHAR(100) DEFAULT NULL");
        }
        if (!$this->db->field_exists('razorpay_payment_id', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `razorpay_payment_id` VARCHAR(100) DEFAULT NULL");
        }
        if (!$this->db->field_exists('razorpay_signature', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `razorpay_signature` VARCHAR(255) DEFAULT NULL");
        }
        if (!$this->db->field_exists('awb_code', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `awb_code` VARCHAR(100) DEFAULT NULL");
        }
        if (!$this->db->field_exists('courier_name', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `courier_name` VARCHAR(100) DEFAULT NULL");
        }
        if (!$this->db->field_exists('tracking_status', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `tracking_status` VARCHAR(100) DEFAULT NULL");
        }
        if (!$this->db->field_exists('pickup_scheduled', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `pickup_scheduled` TINYINT(1) DEFAULT 0");
        }
        if (!$this->db->field_exists('invoice_url', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `invoice_url` VARCHAR(255) DEFAULT NULL");
        }
        if (!$this->db->field_exists('created_at', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `created_at` DATETIME DEFAULT NULL");
        }
        if (!$this->db->field_exists('updated_at', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `updated_at` DATETIME DEFAULT NULL");
        }
        $this->db->query("ALTER TABLE `orders` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
        $this->db->query("ALTER TABLE `orders` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
    }

    private function ensure_order_items_table()
    {
        if (!$this->db->field_exists('product_name', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `product_name` VARCHAR(255) NOT NULL AFTER `product_id`");
        }
        if (!$this->db->field_exists('product_image', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `product_image` VARCHAR(255) DEFAULT NULL AFTER `product_name`");
        }
        if (!$this->db->field_exists('gst_percent', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `gst_percent` DECIMAL(5,2) DEFAULT 0.00 AFTER `price`");
        }
        if (!$this->db->field_exists('gst_amount', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `gst_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `gst_percent`");
        }
        if (!$this->db->field_exists('subtotal', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `subtotal` DECIMAL(10,2) DEFAULT 0.00 AFTER `quantity`");
        }
        if (!$this->db->field_exists('vendor_id', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `vendor_id` INT NOT NULL DEFAULT 0 AFTER `subtotal`");
        }
        if (!$this->db->field_exists('created_at', 'order_items')) {
            $this->db->query("ALTER TABLE `order_items` ADD COLUMN `created_at` DATETIME DEFAULT NULL");
        }
    }

    private function ensure_order_status_history_table()
    {
        if (!$this->db->table_exists('order_status_history')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `order_status_history` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `order_id` INT NOT NULL,
                    `status` VARCHAR(50) NOT NULL,
                    `remarks` TEXT DEFAULT NULL,
                    `changed_by` VARCHAR(50) NOT NULL,
                    `created_at` DATETIME NOT NULL,
                    KEY `order_id` (`order_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }

    private function ensure_products_table()
    {
        if ($this->db->field_exists('gst_percent', 'products')) {
            $this->db->query("ALTER TABLE `products` DROP COLUMN `gst_percent`");
        }
        if ($this->db->field_exists('stock', 'products')) {
            $this->db->query("ALTER TABLE `products` DROP COLUMN `stock`");
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