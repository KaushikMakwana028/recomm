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

        $latitude = isset($input_data['latitude']) && is_numeric($input_data['latitude']) ? floatval($input_data['latitude']) : null;
        $longitude = isset($input_data['longitude']) && is_numeric($input_data['longitude']) ? floatval($input_data['longitude']) : null;

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
            'latitude'      => $latitude,
            'longitude'     => $longitude,
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

        if (array_key_exists('latitude', $input_data)) {
            $update_data['latitude'] = is_numeric($input_data['latitude']) ? floatval($input_data['latitude']) : null;
        }
        if (array_key_exists('longitude', $input_data)) {
            $update_data['longitude'] = is_numeric($input_data['longitude']) ? floatval($input_data['longitude']) : null;
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

        $address_id      = (int) ($input_data['address_id'] ?? 0);
        $payment_method  = strtolower($input_data['payment_method'] ?? 'cod');
        $notes           = trim($input_data['notes'] ?? '');
        $delivery_type   = isset($input_data['delivery_type']) ? trim($input_data['delivery_type']) : 'normal'; // 'normal' or 'urgent'
        $delivery_option = isset($input_data['delivery_option']) ? trim($input_data['delivery_option']) : 'self'; // 'self' or 'delivery_partner'

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

        // Server-side distance and delivery charge calculation (Never trust client distance or delivery_charge)
        $this->load->helper('distance');
        $vendor_id = (int) ($cart_summary['items'][0]['vendor_id'] ?? 0);
        $vendor = null;
        if ($vendor_id > 0) {
            $vendor = $this->db->select('id, latitude, longitude, prep_time_minutes, lunch_window_start, lunch_window_end, dinner_window_start, dinner_window_end, slot_immediately_enabled, slot_later_enabled, slot_lunch_enabled, slot_dinner_enabled, slot_custom_enabled, opening_time, closing_time, is_holiday')->get_where('users', ['id' => $vendor_id])->row();
        }

        $distance = null;
        $distance_method = 'pure_math';

        if (
            $vendor && $address &&
            $vendor->latitude !== null && $vendor->longitude !== null &&
            $address->latitude !== null && $address->longitude !== null &&
            (float) $vendor->latitude != 0 && (float) $vendor->longitude != 0 &&
            (float) $address->latitude != 0 && (float) $address->longitude != 0
        ) {
            // Attempt road routing with 2-second timeout, silently falling back to pure math
            $resolved = resolve_order_distance($vendor->latitude, $vendor->longitude, $address->latitude, $address->longitude, true);
            $distance = $resolved['distance_km'];
            $distance_method = $resolved['method'];
        }

        // Graceful fallback if coordinates are missing on either side
        if ($distance === null) {
            if (isset($input_data['distance']) && $input_data['distance'] !== '' && is_numeric($input_data['distance']) && floatval($input_data['distance']) >= 0) {
                $distance = floatval($input_data['distance']);
            } else {
                $distance = 0.00;
            }
            $distance_method = 'pure_math';
        }

        $base_charge = round($distance * 10.00, 2);
        if ($delivery_type === 'urgent') {
            $delivery_charge = round($base_charge + 50.00, 2);
        } else {
            $delivery_charge = $base_charge;
        }

        // Calculate delivery time slot window
        $chosen_time_option = isset($input_data['chosen_time_option']) ? trim(strtolower($input_data['chosen_time_option'])) : 'immediately';
        $allowed_options = ['immediately', 'later', 'lunch', 'dinner', 'custom'];
        if (!in_array($chosen_time_option, $allowed_options, true)) {
            $chosen_time_option = 'immediately';
        }
        $custom_delivery_time = isset($input_data['custom_delivery_time']) && !empty($input_data['custom_delivery_time']) ? trim($input_data['custom_delivery_time']) : null;

        $order_time = time();
        $calc_window = function_exists('calculate_order_delivery_window')
            ? calculate_order_delivery_window($order_time, $vendor, $distance, $chosen_time_option, $custom_delivery_time)
            : ['window_start_dt' => null, 'window_end_dt' => null, 'window_display' => ''];
        $estimated_window_start = $calc_window['window_start_dt'];
        $estimated_window_end = $calc_window['window_end_dt'];

        $subtotal     = $cart_summary['subtotal'];
        $total_gst    = $cart_summary['total_gst'];
        $discount     = 0.00;
        $total_amount = round($subtotal + $total_gst + $delivery_charge - $discount, 2);
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
                'user_id'                => $user_id,
                'address_id'             => $address_id,
                'order_number'           => $order_number,
                'subtotal'               => $subtotal,
                'gst_amount'             => $total_gst,
                'delivery_charge'        => $delivery_charge,
                'delivery_option'        => $delivery_option,
                'distance'               => $distance,
                'distance_km'            => $distance,
                'distance_method'        => $distance_method,
                'delivery_type'          => $delivery_type,
                'chosen_time_option'     => $chosen_time_option,
                'estimated_window_start' => $estimated_window_start,
                'estimated_window_end'   => $estimated_window_end,
                'custom_delivery_time'   => $custom_delivery_time,
                'discount'               => $discount,
                'total_amount'           => $total_amount,
                'total_items'            => $cart_summary['total_quantity'],
                'payment_method'         => 'online',
                'payment_status'         => 'pending',
                'razorpay_order_id'      => $gateway['data']['id'],
                'status'                 => 'pending',
                'notes'                  => $notes,
                'created_at'             => date('Y-m-d H:i:s'),
                'updated_at'             => date('Y-m-d H:i:s'),
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
                        'order_id'                   => $order_id,
                        'order_number'               => $order_number,
                        'amount'                     => $total_amount,
                        'currency'                   => $currency,
                        'key_id'                     => $key_id,
                        'gst_amount'                 => $total_gst,
                        'delivery_charge'            => $delivery_charge,
                        'delivery_option'            => $delivery_option,
                        'distance'                   => $distance,
                        'distance_km'                => $distance,
                        'distance_method'            => $distance_method,
                        'delivery_type'              => $delivery_type,
                        'chosen_time_option'         => $chosen_time_option,
                        'estimated_window_start'     => $estimated_window_start,
                        'estimated_window_end'       => $estimated_window_end,
                        'estimated_window_formatted' => $calc_window['window_display'],
                        'custom_delivery_time'       => $custom_delivery_time,
                        'razorpay_order_id'          => $gateway['data']['id'],
                    ]
                ]));
        }

        /* ---------------- COD ---------------- */
        $this->db->trans_begin();

        $this->db->insert('orders', [
            'user_id'                => $user_id,
            'address_id'             => $address_id,
            'order_number'           => $order_number,
            'subtotal'               => $subtotal,
            'gst_amount'             => $total_gst,
            'delivery_charge'        => $delivery_charge,
            'delivery_option'        => $delivery_option,
            'distance'               => $distance,
            'distance_km'            => $distance,
            'distance_method'        => $distance_method,
            'delivery_type'          => $delivery_type,
            'chosen_time_option'     => $chosen_time_option,
            'estimated_window_start' => $estimated_window_start,
            'estimated_window_end'   => $estimated_window_end,
            'custom_delivery_time'   => $custom_delivery_time,
            'discount'               => $discount,
            'total_amount'           => $total_amount,
            'total_items'            => $cart_summary['total_quantity'],
            'payment_method'         => 'cod',
            'payment_status'         => 'pending',
            'status'                 => 'pending',
            'notes'                  => $notes,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
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
     * Calculate live delivery charge
     */
    public function calculate_delivery_charge()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();

        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $delivery_type = isset($input_data['delivery_type']) ? trim($input_data['delivery_type']) : 'normal'; // 'normal' or 'urgent'
        $address_id = (int) ($input_data['address_id'] ?? 0);

        // Resolve customer address coordinates
        $address = null;
        if ($address_id > 0) {
            $address = $this->db->get_where('user_addresses', [
                'id'      => $address_id,
                'user_id' => $user_id
            ])->row();
        } else {
            $address = $this->db->get_where('user_addresses', [
                'user_id'    => $user_id,
                'is_default' => 1
            ])->row();
            if (!$address) {
                $address = $this->db->get_where('user_addresses', [
                    'user_id' => $user_id
                ])->row();
            }
        }

        // Resolve vendor coordinates from cart items or vendor_id
        $vendor_id = (int) ($input_data['vendor_id'] ?? 0);
        if ($vendor_id <= 0) {
            $cart_summary = $this->get_cart_summary_for_order($user_id);
            if (!empty($cart_summary['items'])) {
                $vendor_id = (int) ($cart_summary['items'][0]['vendor_id'] ?? 0);
            }
        }

        $vendor = null;
        if ($vendor_id > 0) {
            $vendor = $this->db->select('id, latitude, longitude, prep_time_minutes, lunch_window_start, lunch_window_end, dinner_window_start, dinner_window_end, slot_immediately_enabled, slot_later_enabled, slot_lunch_enabled, slot_dinner_enabled, slot_custom_enabled, opening_time, closing_time, is_holiday')->get_where('users', ['id' => $vendor_id])->row();
        }

        $distance = null;
        $distance_method = 'pure_math';

        $this->load->helper('distance');

        if (
            $vendor && $address &&
            $vendor->latitude !== null && $vendor->longitude !== null &&
            $address->latitude !== null && $address->longitude !== null &&
            (float) $vendor->latitude != 0 && (float) $vendor->longitude != 0 &&
            (float) $address->latitude != 0 && (float) $address->longitude != 0
        ) {
            $distance = calculate_distance_km($vendor->latitude, $vendor->longitude, $address->latitude, $address->longitude);
        }

        if ($distance === null) {
            if (isset($input_data['distance']) && $input_data['distance'] !== '' && is_numeric($input_data['distance']) && floatval($input_data['distance']) >= 0) {
                $distance = floatval($input_data['distance']);
            } else {
                $distance = 0.00;
            }
        }

        $base_charge = round($distance * 10.00, 2);
        $extra_charge = ($delivery_type === 'urgent') ? 50.00 : 0.00;
        $delivery_charge = round($base_charge + $extra_charge, 2);

        $slots = function_exists('get_vendor_available_delivery_slots') ? get_vendor_available_delivery_slots($vendor, $distance) : [];

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Delivery charge calculated successfully.',
                'data' => [
                    'distance'              => $distance,
                    'distance_km'           => $distance,
                    'distance_method'       => $distance_method,
                    'delivery_type'         => $delivery_type,
                    'base_delivery_charge'  => $base_charge,
                    'extra_delivery_charge' => $extra_charge,
                    'total_delivery_charge' => $delivery_charge,
                    'slots'                 => $slots
                ]
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
        $status_filter     = trim($this->input->get('status') ?? '');
        $normalized_status = strtolower($status_filter);
        if ($normalized_status === 'new') $normalized_status = 'pending';
        if ($normalized_status === 'accepted') $normalized_status = 'confirmed';
        if ($normalized_status === 'rejected') $normalized_status = 'cancelled';
        if ($normalized_status === 'shipped') $normalized_status = 'out_for_delivery';

        $valid_statuses = ['pending', 'confirmed', 'packed', 'out_for_delivery', 'delivered', 'cancelled'];

        $this->db->where('user_id', $user_id);
        if ($normalized_status !== '' && in_array($normalized_status, $valid_statuses, true)) {
            $this->db->where('status', $normalized_status);
        }
        $total_orders = $this->db->count_all_results('orders');

        $this->db->where('user_id', $user_id);
        if ($normalized_status !== '' && in_array($normalized_status, $valid_statuses, true)) {
            $this->db->where('status', $normalized_status);
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
                'delivery_charge'   => (float) $order->delivery_charge,
                'delivery_option'   => $order->delivery_option ?? null,
                'distance'          => isset($order->distance) ? (float) $order->distance : null,
                'distance_km'       => isset($order->distance_km) ? (float) $order->distance_km : (isset($order->distance) ? (float) $order->distance : null),
                'distance_method'   => $order->distance_method ?? 'pure_math',
                'delivery_type'     => $order->delivery_type ?? 'normal',
                'chosen_time_option'         => $order->chosen_time_option ?? 'immediately',
                'estimated_window_start'     => $order->estimated_window_start ?? null,
                'estimated_window_end'       => $order->estimated_window_end ?? null,
                'estimated_window_formatted' => function_exists('format_slot_window_display') ? format_slot_window_display($order->chosen_time_option ?? 'immediately', $order->estimated_window_start ?? null, $order->estimated_window_end ?? null) : ($order->chosen_time_option ?? 'immediately'),
                'custom_delivery_time'       => $order->custom_delivery_time ?? null,
                'first_item_name'   => $first_item->product_name ?? '',
                'first_item_image'  => $image_url,
                'created_at'        => $order->created_at,
                'invoice_url'       => in_array($order->status, ['out_for_delivery', 'delivered'], true) && !empty($order->invoice_url) ? base_url($order->invoice_url) : (in_array($order->status, ['out_for_delivery', 'delivered'], true) ? base_url('api/user/order_invoice/' . $order->id) : null),
                'can_download_invoice' => in_array($order->status, ['out_for_delivery', 'delivered'], true),
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
            $db_status = strtolower($status_filter);
            if ($db_status === 'new') $db_status = 'pending';
            if ($db_status === 'accepted') $db_status = 'confirmed';
            if ($db_status === 'rejected') $db_status = 'cancelled';
            if ($db_status === 'shipped') $db_status = 'out_for_delivery';
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

            $order_list[] = [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'total_amount' => round($vendor_total, 2),
                'created_at' => $order->created_at,
                'customer_name' => $order->customer_name,
                'customer_mobile' => $order->customer_mobile,
                'address' => $address_str,
                'invoice_url' => !empty($order->invoice_url) ? base_url($order->invoice_url) : base_url('api/user/order_invoice/' . $order->id),
                'can_download_invoice' => in_array($order->status, ['out_for_delivery', 'delivered'], true),
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

        // Map status aliases to canonical values
        $db_status = strtolower($status);
        if ($db_status === 'new') $db_status = 'pending';
        if ($db_status === 'accepted') $db_status = 'confirmed';
        if ($db_status === 'rejected') $db_status = 'cancelled';
        if ($db_status === 'shipped') $db_status = 'out_for_delivery';

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

        $update_data = [
            'status' => $db_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $invoice_rel = null;
        if (in_array($db_status, ['out_for_delivery', 'delivered'], true)) {
            $invoice_rel = $this->generate_order_invoice_file($order_id, $vendor_id);
            if ($invoice_rel) {
                $update_data['invoice_url'] = $invoice_rel;
            }
        }

        $this->db->where('id', $order_id)->update('orders', $update_data);

        // Log status history
        $this->insert_status_history($order_id, $db_status, $remarks ?: 'Updated by vendor', 'vendor');

        $invoice_url = $invoice_rel ? base_url($invoice_rel) : base_url('api/user/order_invoice/' . $order_id);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Order status updated successfully',
                'data' => [
                    'order_id' => $order_id,
                    'status' => $db_status,
                    'invoice_url' => $invoice_url
                ]
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

        $search = trim($this->input->get('search', true) ?? '');
        $category_id = trim($this->input->get('category_id', true) ?? '');

        // 1. Fetch Categories
        $categories = $this->db->where('is_active', 1)->get('categories')->result();
        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;
        }

        // 2. Location is strictly required for product discovery
        if (!$this->resolve_customer_location($cust_lat, $cust_lon)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Customer location (latitude and longitude) is required for product discovery',
                    'data' => null
                ]));
        }

        $discovery = $this->get_nearby_grouped_products($cust_lat, $cust_lon, [
            'search' => $search,
            'category_id' => $category_id,
            'limit' => 20
        ]);

        if (!empty($discovery['error'])) {
            return $this->output
                ->set_status_header($discovery['code'] ?? 422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => $discovery['code'] ?? 422,
                    'message' => $discovery['message'] ?? 'Failed to discover nearby products',
                    'data' => null
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Home data fetched successfully',
                'data' => [
                    'categories' => $categories,
                    'products' => $discovery['products'],
                    'delivery_radius_km' => $discovery['delivery_radius_km']
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
    /**
     * Get active products by category ID with nearby discovery & ranking
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

        if (!$this->resolve_customer_location($cust_lat, $cust_lon)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Customer location (latitude and longitude) is required for product discovery',
                    'data' => null
                ]));
        }

        $discovery = $this->get_nearby_grouped_products($cust_lat, $cust_lon, [
            'category_id' => $category_id,
            'limit' => 100
        ]);

        if (!empty($discovery['error'])) {
            return $this->output
                ->set_status_header($discovery['code'] ?? 422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => $discovery['code'] ?? 422,
                    'message' => $discovery['message'] ?? 'Failed to discover nearby products',
                    'data' => null
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Products fetched successfully',
                'data' => $discovery['products'],
                'delivery_radius_km' => $discovery['delivery_radius_km']
            ]));
    }

    /**
     * Get list of all active products with nearby discovery & ranking
     */
    public function get_product_list()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if (!$this->resolve_customer_location($cust_lat, $cust_lon)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Customer location (latitude and longitude) is required for product discovery',
                    'data' => null
                ]));
        }

        $search = trim($this->input->get('search', true) ?? '');
        $category_id = $this->input->get('category_id', true);
        $brand = $this->input->get('brand', true);
        $sort_by = trim($this->input->get('sort_by', true) ?? 'relevance');
        $page = max(1, (int)($this->input->get('page', true) ?? 1));
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? 50)));

        $discovery = $this->get_nearby_grouped_products($cust_lat, $cust_lon, [
            'search' => $search,
            'category_id' => $category_id,
            'brand' => $brand,
            'sort_by' => $sort_by,
            'page' => $page,
            'limit' => $limit
        ]);

        if (!empty($discovery['error'])) {
            return $this->output
                ->set_status_header($discovery['code'] ?? 422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => $discovery['code'] ?? 422,
                    'message' => $discovery['message'] ?? 'Failed to discover nearby products',
                    'data' => null
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Products fetched successfully',
                'data' => $discovery['products'],
                'pagination' => [
                    'total_records' => $discovery['total_records'],
                    'current_page' => $discovery['page'],
                    'limit' => $discovery['limit'],
                    'total_pages' => $discovery['total_pages']
                ],
                'filters' => $discovery['filters'],
                'delivery_radius_km' => $discovery['delivery_radius_km']
            ]));
    }

    /**
     * Get details of a single active product with nearby distance and alternative sellers
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

        // Check customer location for distance & nearby alternatives
        $this->load->helper('distance');
        $delivery_radius_km = get_delivery_radius_km();
        $product->delivery_radius_km = $delivery_radius_km;
        $product->distance_km = null;
        $product->in_delivery_radius = true;
        $product->alternatives = [];

        if ($this->resolve_customer_location($cust_lat, $cust_lon)) {
            if ($product->vendor_latitude !== null && $product->vendor_longitude !== null) {
                $dist = calculate_distance_km($cust_lat, $cust_lon, $product->vendor_latitude, $product->vendor_longitude);
                $product->distance_km = $dist;
                $product->in_delivery_radius = ($dist <= $delivery_radius_km);
            }

            // Find alternative vendors selling the same variant/product
            $this->get_vendor_products_query();
            $this->db->where('vp.is_active', 1);
            $this->db->where('vp.stock >', 0);
            $this->db->where('vp.id !=', $product->id);
            $this->db->where('u.latitude IS NOT NULL', null, false);
            $this->db->where('u.longitude IS NOT NULL', null, false);
            $this->db->where('u.role', 'vendor');

            if (!empty($product->variant_id)) {
                $this->db->where('vp.variant_id', $product->variant_id);
            } elseif (!empty($product->product_id)) {
                $this->db->where('vp.product_id', $product->product_id);
            } else {
                $this->db->where('LOWER(TRIM(vp.product_name))', strtolower(trim($product->name)));
            }

            $alt_rows = $this->db->get()->result();
            $qualifying_alts = [];
            foreach ($alt_rows as $alt) {
                $alt_dist = calculate_distance_km($cust_lat, $cust_lon, $alt->vendor_latitude, $alt->vendor_longitude);
                if ($alt_dist <= $delivery_radius_km) {
                    $qualifying_alts[] = [
                        'vendor_product_id' => (int)$alt->id,
                        'vendor_id'         => (int)$alt->vendor_id,
                        'vendor_name'       => $alt->vendor_name,
                        'store_name'        => $alt->store_name,
                        'store_address'     => $alt->store_address,
                        'price'             => (float)$alt->price,
                        'sale_price'        => (float)$alt->sale_price,
                        'stock'             => (int)$alt->stock,
                        'distance_km'       => (float)$alt_dist,
                    ];
                }
            }

            // Rank alternatives: price ASC, distance ASC, stock DESC
            usort($qualifying_alts, function ($a, $b) {
                if (abs($a['sale_price'] - $b['sale_price']) > 0.001) {
                    return ($a['sale_price'] < $b['sale_price']) ? -1 : 1;
                }
                if (abs($a['distance_km'] - $b['distance_km']) > 0.01) {
                    return ($a['distance_km'] < $b['distance_km']) ? -1 : 1;
                }
                return ($a['stock'] > $b['stock']) ? -1 : 1;
            });

            $product->alternatives = $qualifying_alts;
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
     * Search products with nearby proximity, stock filter, price ranking and alternative sellers
     */
    public function search_products()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if (!$this->resolve_customer_location($cust_lat, $cust_lon)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Customer location (latitude and longitude) is required for product discovery',
                    'data' => null
                ]));
        }

        $search = trim($this->input->get('search', true) ?? $this->input->get('q', true) ?? $this->input->get('query', true) ?? '');
        $category_param = $this->input->get('category_id', true);
        $brand_param = $this->input->get('brand', true);
        $min_price = $this->input->get('min_price', true);
        $max_price = $this->input->get('max_price', true);
        $vendor_id = $this->input->get('vendor_id', true);
        $sort_by = trim($this->input->get('sort_by', true) ?? 'relevance');
        $page = max(1, (int)($this->input->get('page', true) ?? 1));
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? 10)));

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

        $discovery = $this->get_nearby_grouped_products($cust_lat, $cust_lon, [
            'search'       => $search,
            'category_ids' => $category_ids,
            'brands'       => $brands,
            'min_price'    => $min_price,
            'max_price'    => $max_price,
            'vendor_id'    => $vendor_id,
            'sort_by'      => $sort_by,
            'page'         => $page,
            'limit'        => $limit
        ]);

        if (!empty($discovery['error'])) {
            return $this->output
                ->set_status_header($discovery['code'] ?? 422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => $discovery['code'] ?? 422,
                    'message' => $discovery['message'] ?? 'Failed to discover nearby products',
                    'data' => null
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Products searched successfully',
                'data' => [
                    'products' => $discovery['products'],
                    'pagination' => [
                        'total_records' => (int)$discovery['total_records'],
                        'current_page' => $discovery['page'],
                        'limit' => $discovery['limit'],
                        'total_pages' => $discovery['total_pages']
                    ],
                    'filters' => $discovery['filters'],
                    'delivery_radius_km' => $discovery['delivery_radius_km']
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
        $this->db->select('vp.id, vp.vendor_id, vp.product_id, vp.variant_id, vp.product_name as name, vp.brand, vp.category_id, vp.unit, vp.mrp as price, vp.selling_price as sale_price, vp.stock, vp.description, COALESCE(vp.image, p.image) as image, vp.is_active, c.name as category_name, u.name as vendor_name, u.store_name, u.address as store_address, u.contact_number as store_contact, u.opening_time as store_opening_time, u.closing_time as store_closing_time, u.store_photo, u.latitude as vendor_latitude, u.longitude as vendor_longitude');
        $this->db->from('vendor_products vp');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->join('categories c', 'c.id = vp.category_id', 'left');
        $this->db->join('users u', 'u.id = vp.vendor_id', 'left');
    }

    /**
     * Resolve customer location from query params (latitude/lat, longitude/lon/lng),
     * or fallback to authenticated customer saved default address coordinates.
     */
    private function resolve_customer_location(&$lat, &$lon)
    {
        $lat = $this->input->get('latitude', true) ?? $this->input->get('lat', true);
        $lon = $this->input->get('longitude', true) ?? $this->input->get('lon', true) ?? $this->input->get('lng', true);

        // Fallback: check JWT token and saved address
        if (($lat === null || $lon === null) || !is_numeric($lat) || !is_numeric($lon)) {
            $authHeader = $this->input->get_request_header('Authorization', TRUE);
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $decoded = $this->verify_jwt($matches[1]);
                if ($decoded && !empty($decoded->data->id)) {
                    $userId = (int)$decoded->data->id;
                    $addr = $this->db->where('user_id', $userId)
                                     ->where('latitude IS NOT NULL', null, false)
                                     ->where('longitude IS NOT NULL', null, false)
                                     ->order_by('is_default', 'DESC')
                                     ->order_by('id', 'DESC')
                                     ->get('user_addresses')
                                     ->row();
                    if ($addr && is_numeric($addr->latitude) && is_numeric($addr->longitude)) {
                        $lat = (float)$addr->latitude;
                        $lon = (float)$addr->longitude;
                    }
                }
            }
        }

        if ($lat !== null && $lon !== null && is_numeric($lat) && is_numeric($lon)) {
            $lat = (float)$lat;
            $lon = (float)$lon;
            return true;
        }

        return false;
    }

    /**
     * STEP 5: Nearby Product Discovery Engine (proximity + stock + price ranking)
     * - Requires customer location (lat/lon)
     * - Filters out zero stock
     * - Filters by admin-configurable delivery radius using pure-math Haversine
     * - Groups identical products across multiple vendors (by variant_id, product_id, or name)
     * - Ranks within each group: cheapest sale_price ASC, breaks ties with distance_km ASC, then stock DESC
     * - Designates winner as main product listing, and attaches qualifying alternatives
     */
    private function get_nearby_grouped_products($cust_lat, $cust_lon, array $options = [])
    {
        if ($cust_lat === null || $cust_lon === null || !is_numeric($cust_lat) || !is_numeric($cust_lon)) {
            return [
                'error' => true,
                'code' => 422,
                'message' => 'Customer location (latitude and longitude) is required for product discovery'
            ];
        }

        $cust_lat = (float)$cust_lat;
        $cust_lon = (float)$cust_lon;

        $this->load->helper('distance');
        $delivery_radius_km = get_delivery_radius_km();

        // Query candidate vendor products
        $this->get_vendor_products_query();
        $this->db->where('vp.is_active', 1);
        $this->db->where('vp.stock >', 0); // Exclude zero stock
        $this->db->where('u.latitude IS NOT NULL', null, false);
        $this->db->where('u.longitude IS NOT NULL', null, false);
        $this->db->where('u.role', 'vendor');

        // Filter: Category
        if (!empty($options['category_ids'])) {
            $this->db->where_in('vp.category_id', $options['category_ids']);
        } elseif (!empty($options['category_id'])) {
            $this->db->where('vp.category_id', (int)$options['category_id']);
        }

        // Filter: Brand
        if (!empty($options['brands'])) {
            $this->db->where_in('vp.brand', $options['brands']);
        } elseif (!empty($options['brand'])) {
            $this->db->where('vp.brand', $options['brand']);
        }

        // Filter: Vendor ID
        if (!empty($options['vendor_id'])) {
            $this->db->where('vp.vendor_id', (int)$options['vendor_id']);
        }

        // Filter: Search query
        if (!empty($options['search'])) {
            $keywords = array_filter(explode(' ', $options['search']));
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

        $rows = $this->db->get()->result();

        // Pure-math distance calculation & delivery radius filtering
        $qualifying_rows = [];
        $facet_categories = [];
        $facet_brands = [];
        $facet_min_price = null;
        $facet_max_price = null;

        foreach ($rows as $row) {
            $dist = calculate_distance_km($cust_lat, $cust_lon, $row->vendor_latitude, $row->vendor_longitude);
            // Must be within configurable delivery radius
            if ($dist <= $delivery_radius_km) {
                $row->distance_km = $dist;
                $row->image_url = !empty($row->image) ? base_url($row->image) : null;
                $row->store_photo_url = !empty($row->store_photo) ? base_url($row->store_photo) : null;
                $row->gallery_urls = [];
                $qualifying_rows[] = $row;

                // Facets from qualifying products
                if (!empty($row->category_id)) {
                    $cid = (int)$row->category_id;
                    if (!isset($facet_categories[$cid])) {
                        $facet_categories[$cid] = [
                            'id' => $cid,
                            'name' => $row->category_name ?? 'Uncategorized',
                            'count' => 0
                        ];
                    }
                    $facet_categories[$cid]['count']++;
                }

                if (!empty($row->brand)) {
                    $bname = trim($row->brand);
                    if (!isset($facet_brands[$bname])) {
                        $facet_brands[$bname] = [
                            'name' => $bname,
                            'count' => 0
                        ];
                    }
                    $facet_brands[$bname]['count']++;
                }

                $p = (float)$row->sale_price;
                if ($facet_min_price === null || $p < $facet_min_price) $facet_min_price = $p;
                if ($facet_max_price === null || $p > $facet_max_price) $facet_max_price = $p;
            }
        }

        // Group same product across multiple vendors:
        // Priority: variant_id > product_id > normalized product name
        $groups = [];
        foreach ($qualifying_rows as $row) {
            if (!empty($row->variant_id)) {
                $group_key = 'var_' . $row->variant_id;
            } elseif (!empty($row->product_id)) {
                $group_key = 'prod_' . $row->product_id;
            } else {
                $clean_name = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $row->name)));
                $group_key = 'name_' . $clean_name;
            }
            $groups[$group_key][] = $row;
        }

        // Rank offers within each group:
        // 1st: sale_price ASC (cheapest first)
        // 2nd: distance_km ASC (closer breaks tie)
        // 3rd: stock DESC (higher stock breaks second tie)
        $ranked_products = [];
        foreach ($groups as $group_key => $group_items) {
            usort($group_items, function ($a, $b) {
                $priceA = (float)$a->sale_price;
                $priceB = (float)$b->sale_price;
                if (abs($priceA - $priceB) > 0.001) {
                    return ($priceA < $priceB) ? -1 : 1;
                }

                $distA = (float)$a->distance_km;
                $distB = (float)$b->distance_km;
                if (abs($distA - $distB) > 0.01) {
                    return ($distA < $distB) ? -1 : 1;
                }

                $stockA = (int)$a->stock;
                $stockB = (int)$b->stock;
                return ($stockA > $stockB) ? -1 : 1;
            });

            // Winning offer is the #1 option
            $winner = $group_items[0];

            // Other qualifying vendors become alternatives
            $alternatives = [];
            for ($i = 1; $i < count($group_items); $i++) {
                $alt = $group_items[$i];
                $alternatives[] = [
                    'vendor_product_id' => (int)$alt->id,
                    'vendor_id'         => (int)$alt->vendor_id,
                    'vendor_name'       => $alt->vendor_name,
                    'store_name'        => $alt->store_name,
                    'store_address'     => $alt->store_address,
                    'price'             => (float)$alt->price,
                    'sale_price'        => (float)$alt->sale_price,
                    'stock'             => (int)$alt->stock,
                    'distance_km'       => (float)$alt->distance_km,
                ];
            }

            $winner->alternatives = $alternatives;
            $winner->total_vendors_count = count($group_items);
            $winner->delivery_radius_km = $delivery_radius_km;

            // Price range filter on winning offer
            if (isset($options['min_price']) && is_numeric($options['min_price'])) {
                if ((float)$winner->sale_price < (float)$options['min_price']) continue;
            }
            if (isset($options['max_price']) && is_numeric($options['max_price'])) {
                if ((float)$winner->sale_price > (float)$options['max_price']) continue;
            }

            $ranked_products[] = $winner;
        }

        // Group-level sorting
        $sort_by = $options['sort_by'] ?? 'relevance';
        switch ($sort_by) {
            case 'price_low_to_high':
                usort($ranked_products, function ($a, $b) {
                    return ((float)$a->sale_price < (float)$b->sale_price) ? -1 : 1;
                });
                break;
            case 'price_high_to_low':
                usort($ranked_products, function ($a, $b) {
                    return ((float)$a->sale_price > (float)$b->sale_price) ? -1 : 1;
                });
                break;
            case 'distance_asc':
            case 'nearest':
                usort($ranked_products, function ($a, $b) {
                    return ((float)$a->distance_km < (float)$b->distance_km) ? -1 : 1;
                });
                break;
            case 'name_asc':
                usort($ranked_products, function ($a, $b) {
                    return strcasecmp($a->name, $b->name);
                });
                break;
            case 'name_desc':
                usort($ranked_products, function ($a, $b) {
                    return strcasecmp($b->name, $a->name);
                });
                break;
            case 'newest':
            case 'relevance':
            default:
                usort($ranked_products, function ($a, $b) {
                    if (abs((float)$a->distance_km - (float)$b->distance_km) > 1.5) {
                        return ((float)$a->distance_km < (float)$b->distance_km) ? -1 : 1;
                    }
                    return ((float)$a->sale_price < (float)$b->sale_price) ? -1 : 1;
                });
                break;
        }

        // Pagination
        $total_records = count($ranked_products);
        $page = max(1, (int)($options['page'] ?? 1));
        $limit = max(1, min(100, (int)($options['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $paginated_products = array_slice($ranked_products, $offset, $limit);
        $total_pages = ceil($total_records / $limit);

        return [
            'error' => false,
            'products' => $paginated_products,
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'page' => $page,
            'limit' => $limit,
            'delivery_radius_km' => $delivery_radius_km,
            'filters' => [
                'categories' => array_values($facet_categories),
                'brands' => array_values($facet_brands),
                'price_range' => [
                    'min' => $facet_min_price !== null ? (float)$facet_min_price : 0,
                    'max' => $facet_max_price !== null ? (float)$facet_max_price : 0
                ]
            ]
        ];
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
            'delivery_option'    => $order['delivery_option'] ?? null,
            'distance'           => isset($order['distance']) ? (float) $order['distance'] : null,
            'distance_km'        => isset($order['distance_km']) ? (float) $order['distance_km'] : (isset($order['distance']) ? (float) $order['distance'] : null),
            'distance_method'    => $order['distance_method'] ?? 'pure_math',
            'delivery_type'      => $order['delivery_type'] ?? 'normal',
            'chosen_time_option'         => $order['chosen_time_option'] ?? 'immediately',
            'estimated_window_start'     => $order['estimated_window_start'] ?? null,
            'estimated_window_end'       => $order['estimated_window_end'] ?? null,
            'estimated_window_formatted' => function_exists('format_slot_window_display') ? format_slot_window_display($order['chosen_time_option'] ?? 'immediately', $order['estimated_window_start'] ?? null, $order['estimated_window_end'] ?? null) : ($order['chosen_time_option'] ?? 'immediately'),
            'custom_delivery_time'       => $order['custom_delivery_time'] ?? null,
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
            'invoice_url'        => in_array($order['status'], ['out_for_delivery', 'delivered'], true) && !empty($order['invoice_url']) ? base_url($order['invoice_url']) : (in_array($order['status'], ['out_for_delivery', 'delivered'], true) ? base_url('api/user/order_invoice/' . $order['id']) : null),
            'can_download_invoice' => in_array($order['status'], ['out_for_delivery', 'delivered'], true),
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
                    `latitude` DECIMAL(10,8) DEFAULT NULL,
                    `longitude` DECIMAL(11,8) DEFAULT NULL,
                    `country` VARCHAR(100) DEFAULT 'India',
                    `is_default` TINYINT(1) DEFAULT 0,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->db->field_exists('latitude', 'user_addresses')) {
            $this->db->query("ALTER TABLE `user_addresses` ADD COLUMN `latitude` DECIMAL(10,8) DEFAULT NULL AFTER `pincode`");
        }
        if (!$this->db->field_exists('longitude', 'user_addresses')) {
            $this->db->query("ALTER TABLE `user_addresses` ADD COLUMN `longitude` DECIMAL(11,8) DEFAULT NULL AFTER `latitude`");
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
            'latitude'      => isset($address->latitude) && $address->latitude !== null ? (float)$address->latitude : null,
            'longitude'     => isset($address->longitude) && $address->longitude !== null ? (float)$address->longitude : null,
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
        if (!$this->db->field_exists('delivery_option', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `delivery_option` VARCHAR(50) DEFAULT 'self'");
        }
        if (!$this->db->field_exists('distance', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `distance` DECIMAL(10,2) DEFAULT NULL");
        }
        if (!$this->db->field_exists('distance_km', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `distance_km` DECIMAL(10,2) DEFAULT NULL");
        }
        if (!$this->db->field_exists('distance_method', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `distance_method` VARCHAR(50) DEFAULT 'pure_math'");
        }
        if (!$this->db->field_exists('delivery_type', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `delivery_type` VARCHAR(50) DEFAULT 'normal'");
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

    public function order_invoice($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id <= 0) {
            show_404();
            return;
        }

        $dir = FCPATH . 'assets/uploads/invoices/';
        $filepath = $dir . 'invoice_' . $order_id . '.pdf';

        if (!file_exists($filepath)) {
            $this->generate_order_invoice_file($order_id);
        }

        if (!file_exists($filepath)) {
            show_404();
            return;
        }

        $order = $this->db->get_where('orders', ['id' => $order_id])->row();
        $order_number = $order ? ($order->order_number ?: ('ORD-' . $order->id)) : ('ORD-' . $order_id);

        $disposition = $this->input->get('download') ? 'attachment' : 'inline';
        $filesize = filesize($filepath);

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="Invoice_' . $order_number . '.pdf"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filepath);
        exit;
    }

    public function generate_order_invoice_file($order_id, $vendor_id = null)
    {
        $dir = FCPATH . 'assets/uploads/invoices/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $pdf_content = $this->generate_order_invoice_pdf($order_id, $vendor_id);
        if (!$pdf_content) {
            return false;
        }

        $filename = 'invoice_' . $order_id . '.pdf';
        $filepath = $dir . $filename;
        @file_put_contents($filepath, $pdf_content);
        return 'assets/uploads/invoices/' . $filename;
    }

    public function generate_order_invoice_pdf($order_id, $vendor_id = null)
    {
        $html = $this->generate_order_invoice_html($order_id, $vendor_id);
        if (!$html) {
            return null;
        }

        try {
            if (!class_exists('Dompdf\Dompdf')) {
                $autoload = FCPATH . 'vendor/autoload.php';
                if (file_exists($autoload)) {
                    require_once $autoload;
                }
            }

            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        } catch (\Exception $e) {
            log_message('error', 'Dompdf generation error: ' . $e->getMessage());
            return null;
        }
    }

    public function generate_order_invoice_html($order_id, $vendor_id = null)
    {
        $order = $this->db->get_where('orders', ['id' => $order_id])->row();
        if (!$order) {
            return null;
        }

        $customer = $this->db->get_where('users', ['id' => $order->user_id])->row();
        $address = $this->db->get_where('user_addresses', ['id' => $order->address_id])->row();

        $this->db->where('order_id', $order_id);
        if ($vendor_id) {
            $this->db->where('vendor_id', $vendor_id);
        }
        $items = $this->db->get('order_items')->result();
        if (empty($items)) {
            $items = $this->db->where('order_id', $order_id)->get('order_items')->result();
        }
        if (empty($items)) {
            return null;
        }

        $actual_vendor_id = $vendor_id ?: ($items[0]->vendor_id ?? 0);
        $vendor = $this->db->get_where('users', ['id' => $actual_vendor_id])->row();

        $subtotal = 0.00;
        $items_rows = '';
        $idx = 1;
        foreach ($items as $item) {
            $line_total = ($item->price * $item->quantity);
            $subtotal += $line_total;
            $p_name = htmlspecialchars($item->product_name);
            $p_qty = (int)$item->quantity;
            $p_price = number_format((float)$item->price, 2);
            $p_line = number_format((float)$line_total, 2);
            $bg = ($idx % 2 === 0) ? '#fbfcfe' : '#ffffff';

            $items_rows .= "
                <tr style='background-color: {$bg};'>
                    <td style='padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: center; color: #64748b;'>{$idx}</td>
                    <td style='padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #00204E;'>{$p_name}</td>
                    <td style='padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: center; color: #334155;'>{$p_qty}</td>
                    <td style='padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: right; color: #334155;'>&#8377; {$p_price}</td>
                    <td style='padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: right; font-weight: bold; color: #34A129;'>&#8377; {$p_line}</td>
                </tr>
            ";
            $idx++;
        }

        $is_urgent = (isset($order->delivery_type) && strtolower($order->delivery_type) === 'urgent');
        $urgent_charge = $is_urgent ? 50.00 : 0.00;
        $delivery_charge = floatval($order->delivery_charge ?? 0.00);
        $base_delivery_charge = ($is_urgent && $delivery_charge >= 50) ? ($delivery_charge - 50.00) : $delivery_charge;
        $grand_total = floatval($order->total_amount ?? ($subtotal + $delivery_charge));

        $order_number = htmlspecialchars($order->order_number ?? ('ORD-' . $order->id));
        $date_str = date('d M Y, h:i A', strtotime($order->created_at ?: ($order->created_on ?: 'now')));

        $vendor_name = htmlspecialchars($vendor->store_name ?? ($vendor->name ?? 'Recomm Vendor Store'));
        $vendor_owner = htmlspecialchars($vendor->owner_name ?? ($vendor->name ?? 'Store Owner'));
        $vendor_phone = htmlspecialchars($vendor->mobile ?? ($vendor->contact_number ?? ''));
        $vendor_addr = htmlspecialchars($vendor->address ?? 'Store Address');
        $vendor_pin = htmlspecialchars($vendor->pincode ?? '');

        $cust_name = htmlspecialchars($address->full_name ?? ($customer->name ?? 'Valued Customer'));
        $cust_phone = htmlspecialchars($address->mobile ?? ($customer->mobile ?? ''));
        $cust_addr = $address ? htmlspecialchars("{$address->address_line1}, {$address->city} - {$address->pincode}") : 'Customer Delivery Address';

        $pay_method = strtoupper(htmlspecialchars($order->payment_method ?? 'COD'));
        $pay_status = strtoupper(htmlspecialchars($order->payment_status ?? 'PENDING'));
        $del_mode = ($order->delivery_option === 'self') ? 'By Self' : 'Delivery Partner';
        $distance_km = $order->distance ? floatval($order->distance) . ' KM' : '';

        $urgent_badge = $is_urgent
            ? '<span style="background-color:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:3px 8px; border-radius:4px; font-weight:bold; font-size:10px;">⚡ URGENT DELIVERY</span>'
            : '<span style="background-color:#f0fdf4; color:#166534; border:1px solid #bbf7d0; padding:3px 8px; border-radius:4px; font-weight:bold; font-size:10px;">STANDARD DELIVERY</span>';

        $urgent_row = $is_urgent ? "
            <tr>
                <td style='padding: 5px 6px; color: #dc2626; font-weight: bold;'>⚡ Urgent Delivery Surcharge:</td>
                <td style='padding: 5px 6px; text-align: right; color: #dc2626; font-weight: bold;'>+ &#8377; " . number_format($urgent_charge, 2) . "</td>
            </tr>" : "";

        $base_delivery_row = $base_delivery_charge > 0 ? "
            <tr>
                <td style='padding: 5px 6px; color: #64748b;'>Delivery Charge" . ($distance_km ? " ({$distance_km})" : "") . ":</td>
                <td style='padding: 5px 6px; text-align: right; color: #334155; font-weight: bold;'>+ &#8377; " . number_format($base_delivery_charge, 2) . "</td>
            </tr>" : "";

        $subtotal_fmt = number_format($subtotal, 2);
        $total_fmt = number_format($grand_total, 2);

        $chosen_slot_opt = $order->chosen_time_option ?? 'immediately';
        $slot_label = ucfirst($chosen_slot_opt);
        if ($chosen_slot_opt === 'later') $slot_label = 'Later (3–4 hrs)';
        if ($chosen_slot_opt === 'immediately') $slot_label = 'Immediately';
        if ($chosen_slot_opt === 'lunch') $slot_label = 'Lunch Window';
        if ($chosen_slot_opt === 'dinner') $slot_label = 'Dinner Window';
        if ($chosen_slot_opt === 'custom') $slot_label = 'Custom Scheduled';

        $window_display = '';
        if (!empty($order->estimated_window_start) && !empty($order->estimated_window_end)) {
            $window_display = function_exists('format_slot_window_display')
                ? format_slot_window_display($order->estimated_window_start, $order->estimated_window_end, strtotime($order->created_at ?: 'now'))
                : (date('d M, h:i A', strtotime($order->estimated_window_start)) . ' – ' . date('h:i A', strtotime($order->estimated_window_end)));
        }

        return '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice - ' . $order_number . '</title>
<style>
    @page {
        margin: 12mm 15mm;
        size: A4 portrait;
    }
    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 11px;
        color: #1e293b;
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }
    .top-bar {
        height: 6px;
        background-color: #00204E;
        margin-bottom: 16px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-table td {
        vertical-align: top;
    }
    .brand-title {
        font-size: 24px;
        font-weight: bold;
        color: #00204E;
        letter-spacing: -0.5px;
    }
    .brand-sub {
        font-size: 10px;
        font-weight: bold;
        color: #34A129;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
    }
    .invoice-title {
        font-size: 20px;
        font-weight: bold;
        color: #00204E;
        text-align: right;
    }
    .invoice-num {
        font-size: 13px;
        font-weight: bold;
        color: #34A129;
        text-align: right;
        margin-top: 2px;
    }
    .invoice-date {
        font-size: 11px;
        color: #64748b;
        text-align: right;
        margin-top: 3px;
    }
    .section-divider {
        border-bottom: 1.5px solid #e2e8f0;
        margin: 14px 0;
    }
    .parties-table {
        margin-bottom: 14px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .parties-table td {
        width: 50%;
        padding: 12px 14px;
        vertical-align: top;
    }
    .party-heading {
        font-size: 10px;
        font-weight: bold;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .party-name {
        font-size: 13px;
        font-weight: bold;
        color: #00204E;
        margin-bottom: 3px;
    }
    .party-info {
        font-size: 11px;
        color: #475569;
        line-height: 1.4;
    }
    .meta-table {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }
    .meta-table td {
        padding: 8px 12px;
        font-size: 11px;
    }
    .items-table {
        margin-bottom: 16px;
    }
    .items-table th {
        background-color: #00204E;
        color: #ffffff;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 10px;
        text-align: left;
    }
    .totals-table {
        width: 320px;
        margin-left: auto;
    }
    .totals-table td {
        padding: 4px 6px;
        font-size: 11px;
    }
    .grand-total-row td {
        border-top: 2px solid #00204E;
        border-bottom: 2px solid #00204E;
        font-size: 14px;
        font-weight: bold;
        color: #00204E;
        padding: 8px 6px;
    }
    .footer {
        margin-top: 26px;
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
        text-align: center;
        font-size: 10px;
        color: #64748b;
    }
</style>
</head>
<body>
    <div class="top-bar"></div>

    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="brand-title">RECOMM</div>
                <div class="brand-sub">Smart Store & Quick Delivery</div>
            </td>
            <td style="width: 45%;">
                <div class="invoice-title">TAX INVOICE</div>
                <div class="invoice-num">#' . $order_number . '</div>
                <div class="invoice-date">Date: ' . $date_str . '</div>
            </td>
        </tr>
    </table>

    <div class="section-divider"></div>

    <table class="parties-table">
        <tr>
            <td>
                <div class="party-heading">Sold By (Vendor)</div>
                <div class="party-name">' . $vendor_name . '</div>'
                . ($vendor_owner ? '<div class="party-info">Owner: ' . $vendor_owner . '</div>' : '')
                . ($vendor_phone ? '<div class="party-info">Mobile: ' . $vendor_phone . '</div>' : '')
                . '<div class="party-info">' . $vendor_addr . ($vendor_pin ? ' - ' . $vendor_pin : '') . '</div>
            </td>
            <td style="border-left: 1px solid #e2e8f0;">
                <div class="party-heading">Billed & Delivered To</div>
                <div class="party-name">' . $cust_name . '</div>'
                . ($cust_phone ? '<div class="party-info">Mobile: ' . $cust_phone . '</div>' : '')
                . '<div class="party-info">' . $cust_addr . '</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td><strong>Payment:</strong> ' . $pay_method . ' (' . $pay_status . ')</td>
            <td><strong>Mode:</strong> ' . $del_mode . ($distance_km ? ' (' . $distance_km . ')' : '') . '</td>
            <td><strong>Type:</strong> ' . $urgent_badge . '</td>
        </tr>
        <tr>
            <td colspan="3" style="border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                <strong>Delivery Time Slot:</strong> <span style="color: #00204E; font-weight: bold;">' . htmlspecialchars($slot_label) . '</span>'
                . ($window_display ? ' &nbsp;|&nbsp; <strong>Est. Delivery Window:</strong> <span style="color: #34A129; font-weight: bold;">' . htmlspecialchars($window_display) . '</span>' : '')
            . '</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">#</th>
                <th style="width: 48%;">Item Description</th>
                <th style="width: 12%; text-align: center;">Qty</th>
                <th style="width: 16%; text-align: right;">Price</th>
                <th style="width: 16%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            ' . $items_rows . '
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="color: #64748b; padding: 5px 6px;">Items Subtotal:</td>
            <td style="text-align: right; font-weight: bold; padding: 5px 6px;">&#8377; ' . $subtotal_fmt . '</td>
        </tr>
        ' . $base_delivery_row . '
        ' . $urgent_row . '
        <tr>
            <td style="color: #64748b; padding: 5px 6px; font-size: 10px;">Time Slot & Window:</td>
            <td style="text-align: right; color: #00204E; font-weight: bold; padding: 5px 6px; font-size: 10px;">' . htmlspecialchars($slot_label) . ($window_display ? '<br><span style="color: #34A129; font-size: 9px; font-weight: normal;">' . htmlspecialchars($window_display) . '</span>' : '') . '</td>
        </tr>
        <tr class="grand-total-row">
            <td>Grand Total:</td>
            <td style="text-align: right; color: #34A129;">&#8377; ' . $total_fmt . '</td>
        </tr>
    </table>

    <div class="footer">
        <p style="font-weight: bold; color: #00204E; margin: 0 0 4px;">Thank you for shopping with Recomm!</p>
        <p style="margin: 0;">This is a computer-generated tax invoice and does not require a physical signature.</p>
    </div>
</body>
</html>';
    }

    /**
     * Reusable pure-math distance calculation endpoint
     */
    public function calculate_distance()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $lat1 = $input['lat1'] ?? null;
        $lon1 = $input['lon1'] ?? null;
        $lat2 = $input['lat2'] ?? null;
        $lon2 = $input['lon2'] ?? null;

        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'lat1, lon1, lat2, and lon2 are required',
                'data' => null
            ]));
        }

        $distance_km = calculate_distance_km($lat1, $lon1, $lat2, $lon2);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Distance calculated successfully',
            'data' => [
                'distance_km' => $distance_km
            ]
        ]));
    }
}