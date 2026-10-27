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

        date_default_timezone_set('Asia/Kolkata');

        // Load models and libraries
        $this->load->model('General_model');
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form']);

        // DB migrations are already fully executed and persisted in MySQL schema
        // Running 50+ DDL and ALTER TABLE queries on every single HTTP request caused severe 1000ms+ lag


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

        $this->db->select('cart_items.id as cart_item_id, cart_items.quantity, cart_items.created_at as added_at, vp.id as product_id, vp.product_name as product_name, vp.mrp as price, vp.selling_price as sale_price, COALESCE(NULLIF(vp.image, ""), p.image) as image, categories.name as category_name, vp.vendor_id, u.name as vendor_name, u.store_name, u.store_photo, u.address as store_address, u.latitude as vendor_latitude, u.longitude as vendor_longitude');
        $this->db->from('cart_items');
        $this->db->join('vendor_products vp', 'vp.id = cart_items.product_id', 'inner');
        $this->db->join('products p', 'p.id = vp.product_id', 'left');
        $this->db->join('categories', 'categories.id = vp.category_id', 'left');
        $this->db->join('users u', 'u.id = vp.vendor_id', 'left');
        $this->db->where('cart_items.user_id', $user_id);
        $this->db->where('vp.is_active', 1);
        $this->db->order_by('cart_items.id', 'ASC');

        $raw_items = $this->db->get()->result();

        // Customer location resolution for accurate per-store distance & delivery charges
        $this->load->helper('distance');
        $cust_lat = null;
        $cust_lon = null;
        $this->resolve_customer_location($cust_lat, $cust_lon);

        $items = [];
        $store_map = [];
        $total_items = count($raw_items);
        $total_quantity = 0;
        $items_subtotal = 0.00;

        foreach ($raw_items as $raw) {
            $raw->price = floatval($raw->price);
            $raw->sale_price = $raw->sale_price !== null ? floatval($raw->sale_price) : null;
            $active_price = $raw->sale_price !== null ? $raw->sale_price : $raw->price;

            $raw->item_total = round($active_price * (int)$raw->quantity, 2);
            $items_subtotal += $raw->item_total;
            $total_quantity += (int)$raw->quantity;

            $raw->image_url = !empty($raw->image) ? base_url($raw->image) : null;
            $raw->store_photo_url = !empty($raw->store_photo) ? base_url($raw->store_photo) : null;
            $raw->id = (int)$raw->product_id;
            $raw->name = $raw->product_name;
            if (!empty($raw->image_url)) {
                $raw->image = $raw->image_url;
            }

            $v_id = (int)($raw->vendor_id ?? 0);
            $v_name = !empty($raw->store_name) ? trim($raw->store_name) : (!empty($raw->vendor_name) ? trim($raw->vendor_name) : 'Store #' . $v_id);
            $raw->store_name = $v_name;
            $raw->vendor_id = $v_id;

            $items[] = $raw;

            if (!isset($store_map[$v_id])) {
                $v_dist = null;
                if ($cust_lat !== null && $cust_lon !== null && $raw->vendor_latitude !== null && $raw->vendor_longitude !== null && (float)$raw->vendor_latitude != 0 && (float)$raw->vendor_longitude != 0) {
                    $v_dist = calculate_distance_km($cust_lat, $cust_lon, (float)$raw->vendor_latitude, (float)$raw->vendor_longitude);
                }

                $store_map[$v_id] = [
                    'vendor_id' => $v_id,
                    'vendor_name' => $raw->vendor_name ?? '',
                    'store_name' => $v_name,
                    'store_photo_url' => $raw->store_photo_url,
                    'store_address' => $raw->store_address ?? '',
                    'distance_km' => $v_dist !== null ? round($v_dist, 2) : null,
                    'distance_text' => $v_dist !== null ? round($v_dist, 1) . ' km' : null,
                    'items' => [],
                    'items_count' => 0,
                    'total_quantity' => 0,
                    'subtotal' => 0.00,
                    'delivery_charge' => 0.00,
                    'discount' => 0.00,
                    'store_total' => 0.00
                ];
            }

            $store_map[$v_id]['items'][] = $raw;
            $store_map[$v_id]['items_count']++;
            $store_map[$v_id]['total_quantity'] += (int)$raw->quantity;
            $store_map[$v_id]['subtotal'] += $raw->item_total;
        }

        $store_groups = [];
        $total_delivery_charge = 0.00;
        $total_discount = 0.00;
        $grand_total = 0.00;

        foreach ($store_map as $v_id => $group) {
            $group['subtotal'] = round($group['subtotal'], 2);
            $dist = $group['distance_km'] ?? 0.00;
            $del_charge = calculate_delivery_charge_amount($dist, 'normal');
            $group['delivery_charge'] = round($del_charge, 2);
            $group['discount'] = round($group['discount'], 2);
            $group['store_total'] = round($group['subtotal'] + $group['delivery_charge'] - $group['discount'], 2);

            $total_delivery_charge += $group['delivery_charge'];
            $total_discount += $group['discount'];
            $grand_total += $group['store_total'];

            $store_groups[] = $group;
        }

        $stores_count = count($store_groups);
        $is_multi_store = $stores_count > 1;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'items' => $items,
                    'store_groups' => $store_groups,
                    'stores_count' => $stores_count,
                    'is_multi_store' => $is_multi_store,
                    'summary' => [
                        'total_items' => $total_items,
                        'total_quantity' => $total_quantity,
                        'subtotal' => round($items_subtotal, 2),
                        'total_delivery_charge' => round($total_delivery_charge, 2),
                        'total_discount' => round($total_discount, 2),
                        'total_amount' => round($grand_total, 2)
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
    // 4B. SAVED STORES & SHOPPING LISTS FLOW
    // ==========================================

    private function ensure_saved_stores_and_lists_tables()
    {
        if (!$this->db->table_exists('saved_stores')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `saved_stores` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `customer_id` INT NOT NULL,
                    `store_id` INT NOT NULL,
                    `created_at` DATETIME NOT NULL,
                    UNIQUE KEY `uniq_customer_store` (`customer_id`, `store_id`),
                    KEY `idx_customer` (`customer_id`),
                    KEY `idx_store` (`store_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->db->table_exists('shopping_lists')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `shopping_lists` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `name` VARCHAR(150) NOT NULL,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    KEY `idx_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->db->table_exists('shopping_list_items')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `shopping_list_items` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `list_id` INT NOT NULL,
                    `product_id` INT NOT NULL,
                    `quantity` INT NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME NOT NULL,
                    UNIQUE KEY `uniq_list_product` (`list_id`, `product_id`),
                    KEY `idx_list` (`list_id`),
                    KEY `idx_product` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }

    /**
     * Save / bookmark a store for the authenticated customer
     */
    public function save_store()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $store_id = (int)($input['store_id'] ?? $this->input->get('store_id', true));

        if ($store_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'Store ID is required',
                'data' => null
            ]));
        }

        $store = $this->db->get_where('users', ['id' => $store_id, 'role' => 'vendor', 'is_active' => 1])->row();
        if (!$store) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Store not found or inactive',
                'data' => null
            ]));
        }

        $existing = $this->db->get_where('saved_stores', ['customer_id' => $user_id, 'store_id' => $store_id])->row();
        if (!$existing) {
            $this->db->insert('saved_stores', [
                'customer_id' => $user_id,
                'store_id'    => $store_id,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Store saved successfully',
            'data' => ['is_saved' => true, 'store_id' => $store_id]
        ]));
    }

    /**
     * Unsave a store for the authenticated customer
     */
    public function unsave_store()
    {
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $store_id = (int)($input['store_id'] ?? $this->input->get('store_id', true));

        if ($store_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'Store ID is required',
                'data' => null
            ]));
        }

        $this->db->delete('saved_stores', ['customer_id' => $user_id, 'store_id' => $store_id]);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Store removed from saved stores',
            'data' => ['is_saved' => false, 'store_id' => $store_id]
        ]));
    }

    /**
     * Check if a store is saved by the current customer
     */
    public function is_store_saved()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->get_optional_user_id();
        $store_id = (int)$this->input->get('store_id', true);

        if (!$user_id || $store_id <= 0) {
            return $this->output->set_status_header(200)->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'data' => ['is_saved' => false]
            ]));
        }

        $this->ensure_saved_stores_and_lists_tables();
        $row = $this->db->get_where('saved_stores', ['customer_id' => $user_id, 'store_id' => $store_id])->row();

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'data' => ['is_saved' => ($row !== null)]
        ]));
    }

    /**
     * Get list of stores saved by the customer
     */
    public function get_saved_stores()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $this->resolve_customer_location($cust_lat, $cust_lon);
        $this->load->helper('distance');

        $saved_rows = $this->db
            ->select('ss.id as saved_id, ss.created_at as saved_at, u.id as store_id, u.name, u.store_name, u.owner_name, u.address, u.pincode, u.latitude, u.longitude, u.opening_time, u.closing_time, u.is_holiday, u.store_status, u.store_photo, u.contact_number, u.is_active')
            ->from('saved_stores ss')
            ->join('users u', 'u.id = ss.store_id', 'inner')
            ->where('ss.customer_id', $user_id)
            ->order_by('ss.created_at', 'DESC')
            ->get()
            ->result();

        $stores = [];
        $now_time = date('H:i:s');

        foreach ($saved_rows as $v) {
            if ((int)$v->is_active !== 1) continue;

            $dist = ($cust_lat !== null && $cust_lon !== null && is_numeric($v->latitude) && is_numeric($v->longitude))
                ? calculate_distance_km($cust_lat, $cust_lon, $v->latitude, $v->longitude)
                : null;

            $is_open = true;
            if (isset($v->store_status) && strtolower((string)$v->store_status) === 'closed') {
                $is_open = false;
            } elseif (isset($v->is_holiday) && (int)$v->is_holiday === 1) {
                $is_open = false;
            } elseif (!empty($v->opening_time) && !empty($v->closing_time) && $v->opening_time !== '00:00:00' && $v->closing_time !== '00:00:00') {
                if ($v->opening_time <= $v->closing_time) {
                    $is_open = ($now_time >= $v->opening_time && $now_time <= $v->closing_time);
                } else {
                    $is_open = ($now_time >= $v->opening_time || $now_time <= $v->closing_time);
                }
            }

            $total_products = $this->db
                ->where('vendor_id', $v->store_id)
                ->where('is_active', 1)
                ->where('stock >', 0)
                ->count_all_results('vendor_products');

            $categories = $this->db
                ->select('c.name')
                ->from('vendor_products vp')
                ->join('categories c', 'c.id = vp.category_id')
                ->where('vp.vendor_id', $v->store_id)
                ->where('vp.is_active', 1)
                ->where('vp.stock >', 0)
                ->group_by('c.id')
                ->limit(3)
                ->get()
                ->result();

            $category_names = array_map(function($c) { return $c->name; }, $categories);
            $category_summary = !empty($category_names) ? implode(', ', $category_names) : 'General Grocery';

            $stores[] = [
                'saved_id'         => (int)$v->saved_id,
                'store_id'         => (int)$v->store_id,
                'id'               => (int)$v->store_id,
                'store_name'       => !empty($v->store_name) ? $v->store_name : $v->name,
                'name'             => $v->name,
                'store_photo_url'  => !empty($v->store_photo) ? base_url($v->store_photo) : null,
                'address'          => $v->address,
                'pincode'          => $v->pincode,
                'contact_number'   => $v->contact_number,
                'distance_km'      => $dist !== null ? round((float)$dist, 2) : null,
                'distance_text'    => $dist !== null ? ($dist < 1 ? round($dist * 1000) . ' m' : round($dist, 1) . ' km') : null,
                'is_open'          => $is_open,
                'status_label'     => $is_open ? 'Open' : 'Closed',
                'category_summary' => $category_summary,
                'total_products'   => (int)$total_products,
                'saved_at'         => $v->saved_at,
                'is_saved'         => true
            ];
        }

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Saved stores retrieved successfully',
            'data' => [
                'stores' => $stores,
                'total' => count($stores)
            ]
        ]));
    }

    /**
    /**
     * Helper to fetch and format items of a shopping list
     */
    private function _fetch_list_items($list_id)
    {
        $items = $this->db
            ->select('sli.id as list_item_id, sli.quantity, sli.created_at as added_at,
                      COALESCE(vp.id, sli.product_id) as product_id,
                      COALESCE(vp.product_name, p.name, "Product") as product_name,
                      COALESCE(vp.mrp, p.price, 0) as price,
                      COALESCE(vp.selling_price, p.sale_price, p.price, 0) as sale_price,
                      COALESCE(vp.stock, 0) as stock_quantity,
                      COALESCE(vp.is_active, p.is_active, 1) as is_active,
                      COALESCE(NULLIF(vp.image, ""), p.image, "") as image,
                      COALESCE(c.name, "") as category_name,
                      COALESCE(vp.vendor_id, 0) as vendor_id,
                      COALESCE(u.store_name, u.name, "") as store_name,
                      u.name as vendor_name')
            ->from('shopping_list_items sli')
            ->join('vendor_products vp', 'vp.id = sli.product_id', 'left')
            ->join('products p', 'p.id = COALESCE(vp.product_id, sli.product_id)', 'left')
            ->join('categories c', 'c.id = COALESCE(vp.category_id, p.category_id)', 'left')
            ->join('users u', 'u.id = vp.vendor_id', 'left')
            ->where('sli.list_id', $list_id)
            ->order_by('sli.id', 'DESC')
            ->get()
            ->result();

        $formatted = [];
        foreach ($items as $it) {
            $sname = !empty($it->store_name) ? $it->store_name : ($it->vendor_name ?? (!empty($it->vendor_id) ? 'Store #' . $it->vendor_id : ''));
            $formatted[] = [
                'list_item_id'   => (int)$it->list_item_id,
                'id'             => (int)$it->product_id,
                'product_id'     => (int)$it->product_id,
                'name'           => $it->product_name,
                'product_name'   => $it->product_name,
                'price'          => (float)$it->price,
                'sale_price'     => (float)($it->sale_price ?? $it->price),
                'image'          => !empty($it->image) ? $this->get_product_image_url($it->image) : '',
                'image_url'      => !empty($it->image) ? $this->get_product_image_url($it->image) : '',
                'category_name'  => $it->category_name ?? '',
                'vendor_id'      => (int)$it->vendor_id,
                'store_name'     => $sname,
                'quantity'       => (int)$it->quantity,
                'stock'          => (int)$it->stock_quantity,
                'is_active'      => (bool)$it->is_active,
                'is_available'   => (bool)($it->is_active && $it->stock_quantity > 0),
                'added_at'       => $it->added_at
            ];
        }
        return $formatted;
    }

    /**
     * Helper to resolve product (supports vendor_products.id and master products.id)
     */
    private function _resolve_product_for_list($product_id)
    {
        // 1. Direct vendor_products.id
        $vp = $this->db->get_where('vendor_products', ['id' => $product_id, 'is_active' => 1])->row();
        if ($vp) {
            return $vp;
        }

        // 2. Check if product_id is master products.id with an active vendor_product
        $vp = $this->db
            ->where('product_id', $product_id)
            ->where('is_active', 1)
            ->order_by('selling_price', 'ASC')
            ->get('vendor_products')
            ->row();
        if ($vp) {
            return $vp;
        }

        // 3. Fallback: master products table
        $p = $this->db->get_where('products', ['id' => $product_id, 'is_active' => 1])->row();
        if ($p) {
            return (object)[
                'id'           => (int)$p->id,
                'product_name' => $p->name,
                'selling_price'=> $p->sale_price ?: $p->price,
                'mrp'          => $p->price,
                'stock'        => 999,
                'is_active'    => 1,
                'vendor_id'    => 0
            ];
        }

        return null;
    }

    /**
     * Get list of shopping lists for customer
     */
    public function get_shopping_lists()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $lists = $this->db
            ->where('user_id', $user_id)
            ->order_by('updated_at', 'DESC')
            ->get('shopping_lists')
            ->result();

        $data = [];
        foreach ($lists as $l) {
            $formatted_items = $this->_fetch_list_items($l->id);

            $preview_images = [];
            foreach ($formatted_items as $fi) {
                if (!empty($fi['image_url']) && count($preview_images) < 4) {
                    $preview_images[] = $fi['image_url'];
                }
            }

            $data[] = [
                'id'             => (int)$l->id,
                'name'           => $l->name,
                'items_count'    => count($formatted_items),
                'items'          => $formatted_items,
                'preview_images' => $preview_images,
                'created_at'     => $l->created_at,
                'updated_at'     => $l->updated_at
            ];
        }

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Shopping lists fetched successfully',
            'data' => [
                'lists' => $data,
                'total' => count($data)
            ]
        ]));
    }

    /**
     * Create a new named shopping list
     */
    public function create_shopping_list()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $name = trim($input['name'] ?? '');

        if ($name === '') {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'List name is required',
                'data' => null
            ]));
        }

        if (mb_strlen($name) > 150) {
            $name = mb_substr($name, 0, 150);
        }

        $now = date('Y-m-d H:i:s');
        $this->db->insert('shopping_lists', [
            'user_id'    => $user_id,
            'name'       => $name,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $new_id = (int)$this->db->insert_id();

        // Optional initial product to add
        $product_id = (int)($input['product_id'] ?? 0);
        $added_initial = false;
        if ($product_id > 0) {
            $product = $this->_resolve_product_for_list($product_id);
            if ($product) {
                $actual_pid = (int)$product->id;
                $this->db->insert('shopping_list_items', [
                    'list_id'    => $new_id,
                    'product_id' => $actual_pid,
                    'quantity'   => max(1, (int)($input['quantity'] ?? 1)),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                $added_initial = true;
            }
        }

        $list_items = $this->_fetch_list_items($new_id);

        return $this->output->set_status_header(201)->set_output(json_encode([
            'status' => true,
            'code' => 201,
            'message' => 'Shopping list created successfully',
            'data' => [
                'id'             => $new_id,
                'name'           => $name,
                'items_count'    => count($list_items),
                'items'          => $list_items,
                'preview_images' => !empty($list_items) && !empty($list_items[0]['image_url']) ? [$list_items[0]['image_url']] : [],
                'created_at'     => $now,
                'updated_at'     => $now
            ]
        ]));
    }

    /**
     * Rename an existing shopping list
     */
    public function rename_shopping_list()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $list_id = (int)($input['list_id'] ?? 0);
        $name = trim($input['name'] ?? '');

        if ($list_id <= 0 || $name === '') {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'List ID and non-empty name are required',
                'data' => null
            ]));
        }

        $list = $this->db->get_where('shopping_lists', ['id' => $list_id, 'user_id' => $user_id])->row();
        if (!$list) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Shopping list not found',
                'data' => null
            ]));
        }

        $this->db->where('id', $list_id)->update('shopping_lists', [
            'name'       => $name,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Shopping list renamed successfully',
            'data' => ['id' => $list_id, 'name' => $name]
        ]));
    }

    /**
     * Delete a shopping list and all its items
     */
    public function delete_shopping_list()
    {
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $list_id = (int)($input['list_id'] ?? $this->input->get('list_id', true));

        if ($list_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'List ID is required',
                'data' => null
            ]));
        }

        $list = $this->db->get_where('shopping_lists', ['id' => $list_id, 'user_id' => $user_id])->row();
        if (!$list) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Shopping list not found',
                'data' => null
            ]));
        }

        $this->db->delete('shopping_list_items', ['list_id' => $list_id]);
        $this->db->delete('shopping_lists', ['id' => $list_id, 'user_id' => $user_id]);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Shopping list deleted successfully',
            'data' => ['id' => $list_id]
        ]));
    }

    /**
     * Get items inside a shopping list
     */
    public function get_shopping_list_items()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $list_id = (int)$this->input->get('list_id', true);
        if ($list_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'Shopping list ID is required',
                'data' => null
            ]));
        }

        $list = $this->db->get_where('shopping_lists', ['id' => $list_id, 'user_id' => $user_id])->row();
        if (!$list) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Shopping list not found',
                'data' => null
            ]));
        }

        $formatted = $this->_fetch_list_items($list_id);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Shopping list items retrieved successfully',
            'data' => [
                'list' => [
                    'id'          => (int)$list->id,
                    'name'        => $list->name,
                    'items_count' => count($formatted),
                    'created_at'  => $list->created_at,
                    'updated_at'  => $list->updated_at
                ],
                'items' => $formatted
            ]
        ]));
    }

    /**
     * Add a product to a shopping list
     */
    public function add_to_shopping_list()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $list_id = (int)($input['list_id'] ?? 0);
        $product_id = (int)($input['product_id'] ?? 0);
        $quantity = max(1, (int)($input['quantity'] ?? 1));

        if ($list_id <= 0 || $product_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'List ID and Product ID are required',
                'data' => null
            ]));
        }

        $list = $this->db->get_where('shopping_lists', ['id' => $list_id, 'user_id' => $user_id])->row();
        if (!$list) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Shopping list not found',
                'data' => null
            ]));
        }

        $product = $this->_resolve_product_for_list($product_id);
        if (!$product) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Product not found or inactive',
                'data' => null
            ]));
        }

        $actual_pid = (int)$product->id;

        $existing = $this->db->get_where('shopping_list_items', [
            'list_id'    => $list_id,
            'product_id' => $actual_pid
        ])->row();

        if ($existing) {
            $this->db->where('id', $existing->id)->update('shopping_list_items', [
                'quantity'   => $existing->quantity + $quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->insert('shopping_list_items', [
                'list_id'    => $list_id,
                'product_id' => $actual_pid,
                'quantity'   => $quantity,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->where('id', $list_id)->update('shopping_lists', [
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => "Added {$product->product_name} to '{$list->name}'",
            'data' => [
                'list_id'    => $list_id,
                'product_id' => $actual_pid,
                'list_name'  => $list->name
            ]
        ]));
    }

    /**
     * Remove a product from a shopping list
     */
    public function remove_from_shopping_list()
    {
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $list_id = (int)($input['list_id'] ?? $this->input->get('list_id', true));
        $product_id = (int)($input['product_id'] ?? $this->input->get('product_id', true));

        if ($list_id <= 0 || $product_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'List ID and Product ID are required',
                'data' => null
            ]));
        }

        $list = $this->db->get_where('shopping_lists', ['id' => $list_id, 'user_id' => $user_id])->row();
        if (!$list) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Shopping list not found',
                'data' => null
            ]));
        }

        // Remove by matching product_id OR list_item_id
        $this->db->group_start()
            ->where('product_id', $product_id)
            ->or_where('id', $product_id)
            ->group_end()
            ->where('list_id', $list_id)
            ->delete('shopping_list_items');

        $this->db->where('id', $list_id)->update('shopping_lists', [
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Product removed from shopping list',
            'data' => null
        ]));
    }

    /**
     * Add every product in a shopping list to cart in one action
     */
    public function shopping_list_add_all_to_cart()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_saved_stores_and_lists_tables();
        $this->check_cart_table();

        $input = json_decode($this->input->raw_input_stream, true) ?: $this->input->post();
        $list_id = (int)($input['list_id'] ?? 0);

        if ($list_id <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'Shopping list ID is required',
                'data' => null
            ]));
        }

        $list = $this->db->get_where('shopping_lists', ['id' => $list_id, 'user_id' => $user_id])->row();
        if (!$list) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => false,
                'code' => 404,
                'message' => 'Shopping list not found',
                'data' => null
            ]));
        }

        $list_items = $this->db->get_where('shopping_list_items', ['list_id' => $list_id])->result();
        if (empty($list_items)) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'Shopping list is empty',
                'data' => null
            ]));
        }

        $added_count = 0;
        $this->db->trans_begin();

        foreach ($list_items as $item) {
            $vp = $this->db->get_where('vendor_products', ['id' => $item->product_id, 'is_active' => 1])->row();
            if (!$vp) {
                // If item->product_id was a master products.id, resolve to active vendor_product
                $vp = $this->db
                    ->where('product_id', $item->product_id)
                    ->where('is_active', 1)
                    ->where('stock >', 0)
                    ->order_by('selling_price', 'ASC')
                    ->get('vendor_products')
                    ->row();
            }

            if (!$vp || (int)$vp->stock <= 0) {
                continue;
            }

            $desired_qty = max(1, (int)$item->quantity);
            $cart_product_id = (int)$vp->id;

            $existing_cart = $this->db->get_where('cart_items', [
                'user_id'    => $user_id,
                'product_id' => $cart_product_id
            ])->row();

            if ($existing_cart) {
                $new_qty = min((int)$vp->stock, (int)$existing_cart->quantity + $desired_qty);
                $this->db->where('id', $existing_cart->id)->update('cart_items', [
                    'quantity' => $new_qty
                ]);
            } else {
                $initial_qty = min((int)$vp->stock, $desired_qty);
                $this->db->insert('cart_items', [
                    'user_id'    => $user_id,
                    'product_id' => $cart_product_id,
                    'quantity'   => $initial_qty,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            $added_count++;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->output->set_status_header(500)->set_output(json_encode([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to add items to cart',
                'data' => null
            ]));
        }

        $this->db->trans_commit();

        return $this->output->set_status_header(200)->set_output(json_encode([
            'status' => true,
            'code' => 200,
            'message' => $added_count > 0
                ? "Added {$added_count} item(s) from '{$list->name}' to cart"
                : "No in-stock items available in this list to add",
            'data' => [
                'added_count' => $added_count,
                'list_id'     => $list_id
            ]
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

        // If coordinates were not provided, attempt automatic server-side geocoding
        if ($address && ($address->latitude === null || $address->longitude === null || (float)$address->latitude == 0)) {
            $this->load->helper('distance');
            geocode_address_if_needed($address);
            $address = $this->db->get_where('user_addresses', ['id' => $address_id, 'user_id' => $user_id])->row();
        }

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

        // If coordinates are missing, attempt auto-geocoding
        if ($updated_address && ($updated_address->latitude === null || $updated_address->longitude === null || (float)$updated_address->latitude == 0)) {
            $this->load->helper('distance');
            geocode_address_if_needed($updated_address);
            $updated_address = $this->db->get_where('user_addresses', ['id' => $address_id, 'user_id' => $user_id])->row();
        }

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
        $delivery_option = !empty($input_data['delivery_option']) ? trim($input_data['delivery_option']) : 'delivery_partner'; // 'delivery_partner' or 'self'

        // Calculate delivery time slot window
        $chosen_time_option = isset($input_data['chosen_time_option']) && !empty($input_data['chosen_time_option']) ? trim(strtolower($input_data['chosen_time_option'])) : 'later';
        $allowed_options = ['immediately', 'later', 'lunch', 'dinner', 'custom'];
        if (!in_array($chosen_time_option, $allowed_options, true)) {
            $chosen_time_option = 'later';
        }
        $custom_delivery_time = isset($input_data['custom_delivery_time']) && !empty($input_data['custom_delivery_time']) ? trim($input_data['custom_delivery_time']) : null;

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

        // Group cart items by vendor for accurate per-store delivery fee calculation
        $vendor_groups = [];
        foreach ($cart_summary['items'] as $ci) {
            $v_id = (int) ($ci['vendor_id'] ?? 0);
            if (!isset($vendor_groups[$v_id])) {
                $vendor_groups[$v_id] = [
                    'vendor_id' => $v_id,
                    'items' => [],
                    'subtotal' => 0.00,
                    'total_gst' => 0.00,
                    'total_quantity' => 0
                ];
            }
            $vendor_groups[$v_id]['items'][] = $ci;
            $vendor_groups[$v_id]['subtotal'] += $ci['line_total'];
            $vendor_groups[$v_id]['total_gst'] += $ci['gst_amount'];
            $vendor_groups[$v_id]['total_quantity'] += $ci['quantity'];
        }

        $this->load->helper('distance');
        if ($address) {
            geocode_address_if_needed($address);
        }

        // Calculate delivery charges across all stores in the cart (sum of per-store delivery charges)
        $total_delivery_charge = 0.00;
        $max_distance = 0.00;
        $distance_method = 'pure_math';
        $primary_vendor = null;

        foreach ($vendor_groups as $v_id => $group) {
            $vendor = null;
            if ($v_id > 0) {
                $vendor = $this->db->select('id, address, pincode, latitude, longitude, prep_time_minutes, lunch_window_start, lunch_window_end, dinner_window_start, dinner_window_end, slot_immediately_enabled, slot_later_enabled, slot_lunch_enabled, slot_dinner_enabled, slot_custom_enabled, opening_time, closing_time, is_holiday')->get_where('users', ['id' => $v_id])->row();
            }
            if ($vendor) {
                geocode_vendor_if_needed($vendor);
                if (!$primary_vendor) {
                    $primary_vendor = $vendor;
                }
            }

            $v_dist = null;
            if (
                $vendor && $address &&
                $vendor->latitude !== null && $vendor->longitude !== null &&
                $address->latitude !== null && $address->longitude !== null &&
                (float) $vendor->latitude != 0 && (float) $vendor->longitude != 0 &&
                (float) $address->latitude != 0 && (float) $address->longitude != 0
            ) {
                $resolved = resolve_order_distance($vendor->latitude, $vendor->longitude, $address->latitude, $address->longitude, true);
                $v_dist = (float) $resolved['distance_km'];
                $distance_method = $resolved['method'];
            }

            if ($v_dist === null) {
                if (isset($input_data['distance']) && $input_data['distance'] !== '' && is_numeric($input_data['distance']) && floatval($input_data['distance']) >= 0) {
                    $v_dist = floatval($input_data['distance']);
                } else {
                    $v_dist = 0.00;
                }
            }

            $v_charge = calculate_delivery_charge_amount($v_dist, $delivery_type, $chosen_time_option);
            $total_delivery_charge += $v_charge;
            if ($v_dist > $max_distance) {
                $max_distance = $v_dist;
            }
        }

        $delivery_charge = round($total_delivery_charge, 2);
        $distance = round($max_distance, 2);

        $order_time = time();
        $calc_window = function_exists('calculate_order_delivery_window')
            ? calculate_order_delivery_window($order_time, $primary_vendor, $distance, $chosen_time_option, $custom_delivery_time)
            : ['window_start_dt' => null, 'window_end_dt' => null, 'window_display' => ''];
        $estimated_window_start = $calc_window['window_start_dt'];
        $estimated_window_end = $calc_window['window_end_dt'];

        $subtotal     = $cart_summary['subtotal'];
        $total_gst    = $cart_summary['total_gst'];
        $discount     = 0.00;
        $total_amount = round($subtotal + $total_gst + $delivery_charge - $discount, 2);
        $order_number = 'REC' . date('Ymd') . strtoupper(substr(uniqid(), -6));

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
        $chosen_time_option = isset($input_data['chosen_time_option']) && !empty($input_data['chosen_time_option']) ? trim(strtolower($input_data['chosen_time_option'])) : ($delivery_type === 'urgent' ? 'immediately' : 'later');
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
        $cart_summary = $this->get_cart_summary_for_order($user_id);

        if ($vendor_id <= 0 && !empty($cart_summary['items'])) {
            $vendor_items_map = [];
            foreach ($cart_summary['items'] as $ci) {
                $vi = (int) ($ci['vendor_id'] ?? 0);
                if ($vi > 0) {
                    $vendor_items_map[$vi][] = $ci;
                }
            }

            if (count($vendor_items_map) > 1) {
                // Multi-store cart delivery calculation
                $this->load->helper('distance');
                if ($address) {
                    geocode_address_if_needed($address);
                }

                $total_multi_delivery = 0.00;
                $store_charges = [];
                $rate_per_km = get_delivery_charge_per_km();

                foreach ($vendor_items_map as $vi => $items_list) {
                    $v_row = $this->db->select('id, name, store_name, address, pincode, latitude, longitude')->get_where('users', ['id' => $vi])->row();
                    if ($v_row) {
                        geocode_vendor_if_needed($v_row);
                    }

                    $v_dist = 0.00;
                    if (
                        $v_row && $address &&
                        $v_row->latitude !== null && $v_row->longitude !== null &&
                        $address->latitude !== null && $address->longitude !== null &&
                        (float) $v_row->latitude != 0 && (float) $v_row->longitude != 0 &&
                        (float) $address->latitude != 0 && (float) $address->longitude != 0
                    ) {
                        $res = resolve_order_distance($v_row->latitude, $v_row->longitude, $address->latitude, $address->longitude, true);
                        $v_dist = (float) ($res['distance_km'] ?? 0);
                    }

                    $v_charge = calculate_delivery_charge_amount($v_dist, $delivery_type, $chosen_time_option);
                    $total_multi_delivery += $v_charge;

                    $store_charges[] = [
                        'vendor_id' => $vi,
                        'store_name' => !empty($v_row->store_name) ? $v_row->store_name : ($v_row->name ?? 'Store #' . $vi),
                        'distance_km' => round($v_dist, 2),
                        'delivery_charge' => round($v_charge, 2),
                    ];
                }

                $immediate_extra = function_exists('get_immediate_delivery_charge') ? get_immediate_delivery_charge() : 50.00;

                return $this->output
                    ->set_status_header(200)
                    ->set_output(json_encode([
                        'status' => true,
                        'code' => 200,
                        'message' => 'Multi-store delivery charges calculated successfully.',
                        'data' => [
                            'is_multi_store'            => true,
                            'stores_count'              => count($store_charges),
                            'store_charges'             => $store_charges,
                            'delivery_type'             => $delivery_type,
                            'chosen_time_option'        => $chosen_time_option,
                            'rate_per_km'               => $rate_per_km,
                            'base_delivery_charge'      => round($total_multi_delivery, 2),
                            'immediate_delivery_charge' => $immediate_extra,
                            'extra_delivery_charge'     => 0.00,
                            'total_delivery_charge'     => round($total_multi_delivery, 2),
                            'delivery_charge'           => round($total_multi_delivery, 2),
                            'distance'                  => $store_charges[0]['distance_km'] ?? 0,
                            'distance_km'               => $store_charges[0]['distance_km'] ?? 0,
                            'slots'                     => []
                        ]
                    ]));
            } else if (!empty($cart_summary['items'])) {
                $vendor_id = (int) ($cart_summary['items'][0]['vendor_id'] ?? 0);
            }
        }

        $vendor = null;
        if ($vendor_id > 0) {
            $vendor = $this->db->select('id, address, pincode, latitude, longitude, prep_time_minutes, lunch_window_start, lunch_window_end, dinner_window_start, dinner_window_end, slot_immediately_enabled, slot_later_enabled, slot_lunch_enabled, slot_dinner_enabled, slot_custom_enabled, opening_time, closing_time, is_holiday')->get_where('users', ['id' => $vendor_id])->row();
        }

        $distance = null;
        $distance_method = 'pure_math';

        $this->load->helper('distance');

        if ($address) {
            geocode_address_if_needed($address);
        }
        if ($vendor) {
            geocode_vendor_if_needed($vendor);
        }

        if (
            $vendor && $address &&
            $vendor->latitude !== null && $vendor->longitude !== null &&
            $address->latitude !== null && $address->longitude !== null &&
            (float) $vendor->latitude != 0 && (float) $vendor->longitude != 0 &&
            (float) $address->latitude != 0 && (float) $address->longitude != 0
        ) {
            $resolved = resolve_order_distance($vendor->latitude, $vendor->longitude, $address->latitude, $address->longitude, true);
            $distance = $resolved['distance_km'];
            $distance_method = $resolved['method'];
        }

        if ($distance === null) {
            if (isset($input_data['distance']) && $input_data['distance'] !== '' && is_numeric($input_data['distance']) && floatval($input_data['distance']) >= 0) {
                $distance = floatval($input_data['distance']);
            } else {
                $distance = 0.00;
            }
        }

        $breakdown = get_delivery_charge_breakdown($distance, $chosen_time_option, $delivery_type);
        $rate_per_km = $breakdown['rate_per_km'];
        $billed_distance_km = $breakdown['billed_distance_km'];
        $base_charge = $breakdown['base_charge'];
        $extra_charge = $breakdown['extra_charge'];
        $immediate_delivery_charge = $breakdown['immediate_delivery_charge'];
        $is_immediate = $breakdown['is_immediate'];
        $delivery_charge = $breakdown['total_delivery_charge'];

        $slots = function_exists('get_vendor_available_delivery_slots') ? get_vendor_available_delivery_slots($vendor, $distance) : [];

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Delivery charge calculated successfully.',
                'data' => [
                    'distance'                  => $distance,
                    'distance_km'               => $distance,
                    'billed_distance_km'        => $billed_distance_km,
                    'distance_method'           => $distance_method,
                    'delivery_type'             => $delivery_type,
                    'chosen_time_option'        => $chosen_time_option,
                    'rate_per_km'               => $rate_per_km,
                    'base_delivery_charge'      => $base_charge,
                    'immediate_delivery_charge' => $immediate_delivery_charge,
                    'is_immediate'              => $is_immediate,
                    'extra_delivery_charge'     => $extra_charge,
                    'total_delivery_charge'     => $delivery_charge,
                    'delivery_charge'           => $delivery_charge,
                    'slots'                     => $slots
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
        if ($this->db->field_exists('hidden_by_customer', 'orders')) {
            $this->db->where('hidden_by_customer', 0);
        }
        if ($normalized_status !== '' && in_array($normalized_status, $valid_statuses, true)) {
            $this->db->where('status', $normalized_status);
        }
        $total_orders = $this->db->count_all_results('orders');

        $this->db->where('user_id', $user_id);
        if ($this->db->field_exists('hidden_by_customer', 'orders')) {
            $this->db->where('hidden_by_customer', 0);
        }
        if ($normalized_status !== '' && in_array($normalized_status, $valid_statuses, true)) {
            $this->db->where('status', $normalized_status);
        }
        $orders = $this->db->order_by('id', 'DESC')->limit($items_per_page, $offset)->get('orders')->result();

        $order_list = array_map(function ($order) {
            $first_item = $this->db->where('order_id', $order->id)->limit(1)->get('order_items')->row();

            $image_url = $first_item && !empty($first_item->product_image)
                ? $this->get_product_image_url($first_item->product_image)
                : '';

            $store_rows = $this->db
                ->distinct()
                ->select('oi.vendor_id, u.store_name, u.name as vendor_name')
                ->from('order_items oi')
                ->join('users u', 'u.id = oi.vendor_id', 'left')
                ->where('oi.order_id', $order->id)
                ->get()
                ->result_array();

            $store_names = [];
            $stores = [];
            foreach ($store_rows as $sr) {
                $name = !empty($sr['store_name']) ? $sr['store_name'] : ($sr['vendor_name'] ?? '');
                $vid = !empty($sr['vendor_id']) ? (int)$sr['vendor_id'] : null;
                if (!empty($name) && !in_array($name, $store_names, true)) {
                    $store_names[] = $name;
                }
                if ($vid && !isset($stores[$vid])) {
                    $stores[$vid] = [
                        'vendor_id'  => $vid,
                        'store_name' => $name ?: 'Store'
                    ];
                }
            }

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
                'store_names'       => $store_names,
                'stores'            => array_values($stores),
                'stores_count'      => count($store_names),
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

    /**
     * Customer hide order from their own history view
     */
    public function hide_order()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_orders_table();

        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $order_id = intval($input_data['order_id'] ?? 0);
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
            'user_id' => $user_id
        ])->row();

        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Order not found or does not belong to your account.',
                    'data' => null
                ]));
        }

        $this->db->where('id', $order_id)->update('orders', [
            'hidden_by_customer' => 1,
            'updated_at'         => date('Y-m-d H:i:s')
        ]);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Order removed from your history.',
                'data' => [
                    'order_id'     => $order_id,
                    'order_number' => $order->order_number
                ]
            ]));
    }

    /**
     * Ensure support_requests table exists with schema
     */
    private function ensure_support_requests_table()
    {
        if (!$this->db->table_exists('support_requests')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `support_requests` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `ticket_number` VARCHAR(32) NOT NULL UNIQUE,
              `order_id` INT NOT NULL,
              `customer_id` INT NOT NULL,
              `category` VARCHAR(100) NOT NULL,
              `description` TEXT NOT NULL,
              `status` ENUM('Open', 'In Progress', 'Resolved') NOT NULL DEFAULT 'Open',
              `admin_response` TEXT NULL,
              `resolved_at` DATETIME NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              INDEX `idx_support_order` (`order_id`),
              INDEX `idx_support_customer` (`customer_id`),
              INDEX `idx_support_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
    }

    /**
     * Customer support request creation for an order
     */
    public function create_support_request()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_support_requests_table();
        $this->ensure_orders_table();

        $input_data = json_decode($this->input->raw_input_stream, true);
        if (empty($input_data)) {
            $input_data = $this->input->post();
        }

        $order_id    = intval($input_data['order_id'] ?? 0);
        $category    = trim($input_data['category'] ?? '');
        $description = trim($input_data['description'] ?? '');

        if ($order_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 400,
                    'message' => 'A valid order ID is required.',
                    'data'    => null
                ]));
        }

        $valid_categories = [
            'Payment issue',
            'Order issue',
            'Missing item',
            'Wrong item',
            'Delivery issue',
            'Return/Refund issue',
            'Other'
        ];

        if (empty($category) || !in_array($category, $valid_categories, true)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 422,
                    'message' => 'Please select a valid issue category.',
                    'data'    => null
                ]));
        }

        if (empty($description)) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 422,
                    'message' => 'Please provide a description of the issue.',
                    'data'    => null
                ]));
        }

        if (mb_strlen($description) < 10) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 422,
                    'message' => 'Please provide a more detailed description (at least 10 characters).',
                    'data'    => null
                ]));
        }

        // Verify order exists and belongs to this user
        $order = $this->db->get_where('orders', [
            'id'      => $order_id,
            'user_id' => $user_id
        ])->row();

        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 404,
                    'message' => 'Order not found or does not belong to your account.',
                    'data'    => null
                ]));
        }

        // Generate human-readable unique reference number: SR-YYYYMMDD-XXXXXX
        do {
            $random_suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $ticket_number = 'SR-' . date('Ymd') . '-' . $random_suffix;
            $exists = $this->db->where('ticket_number', $ticket_number)->count_all_results('support_requests');
        } while ($exists > 0);

        $now = date('Y-m-d H:i:s');
        $this->db->insert('support_requests', [
            'ticket_number' => $ticket_number,
            'order_id'      => $order_id,
            'customer_id'   => $user_id,
            'category'      => $category,
            'description'   => $description,
            'status'        => 'Open',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $request_id = $this->db->insert_id();

        // Notify and record in order status timeline
        $this->insert_status_history(
            $order_id,
            $order->status,
            "Customer opened support ticket #{$ticket_number} [{$category}]",
            'customer'
        );

        log_message('info', "Support request {$ticket_number} created for Order #{$order->order_number} (ID: {$order_id}) by user #{$user_id}.");

        return $this->output
            ->set_status_header(201)
            ->set_output(json_encode([
                'status'  => true,
                'code'    => 201,
                'message' => 'Support request submitted successfully. Our support team has been notified.',
                'data'    => [
                    'id'             => (int)$request_id,
                    'ticket_number'  => $ticket_number,
                    'order_id'       => (int)$order_id,
                    'order_number'   => $order->order_number,
                    'category'       => $category,
                    'description'    => $description,
                    'status'         => 'Open',
                    'admin_response' => null,
                    'resolved_at'    => null,
                    'created_at'     => $now,
                ]
            ]));
    }

    /**
     * Get all support requests for a specific order
     */
    public function get_order_support_requests($order_id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->authenticate();
        $this->ensure_support_requests_table();
        $this->ensure_orders_table();

        $order_id = intval($order_id ?: $this->input->get('order_id', true));

        if ($order_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 400,
                    'message' => 'A valid order ID is required.',
                    'data'    => null
                ]));
        }

        $order = $this->db->get_where('orders', [
            'id'      => $order_id,
            'user_id' => $user_id
        ])->row();

        if (!$order) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 404,
                    'message' => 'Order not found or does not belong to your account.',
                    'data'    => null
                ]));
        }

        $rows = $this->db
            ->where('order_id', $order_id)
            ->where('customer_id', $user_id)
            ->order_by('id', 'DESC')
            ->get('support_requests')
            ->result_array();

        $requests = array_map(function ($r) {
            return [
                'id'             => (int)$r['id'],
                'ticket_number'  => $r['ticket_number'],
                'order_id'       => (int)$r['order_id'],
                'category'       => $r['category'],
                'description'    => $r['description'],
                'status'         => $r['status'],
                'admin_response' => $r['admin_response'],
                'resolved_at'    => $r['resolved_at'],
                'created_at'     => $r['created_at'],
                'updated_at'     => $r['updated_at'],
            ];
        }, $rows);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => true,
                'code'    => 200,
                'message' => 'Support requests retrieved successfully.',
                'data'    => [
                    'order_id'     => (int)$order_id,
                    'order_number' => $order->order_number,
                    'total_count'  => count($requests),
                    'requests'     => $requests,
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
                'total_amount' => round($vendor_total + (float)$order->delivery_charge, 2),
                'delivery_charge' => (float)$order->delivery_charge,
                'delivery_option' => $order->delivery_option ?? 'delivery_partner',
                'distance' => $order->distance ? (float)$order->distance : null,
                'distance_km' => isset($order->distance_km) ? (float)$order->distance_km : ($order->distance ? (float)$order->distance : null),
                'distance_method' => $order->distance_method ?? 'pure_math',
                'delivery_type' => $order->delivery_type ?? 'normal',
                'chosen_time_option' => $order->chosen_time_option ?? 'immediately',
                'estimated_window_formatted' => function_exists('format_slot_window_display') ? format_slot_window_display($order->chosen_time_option ?? 'immediately', $order->estimated_window_start ?? null, $order->estimated_window_end ?? null) : ($order->chosen_time_option ?? 'immediately'),
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
            'limit' => 20,
            'in_stock_only' => true
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

        $nearby_stores = $this->get_nearby_stores_data($cust_lat, $cust_lon, 12);
        $hero = $this->fetch_hero_data();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Home data fetched successfully',
                'data' => [
                    'hero'                 => $hero,
                    'categories'           => $categories,
                    'stores'               => $nearby_stores,
                    'products'             => $discovery['products'],
                    'delivery_radius_km'   => $discovery['delivery_radius_km'],
                    'nearby_vendors_count' => (int)($discovery['nearby_vendors_count'] ?? 0),
                    'no_nearby_vendors'    => (bool)($discovery['no_nearby_vendors'] ?? false)
                ]
            ]));
    }


    /**
     * Get Hero section text & active slider images (Public - No Token required)
     */
    public function get_hero_section()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $hero = $this->fetch_hero_data();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => true,
                'code'    => 200,
                'message' => 'Hero section fetched successfully',
                'data'    => $hero
            ]));
    }

    private function fetch_hero_data()
    {
        $content = $this->db->where('id', 1)->get('hero_section')->row();
        if (!$content) {
            $content = (object)[
                'id'          => 1,
                'title'       => 'Delicious Food & Beverages',
                'subtitle'    => 'Delivered to Your Door',
                'description' => 'Discover premium quality food and beverages. Fresh, organic, and delivered with care.',
                'btn_text'    => 'Shop Now',
                'btn_link'    => '/products',
                'btn2_text'   => 'Explore Products',
                'btn2_link'   => '#featured',
            ];
        }

        $sliders_query = $this->db
            ->where('is_active', 1)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'DESC')
            ->get('hero_sliders')
            ->result();

        $sliders = [];
        foreach ($sliders_query as $slide) {
            $image_url = filter_var($slide->image, FILTER_VALIDATE_URL) 
                ? $slide->image 
                : base_url($slide->image);

            $sliders[] = [
                'id'         => (int)$slide->id,
                'title'      => $slide->title ?? '',
                'image_url'  => $image_url,
                'sort_order' => (int)$slide->sort_order
            ];
        }

        if (empty($sliders)) {
            $sliders[] = [
                'id'         => 0,
                'title'      => 'Fresh Food & Groceries',
                'image_url'  => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=700',
                'sort_order' => 1
            ];
        }

        return [
            'content' => $content,
            'sliders' => $sliders
        ];
    }

    /**
     * Get list of all active categories (No JWT token needed)
     */
    public function get_category_list()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $search = trim($this->input->get('search', true) ?? $this->input->get('q', true) ?? '');

        $this->db->where('is_active', 1);
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }
        $categories = $this->db->order_by('id', 'ASC')->get('categories')->result();

        // Calculate product counts for each category
        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;

            // Count in vendor_products
            $vp_count = (int)$this->db->where('category_id', (int)$cat->id)
                                      ->where('is_active', 1)
                                      ->count_all_results('vendor_products');

            // Count in master products
            $p_count = (int)$this->db->where('category_id', (int)$cat->id)
                                     ->where('is_active', 1)
                                     ->count_all_results('products');

            $count = max($vp_count, $p_count);
            $cat->product_count = $count;
            $cat->has_products = ($count > 0);
        }

        // Sort: categories with products FIRST, categories without products LAST
        usort($categories, function ($a, $b) {
            $a_has = $a->has_products ? 1 : 0;
            $b_has = $b->has_products ? 1 : 0;
            if ($a_has !== $b_has) {
                return $b_has - $a_has; // 1 before 0 (has products first)
            }
            if ($a->product_count !== $b->product_count) {
                return ($a->product_count > $b->product_count) ? -1 : 1;
            }
            return (int)$a->id - (int)$b->id;
        });

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
                'delivery_radius_km' => $discovery['delivery_radius_km'],
                'nearby_vendors_count' => (int)($discovery['nearby_vendors_count'] ?? 0),
                'no_nearby_vendors' => (bool)($discovery['no_nearby_vendors'] ?? false)
            ]));
    }

    /**
     * Get list of nearby stores (factual info only: image, name, category, status, distance, view store action)
     */
    public function get_nearby_stores()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $this->resolve_customer_location($cust_lat, $cust_lon);
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? 20)));

        $stores = $this->get_nearby_stores_data($cust_lat, $cust_lon, $limit);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Nearby stores fetched successfully',
                'data' => [
                    'stores' => $stores,
                    'total' => count($stores),
                ]
            ]));
    }

    /**
     * Get dedicated store details by vendor ID
     */
    public function get_store_detail($vendor_id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if ($vendor_id === null) {
            $vendor_id = $this->input->get('vendor_id', true) ?? $this->input->get('id', true);
        }
        $vendor_id = (int)$vendor_id;

        if ($vendor_id <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Store / Vendor ID is required',
                    'data' => null
                ]));
        }

        $v = $this->db
            ->select('id, name, store_name, owner_name, address, pincode, latitude, longitude, opening_time, closing_time, is_holiday, store_status, store_photo, profile_image, contact_number, is_active')
            ->where('id', $vendor_id)
            ->where('role', 'vendor')
            ->get('users')
            ->row();

        if (!$v || (int)$v->is_active !== 1) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 404,
                    'message' => 'Store not found or inactive',
                    'data' => null
                ]));
        }

        $this->resolve_customer_location($cust_lat, $cust_lon);
        $this->load->helper('distance');
        $dist = ($cust_lat !== null && $cust_lon !== null && is_numeric($v->latitude) && is_numeric($v->longitude))
            ? calculate_distance_km($cust_lat, $cust_lon, $v->latitude, $v->longitude)
            : null;

        // Factual Open/Closed status
        $now_time = date('H:i:s');
        $is_open = true;
        $status_reason = 'Operating normally';
        if (isset($v->store_status) && strtolower((string)$v->store_status) === 'closed') {
            $is_open = false;
            $status_reason = 'Store is currently closed';
        } elseif (isset($v->is_holiday) && (int)$v->is_holiday === 1) {
            $is_open = false;
            $status_reason = 'On holiday';
        } elseif (!empty($v->opening_time) && !empty($v->closing_time) && $v->opening_time !== '00:00:00' && $v->closing_time !== '00:00:00') {
            $open_t = $v->opening_time;
            $close_t = $v->closing_time;
            if ($open_t <= $close_t) {
                $is_open = ($now_time >= $open_t && $now_time <= $close_t);
            } else {
                $is_open = ($now_time >= $open_t || $now_time <= $close_t);
            }
            if (!$is_open) {
                $status_reason = 'Outside operating hours';
            }
        }

        $operating_hours = 'Hours not specified';
        if (!empty($v->opening_time) && !empty($v->closing_time)) {
            $formatted_open = date('h:i A', strtotime($v->opening_time));
            $formatted_close = date('h:i A', strtotime($v->closing_time));
            $operating_hours = "{$formatted_open} - {$formatted_close}";
        }

        // Store categories (only categories that have active products in this store)
        $categories = $this->db
            ->select('c.id, c.name, c.slug, c.image, COUNT(vp.id) as product_count')
            ->from('vendor_products vp')
            ->join('categories c', 'c.id = vp.category_id')
            ->where('vp.vendor_id', $vendor_id)
            ->where('vp.is_active', 1)
            ->where('vp.stock >', 0)
            ->group_by('c.id')
            ->order_by('product_count', 'DESC')
            ->get()
            ->result();

        foreach ($categories as $cat) {
            $cat->image_url = !empty($cat->image) ? base_url($cat->image) : null;
        }

        $category_names = array_map(function($c) { return $c->name; }, $categories);
        $category_summary = !empty($category_names) ? implode(', ', array_slice($category_names, 0, 3)) : 'General Grocery';

        $total_products = $this->db
            ->where('vendor_id', $vendor_id)
            ->where('is_active', 1)
            ->where('stock >', 0)
            ->count_all_results('vendor_products');

        $store_data = [
            'id'               => (int)$v->id,
            'name'             => $v->name,
            'store_name'       => !empty($v->store_name) ? $v->store_name : $v->name,
            'owner_name'       => $v->owner_name,
            'store_photo'      => $v->store_photo,
            'store_photo_url'  => !empty($v->store_photo) ? base_url($v->store_photo) : null,
            'address'          => $v->address,
            'pincode'          => $v->pincode,
            'contact_number'   => $v->contact_number,
            'latitude'         => (float)$v->latitude,
            'longitude'        => (float)$v->longitude,
            'distance_km'      => $dist !== null ? (float)$dist : null,
            'distance_text'    => $dist !== null ? ($dist < 1 ? round($dist * 1000) . ' m' : round($dist, 1) . ' km') : null,
            'store_status'     => $v->store_status ?? 'open',
            'is_holiday'       => (int)($v->is_holiday ?? 0),
            'opening_time'     => $v->opening_time,
            'closing_time'     => $v->closing_time,
            'operating_hours'  => $operating_hours,
            'is_open'          => $is_open,
            'status_label'     => $is_open ? 'Open' : 'Closed',
            'status_reason'    => $status_reason,
            'category_summary' => $category_summary,
            'total_products'   => (int)$total_products,
            'categories'       => $categories,
        ];

        // Check if customer saved this store
        $is_saved = false;
        $optional_user_id = $this->get_optional_user_id();
        if ($optional_user_id) {
            $this->ensure_saved_stores_and_lists_tables();
            $check_s = $this->db->get_where('saved_stores', [
                'customer_id' => $optional_user_id,
                'store_id'    => $vendor_id
            ])->row();
            $is_saved = ($check_s !== null);
        }
        $store_data['is_saved'] = $is_saved;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Store details fetched successfully',
                'data' => $store_data
            ]));
    }

    /**
     * Get products strictly scoped to a specific store/vendor.
     * Supports category filtering and in-store search.
     */
    public function get_store_products($vendor_id = null)
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        if ($vendor_id === null) {
            $vendor_id = $this->input->get('vendor_id', true) ?? $this->input->get('id', true);
        }
        $vendor_id = (int)$vendor_id;

        if ($vendor_id <= 0) {
            return $this->output
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 422,
                    'message' => 'Store / Vendor ID is required',
                    'data' => null
                ]));
        }

        $category_id = $this->input->get('category_id', true);
        $search = trim($this->input->get('search', true) ?? $this->input->get('q', true) ?? '');
        $sort_by = trim($this->input->get('sort_by', true) ?? 'newest');
        $page = max(1, (int)($this->input->get('page', true) ?? 1));
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? 20)));

        $this->get_vendor_products_query();
        $this->db->where('vp.vendor_id', $vendor_id);
        $this->db->where('vp.is_active', 1);

        if (!empty($category_id)) {
            $this->db->where('vp.category_id', (int)$category_id);
        }

        if ($search !== '') {
            $words = array_filter(explode(' ', $search));
            foreach ($words as $w) {
                $w = trim($w);
                if ($w !== '') {
                    $this->db->group_start();
                    $this->db->like('vp.product_name', $w);
                    $this->db->or_like('vp.brand', $w);
                    $this->db->or_like('vp.description', $w);
                    $this->db->or_like('c.name', $w);
                    $this->db->group_end();
                }
            }
        }

        // Apply sorting
        switch ($sort_by) {
            case 'price_asc':
            case 'price_low_to_high':
                $this->db->order_by('vp.selling_price', 'ASC');
                break;
            case 'price_desc':
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
            default:
                $this->db->order_by('vp.id', 'DESC');
                break;
        }

        $all_store_rows = $this->db->get()->result();

        $grouped_store = [];
        foreach ($all_store_rows as $row) {
            $row->price = (float)$row->price;
            $row->sale_price = (float)$row->sale_price;
            $row->stock = (int)$row->stock;
            $row->image_url = !empty($row->image) ? base_url($row->image) : null;
            $row->store_photo_url = !empty($row->store_photo) ? base_url($row->store_photo) : null;
            $row->gallery_urls = [];

            if (!empty($row->product_id)) {
                $gk = 'p_' . $row->product_id;
            } else {
                $gk = 'n_' . strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $row->name)));
            }
            $grouped_store[$gk][] = $row;
        }

        $this->load->helper('distance');
        $threshold = get_low_stock_threshold();

        $products = [];
        foreach ($grouped_store as $gk => $items) {
            // Sort variants inside group: lowest sale_price first
            usort($items, function ($a, $b) {
                if ($a->sale_price == $b->sale_price) return 0;
                return ($a->sale_price < $b->sale_price) ? -1 : 1;
            });

            $lead = $items[0];
            $lead_variants = [];
            foreach ($items as $it) {
                $var_temp_unavail = (int)($it->temporarily_unavailable ?? 0);
                $var_stock = (int)$it->stock;
                $lead_variants[] = [
                    'id'                      => (int)$it->id,
                    'vendor_product_id'       => (int)$it->id,
                    'product_id'              => (int)$it->product_id,
                    'variant_id'              => !empty($it->variant_id) ? (int)$it->variant_id : null,
                    'variant_name'            => !empty($it->unit) ? $it->unit : $it->name,
                    'unit'                    => $it->unit ?? '',
                    'price'                   => (float)$it->price,
                    'sale_price'              => (float)$it->sale_price,
                    'stock'                   => $var_stock,
                    'temporarily_unavailable' => $var_temp_unavail,
                    'low_stock_threshold'     => $threshold,
                    'stock_state'             => classify_stock_state($var_stock, $var_temp_unavail, $threshold),
                ];
            }
            $lead->temporarily_unavailable = (int)($lead->temporarily_unavailable ?? 0);
            $lead->low_stock_threshold = $threshold;
            $lead->stock_state = classify_stock_state((int)$lead->stock, $lead->temporarily_unavailable, $threshold);
            $lead->variants = $lead_variants;
            $lead->variant_count = count($lead_variants);
            $lead->has_variants = count($lead_variants) > 1;
            $products[] = $lead;
        }

        // Apply sort_by on grouped products
        switch ($sort_by) {
            case 'price_asc':
            case 'price_low_to_high':
                usort($products, function ($a, $b) {
                    return ($a->sale_price < $b->sale_price) ? -1 : 1;
                });
                break;
            case 'price_desc':
            case 'price_high_to_low':
                usort($products, function ($a, $b) {
                    return ($a->sale_price > $b->sale_price) ? -1 : 1;
                });
                break;
            case 'name_asc':
                usort($products, function ($a, $b) {
                    return strcasecmp($a->name, $b->name);
                });
                break;
            case 'name_desc':
                usort($products, function ($a, $b) {
                    return strcasecmp($b->name, $a->name);
                });
                break;
            case 'newest':
            default:
                usort($products, function ($a, $b) {
                    return ($a->id > $b->id) ? -1 : 1;
                });
                break;
        }

        $total_records = count($products);
        $offset = ($page - 1) * $limit;
        $paginated_products = array_slice($products, $offset, $limit);

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Store products fetched successfully',
                'data' => [
                    'products' => $paginated_products,
                    'pagination' => [
                        'total_records' => (int)$total_records,
                        'current_page'  => $page,
                        'limit'         => $limit,
                        'total_pages'   => (int)ceil($total_records / $limit),
                    ]
                ]
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
                'delivery_radius_km' => $discovery['delivery_radius_km'],
                'nearby_vendors_count' => (int)($discovery['nearby_vendors_count'] ?? 0),
                'no_nearby_vendors' => (bool)($discovery['no_nearby_vendors'] ?? false)
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

        // Check customer location for distance & nearby alternatives/offers
        $this->load->helper('distance');
        $delivery_radius_km = get_delivery_radius_km();
        $threshold = get_low_stock_threshold();
        $product->delivery_radius_km = $delivery_radius_km;
        $product->low_stock_threshold = $threshold;
        $product->temporarily_unavailable = (int)($product->temporarily_unavailable ?? 0);
        $product->stock_state = classify_stock_state((int)$product->stock, $product->temporarily_unavailable, $threshold);
        $product->distance_km = null;
        $product->in_delivery_radius = true;
        $product->alternatives = [];
        $product->offers = [];

        $has_cust_loc = $this->resolve_customer_location($cust_lat, $cust_lon);
        if ($has_cust_loc && $product->vendor_latitude !== null && $product->vendor_longitude !== null) {
            $dist = calculate_distance_km($cust_lat, $cust_lon, $product->vendor_latitude, $product->vendor_longitude);
            $product->distance_km = $dist;
            $product->in_delivery_radius = ($dist <= $delivery_radius_km);
        }

        // Query all active vendor listings selling the same master product (product_id or matching name)
        $this->get_vendor_products_query();
        $this->db->where('vp.is_active', 1);
        $this->db->where('u.role', 'vendor');

        if (!empty($product->product_id)) {
            $this->db->where('vp.product_id', $product->product_id);
        } else {
            $this->db->where('LOWER(TRIM(vp.product_name))', strtolower(trim($product->name)));
        }

        $all_rows = $this->db->get()->result();

        // 1. Gather all variants for the CURRENT vendor
        $variants = [];
        foreach ($all_rows as $row) {
            if ((int)$row->vendor_id === (int)$product->vendor_id) {
                $var_temp = (int)($row->temporarily_unavailable ?? 0);
                $var_stock = (int)($row->stock ?? 0);
                $variants[] = [
                    'id'                      => (int)$row->id,
                    'vendor_product_id'       => (int)$row->id,
                    'product_id'              => (int)$row->product_id,
                    'variant_id'              => !empty($row->variant_id) ? (int)$row->variant_id : null,
                    'variant_name'            => !empty($row->unit) ? $row->unit : (!empty($row->name) ? $row->name : 'Standard'),
                    'unit'                    => $row->unit ?? '',
                    'price'                   => (float)($row->price ?? 0),
                    'sale_price'              => (float)($row->sale_price ?? $row->price),
                    'stock'                   => $var_stock,
                    'sku'                     => $row->sku ?? null,
                    'image_url'               => !empty($row->image) ? base_url($row->image) : $product->image_url,
                    'temporarily_unavailable' => $var_temp,
                    'low_stock_threshold'     => $threshold,
                    'stock_state'             => classify_stock_state($var_stock, $var_temp, $threshold),
                    'is_available'            => ($var_stock > 0 && ($row->store_status ?? 'open') !== 'closed' && empty($var_temp)),
                    'is_current'              => ((int)$row->id === (int)$product->id),
                ];
            }
        }
        // Ensure current product is in $variants if not present
        $has_curr_var = false;
        foreach ($variants as $v) {
            if ($v['id'] === (int)$product->id) {
                $has_curr_var = true;
                break;
            }
        }
        if (!$has_curr_var) {
            $variants[] = [
                'id'                      => (int)$product->id,
                'vendor_product_id'       => (int)$product->id,
                'product_id'              => (int)$product->product_id,
                'variant_id'              => !empty($product->variant_id) ? (int)$product->variant_id : null,
                'variant_name'            => !empty($product->unit) ? $product->unit : (!empty($product->name) ? $product->name : 'Standard'),
                'unit'                    => $product->unit ?? '',
                'price'                   => (float)($product->price ?? 0),
                'sale_price'              => (float)($product->sale_price ?? $product->price),
                'stock'                   => (int)($product->stock ?? 0),
                'sku'                     => $product->sku ?? null,
                'image_url'               => $product->image_url,
                'temporarily_unavailable' => $product->temporarily_unavailable,
                'low_stock_threshold'     => $threshold,
                'stock_state'             => $product->stock_state,
                'is_available'            => ((int)($product->stock ?? 0) > 0 && ($product->store_status ?? 'open') !== 'closed' && empty($product->temporarily_unavailable)),
                'is_current'              => true,
            ];
        }
        usort($variants, function ($a, $b) {
            return ($a['sale_price'] < $b['sale_price']) ? -1 : 1;
        });
        $product->variants = $variants;
        $product->has_variants = count($variants) > 1;
        $product->variant_count = count($variants);

        $qualifying_offers = [];
        $qualifying_alts = [];

        foreach ($all_rows as $row) {
            $row_dist = null;
            if ($has_cust_loc && $row->vendor_latitude !== null && $row->vendor_longitude !== null) {
                $row_dist = calculate_distance_km($cust_lat, $cust_lon, $row->vendor_latitude, $row->vendor_longitude);
                if ($row_dist > $delivery_radius_km) {
                    continue; // Skip stores outside delivery radius
                }
            }

            $mrp = (float)($row->price ?? 0);
            $sale_price = (float)($row->sale_price ?? $mrp);
            $stock = (int)($row->stock ?? 0);
            $store_status = $row->store_status ?? 'open';
            $is_holiday = (int)($row->is_holiday ?? 0);
            $is_open = ($store_status !== 'closed' && $is_holiday === 0);
            $row_temp_unavail = (int)($row->temporarily_unavailable ?? 0);
            $offer_stock_state = classify_stock_state($stock, $row_temp_unavail, $threshold);
            $is_available = ($stock > 0 && $is_open && empty($row_temp_unavail));

            if (!empty($row_temp_unavail)) {
                $availability = 'Temporarily Unavailable';
                $fulfilment_option = 'Unavailable';
            } elseif ($stock <= 0) {
                $availability = 'Out of Stock';
                $fulfilment_option = 'Unavailable';
            } elseif (!$is_open) {
                $availability = 'Store Closed';
                $open_time_str = !empty($row->store_opening_time) ? date('h:i A', strtotime($row->store_opening_time)) : '';
                $fulfilment_option = $open_time_str ? "Opens at {$open_time_str}" : "Store Closed";
            } else {
                $availability = ($stock < $threshold) ? 'Low Stock' : 'In Stock';
                $prep = isset($row->prep_time_minutes) && is_numeric($row->prep_time_minutes) && (int)$row->prep_time_minutes > 0 ? (int)$row->prep_time_minutes : 30;
                $travel = ($row_dist !== null) ? max(10, (int)ceil(($row_dist / 25.0) * 60.0)) : 15;
                $est_time = $prep + $travel;
                $fulfilment_option = "Standard Delivery (~{$est_time} mins)";
            }

            $discount_percent = 0;
            if ($mrp > 0 && $sale_price < $mrp) {
                $discount_percent = (int)round((($mrp - $sale_price) / $mrp) * 100);
            }

            $offer_item = [
                'vendor_product_id'       => (int)$row->id,
                'vendor_id'               => (int)$row->vendor_id,
                'vendor_name'             => $row->vendor_name ?? '',
                'store_name'              => !empty($row->store_name) ? $row->store_name : ($row->vendor_name ?? 'Store'),
                'store_photo_url'         => !empty($row->store_photo) ? base_url($row->store_photo) : null,
                'store_address'           => $row->store_address ?? '',
                'store_contact'           => $row->store_contact ?? '',
                'store_opening_time'      => $row->store_opening_time ?? null,
                'store_closing_time'      => $row->store_closing_time ?? null,
                'store_status'            => $store_status,
                'is_holiday'              => $is_holiday,
                'price'                   => $mrp,
                'sale_price'              => $sale_price,
                'discount_percent'        => $discount_percent,
                'stock'                   => $stock,
                'temporarily_unavailable' => $row_temp_unavail,
                'low_stock_threshold'     => $threshold,
                'stock_state'             => $offer_stock_state,
                'is_available'            => $is_available,
                'availability'            => $availability,
                'distance_km'             => $row_dist !== null ? (float)$row_dist : null,
                'distance_text'           => $row_dist !== null ? "{$row_dist} km away" : '',
                'fulfilment_option'       => $fulfilment_option,
                'is_current'              => ((int)$row->id === (int)$product->id),
                'ranking_badge'           => null,
            ];

            $qualifying_offers[] = $offer_item;

            // Backward compatible alternatives (other vendors with stock > 0)
            if ((int)$row->id !== (int)$product->id && $stock > 0) {
                $qualifying_alts[] = [
                    'vendor_product_id'       => (int)$row->id,
                    'vendor_id'               => (int)$row->vendor_id,
                    'vendor_name'             => $row->vendor_name ?? '',
                    'store_name'              => !empty($row->store_name) ? $row->store_name : ($row->vendor_name ?? 'Store'),
                    'store_address'           => $row->store_address ?? '',
                    'price'                   => $mrp,
                    'sale_price'              => $sale_price,
                    'stock'                   => $stock,
                    'temporarily_unavailable' => $row_temp_unavail,
                    'low_stock_threshold'     => $threshold,
                    'stock_state'             => $offer_stock_state,
                    'distance_km'             => $row_dist !== null ? (float)$row_dist : 0.0,
                ];
            }
        }

        // Ensure current product is present in offers list if not already
        $has_current = false;
        foreach ($qualifying_offers as $o) {
            if ($o['vendor_product_id'] === (int)$product->id) {
                $has_current = true;
                break;
            }
        }
        if (!$has_current) {
            $mrp = (float)($product->price ?? 0);
            $sale_price = (float)($product->sale_price ?? $mrp);
            $stock = (int)($product->stock ?? 0);
            $store_status = $product->store_status ?? 'open';
            $is_holiday = (int)($product->is_holiday ?? 0);
            $is_open = ($store_status !== 'closed' && $is_holiday === 0);
            $prod_temp_unavail = (int)($product->temporarily_unavailable ?? 0);
            $prod_stock_state = classify_stock_state($stock, $prod_temp_unavail, $threshold);
            $is_available = ($stock > 0 && $is_open && empty($prod_temp_unavail));

            if (!empty($prod_temp_unavail)) {
                $availability = 'Temporarily Unavailable';
                $fulfilment_option = 'Unavailable';
            } elseif ($stock <= 0) {
                $availability = 'Out of Stock';
                $fulfilment_option = 'Unavailable';
            } elseif (!$is_open) {
                $availability = 'Store Closed';
                $open_time_str = !empty($product->store_opening_time) ? date('h:i A', strtotime($product->store_opening_time)) : '';
                $fulfilment_option = $open_time_str ? "Opens at {$open_time_str}" : "Store Closed";
            } else {
                $availability = ($stock < $threshold) ? 'Low Stock' : 'In Stock';
                $prep = isset($product->prep_time_minutes) && is_numeric($product->prep_time_minutes) && (int)$product->prep_time_minutes > 0 ? (int)$product->prep_time_minutes : 30;
                $travel = ($product->distance_km !== null) ? max(10, (int)ceil(($product->distance_km / 25.0) * 60.0)) : 15;
                $fulfilment_option = "Standard Delivery (~" . ($prep + $travel) . " mins)";
            }

            $discount_percent = 0;
            if ($mrp > 0 && $sale_price < $mrp) {
                $discount_percent = (int)round((($mrp - $sale_price) / $mrp) * 100);
            }

            array_unshift($qualifying_offers, [
                'vendor_product_id'       => (int)$product->id,
                'vendor_id'               => (int)$product->vendor_id,
                'vendor_name'             => $product->vendor_name ?? '',
                'store_name'              => !empty($product->store_name) ? $product->store_name : ($product->vendor_name ?? 'Store'),
                'store_photo_url'         => $product->store_photo_url,
                'store_address'           => $product->store_address ?? '',
                'store_contact'           => $product->store_contact ?? '',
                'store_opening_time'      => $product->store_opening_time ?? null,
                'store_closing_time'      => $product->store_closing_time ?? null,
                'store_status'            => $store_status,
                'is_holiday'              => $is_holiday,
                'price'                   => $mrp,
                'sale_price'              => $sale_price,
                'discount_percent'        => $discount_percent,
                'stock'                   => $stock,
                'temporarily_unavailable' => $prod_temp_unavail,
                'low_stock_threshold'     => $threshold,
                'stock_state'             => $prod_stock_state,
                'is_available'            => $is_available,
                'availability'            => $availability,
                'distance_km'             => $product->distance_km,
                'distance_text'           => $product->distance_km !== null ? "{$product->distance_km} km away" : '',
                'fulfilment_option'       => $fulfilment_option,
                'is_current'              => true,
                'ranking_badge'           => null,
            ]);
        }
        // Deduplicate offers by vendor_id for store comparison (keep lowest price offer or current offer per vendor)
        $store_offers = [];
        foreach ($qualifying_offers as $o) {
            $vid = (int)$o['vendor_id'];
            if (!isset($store_offers[$vid])) {
                $store_offers[$vid] = $o;
            } else {
                if ($o['is_current'] || $o['sale_price'] < $store_offers[$vid]['sale_price']) {
                    $store_offers[$vid] = $o;
                }
            }
        }
        $qualifying_offers = array_values($store_offers);

        // Rank offers: in-stock first, lowest price first, shortest distance; out-of-stock at bottom
        usort($qualifying_offers, function ($a, $b) {
            if ($a['is_available'] !== $b['is_available']) {
                return $a['is_available'] ? -1 : 1;
            }
            if (($a['stock'] > 0) !== ($b['stock'] > 0)) {
                return ($a['stock'] > 0) ? -1 : 1;
            }
            if (abs($a['sale_price'] - $b['sale_price']) > 0.001) {
                return ($a['sale_price'] < $b['sale_price']) ? -1 : 1;
            }
            $da = $a['distance_km'] ?? 9999;
            $db = $b['distance_km'] ?? 9999;
            if (abs($da - $db) > 0.01) {
                return ($da < $db) ? -1 : 1;
            }
            return ($a['stock'] > $b['stock']) ? -1 : 1;
        });

        // Factual badge for strictly lowest price among available offers (when > 1 in-stock offer)
        $in_stock_offers = array_values(array_filter($qualifying_offers, function ($o) {
            return $o['stock'] > 0;
        }));
        if (count($in_stock_offers) > 1) {
            $min_price = min(array_column($in_stock_offers, 'sale_price'));
            $min_count = 0;
            foreach ($in_stock_offers as $iso) {
                if (abs($iso['sale_price'] - $min_price) < 0.01) {
                    $min_count++;
                }
            }
            if ($min_count === 1) {
                foreach ($qualifying_offers as &$off) {
                    if ($off['stock'] > 0 && abs($off['sale_price'] - $min_price) < 0.01) {
                        $off['ranking_badge'] = 'Lowest Price';
                    }
                }
                unset($off);
            }
        }

        // Rank alternatives for backward compatibility
        usort($qualifying_alts, function ($a, $b) {
            if (abs($a['sale_price'] - $b['sale_price']) > 0.001) {
                return ($a['sale_price'] < $b['sale_price']) ? -1 : 1;
            }
            if (abs($a['distance_km'] - $b['distance_km']) > 0.01) {
                return ($a['distance_km'] < $b['distance_km']) ? -1 : 1;
            }
            return ($a['stock'] > $b['stock']) ? -1 : 1;
        });

        $product->offers = $qualifying_offers;
        $product->alternatives = $qualifying_alts;

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
        $mode = strtolower(trim($this->input->get('mode', true) ?? 'products'));
        $category_param = $this->input->get('category_id', true);
        $brand_param = $this->input->get('brand', true);
        $unit_param = $this->input->get('unit', true);
        $availability = $this->input->get('availability', true) ?? ($this->input->get('in_stock', true) ? 'in_stock' : null);
        $fulfilment = $this->input->get('fulfilment', true);
        $min_price = $this->input->get('min_price', true);
        $max_price = $this->input->get('max_price', true);
        $vendor_id = $this->input->get('vendor_id', true);
        $sort_by = trim($this->input->get('sort_by', true) ?? ($mode === 'stores' ? 'distance_asc' : 'relevance'));
        $page = max(1, (int)($this->input->get('page', true) ?? 1));
        $limit = max(1, min(100, (int)($this->input->get('limit', true) ?? ($mode === 'stores' ? 12 : 10))));

        // Mode: STORES (Store-first results)
        if ($mode === 'stores') {
            $all_stores = $this->get_nearby_stores_data($cust_lat, $cust_lon, 100);
            $filtered_stores = [];
            foreach ($all_stores as $s) {
                if (!empty($search)) {
                    $match = false;
                    $q_lower = strtolower($search);
                    if (strpos(strtolower($s['store_name']), $q_lower) !== false) $match = true;
                    if (strpos(strtolower($s['name']), $q_lower) !== false) $match = true;
                    if (strpos(strtolower($s['address'] ?? ''), $q_lower) !== false) $match = true;
                    if (strpos(strtolower($s['category_summary'] ?? ''), $q_lower) !== false) $match = true;
                    if (!$match) continue;
                }

                if (!empty($category_param)) {
                    $cat_match = false;
                    $cat_ids = is_array($category_param) ? array_map('intval', $category_param) : array_filter(array_map('intval', explode(',', $category_param)));
                    if (!empty($cat_ids) && !empty($s['categories'])) {
                        foreach ($s['categories'] as $scat) {
                            if (in_array((int)$scat->id, $cat_ids)) {
                                $cat_match = true;
                                break;
                            }
                        }
                    }
                    if (!$cat_match && !empty($cat_ids)) continue;
                }

                if (!empty($availability) && ($availability === 'in_stock' || $availability === 'open')) {
                    if (!$s['is_open']) continue;
                }

                $filtered_stores[] = $s;
            }

            // Sort stores
            switch ($sort_by) {
                case 'name_asc':
                    usort($filtered_stores, function($a, $b) {
                        return strcasecmp($a['store_name'], $b['store_name']);
                    });
                    break;
                case 'name_desc':
                    usort($filtered_stores, function($a, $b) {
                        return strcasecmp($b['store_name'], $a['store_name']);
                    });
                    break;
                case 'availability':
                case 'open_first':
                    usort($filtered_stores, function($a, $b) {
                        if ($a['is_open'] !== $b['is_open']) {
                            return $a['is_open'] ? -1 : 1;
                        }
                        return ($a['distance_km'] < $b['distance_km']) ? -1 : 1;
                    });
                    break;
                case 'distance_asc':
                default:
                    usort($filtered_stores, function($a, $b) {
                        if ($a['distance_km'] === null) return 1;
                        if ($b['distance_km'] === null) return -1;
                        return ($a['distance_km'] < $b['distance_km']) ? -1 : 1;
                    });
                    break;
            }

            $total_records = count($filtered_stores);
            $offset = ($page - 1) * $limit;
            $paginated_stores = array_slice($filtered_stores, $offset, $limit);
            $total_pages = ceil($total_records / $limit);

            $this->load->helper('distance');
            $delivery_radius_km = get_delivery_radius_km();

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Stores searched successfully',
                    'data' => [
                        'mode' => 'stores',
                        'stores' => $paginated_stores,
                        'pagination' => [
                            'total_records' => (int)$total_records,
                            'current_page' => $page,
                            'limit' => $limit,
                            'total_pages' => (int)$total_pages
                        ],
                        'delivery_radius_km' => $delivery_radius_km,
                        'nearby_stores_count' => count($all_stores),
                        'no_nearby_vendors' => (count($all_stores) === 0)
                    ],
                    'nearby_vendors_count' => count($all_stores),
                    'no_nearby_vendors' => (count($all_stores) === 0)
                ]));
        }

        // Mode: PRODUCTS (Product-first results)
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

        // Parse unit filter (supports comma-separated list or array)
        $units = [];
        if (!empty($unit_param)) {
            if (is_array($unit_param)) {
                $units = array_map('trim', $unit_param);
            } else {
                $units = array_filter(array_map('trim', explode(',', $unit_param)));
            }
        }

        $discovery = $this->get_nearby_grouped_products($cust_lat, $cust_lon, [
            'search'       => $search,
            'category_ids' => $category_ids,
            'brands'       => $brands,
            'units'        => $units,
            'unit'         => !empty($units) ? null : $unit_param,
            'availability' => $availability,
            'fulfilment'   => $fulfilment,
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
                    'mode' => 'products',
                    'products' => $discovery['products'],
                    'pagination' => [
                        'total_records' => (int)$discovery['total_records'],
                        'current_page' => $discovery['page'],
                        'limit' => $discovery['limit'],
                        'total_pages' => $discovery['total_pages']
                    ],
                    'filters' => $discovery['filters'],
                    'delivery_radius_km' => $discovery['delivery_radius_km'],
                    'nearby_vendors_count' => (int)($discovery['nearby_vendors_count'] ?? 0),
                    'no_nearby_vendors' => (bool)($discovery['no_nearby_vendors'] ?? false)
                ],
                'nearby_vendors_count' => (int)($discovery['nearby_vendors_count'] ?? 0),
                'no_nearby_vendors' => (bool)($discovery['no_nearby_vendors'] ?? false)
            ]));
    }

    /**
     * Search suggestions across products, brands, and store names
     */
    public function search_suggestions()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $q = trim($this->input->get('q', true) ?? $this->input->get('search', true) ?? '');
        if (strlen($q) < 2) {
            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Query too short',
                    'data' => [
                        'products' => [],
                        'brands' => [],
                        'stores' => []
                    ]
                ]));
        }

        $this->resolve_customer_location($cust_lat, $cust_lon);
        $this->load->helper('distance');
        $delivery_radius_km = get_delivery_radius_km();

        // 1. Stores matching query
        $stores = [];
        $vendors = $this->db
            ->select('id, name, store_name, address, pincode, latitude, longitude, store_photo')
            ->where('role', 'vendor')
            ->where('is_active', 1)
            ->group_start()
                ->like('store_name', $q)
                ->or_like('name', $q)
            ->group_end()
            ->limit(10)
            ->get('users')
            ->result();

        foreach ($vendors as $v) {
            $dist = null;
            if ($cust_lat !== null && $cust_lon !== null && is_numeric($v->latitude) && is_numeric($v->longitude) && (float)$v->latitude != 0) {
                $dist = calculate_distance_km($cust_lat, $cust_lon, $v->latitude, $v->longitude);
                if ($dist > $delivery_radius_km) {
                    continue;
                }
            }
            $stores[] = [
                'id' => (int)$v->id,
                'name' => !empty($v->store_name) ? $v->store_name : $v->name,
                'address' => $v->address,
                'store_photo_url' => !empty($v->store_photo) ? base_url($v->store_photo) : null,
                'distance_km' => $dist !== null ? round((float)$dist, 1) : null
            ];
        }
        $stores = array_slice($stores, 0, 5);

        // Find qualifying vendor IDs within delivery radius
        $qualifying_vendor_ids = [];
        if ($cust_lat !== null && $cust_lon !== null) {
            $all_v = $this->db->select('id, latitude, longitude')
                ->where('role', 'vendor')
                ->where('is_active', 1)
                ->get('users')
                ->result();
            foreach ($all_v as $v) {
                if (is_numeric($v->latitude) && is_numeric($v->longitude) && (float)$v->latitude != 0) {
                    if (calculate_distance_km($cust_lat, $cust_lon, $v->latitude, $v->longitude) <= $delivery_radius_km) {
                        $qualifying_vendor_ids[] = (int)$v->id;
                    }
                }
            }
        }

        // 2. Brands matching query
        $brands = [];
        $this->db->select('brand, COUNT(id) as count')
            ->from('vendor_products')
            ->where('is_active', 1)
            ->where('stock >', 0)
            ->where('brand IS NOT NULL', null, false)
            ->where('TRIM(brand) !=', '')
            ->like('brand', $q)
            ->group_by('brand')
            ->order_by('count', 'DESC')
            ->limit(6);
        if (!empty($qualifying_vendor_ids)) {
            $this->db->where_in('vendor_id', $qualifying_vendor_ids);
        }
        $brand_rows = $this->db->get()->result();
        foreach ($brand_rows as $b) {
            $brands[] = [
                'name' => trim($b->brand),
                'count' => (int)$b->count
            ];
        }

        // 3. Products matching query
        $products = [];
        $this->db->select('vp.id, vp.product_name, vp.brand, vp.unit, vp.selling_price as sale_price, vp.mrp as price, vp.image, p.image as master_image, c.name as category_name, u.store_name, u.name as vendor_name')
            ->from('vendor_products vp')
            ->join('products p', 'p.id = vp.product_id', 'left')
            ->join('categories c', 'c.id = vp.category_id', 'left')
            ->join('users u', 'u.id = vp.vendor_id', 'left')
            ->where('vp.is_active', 1)
            ->where('vp.stock >', 0)
            ->group_start()
                ->like('vp.product_name', $q)
                ->or_like('vp.brand', $q)
                ->or_like('c.name', $q)
            ->group_end()
            ->order_by('vp.product_name', 'ASC')
            ->limit(12);
        if (!empty($qualifying_vendor_ids)) {
            $this->db->where_in('vp.vendor_id', $qualifying_vendor_ids);
        }
        $prod_rows = $this->db->get()->result();

        $seen_names = [];
        foreach ($prod_rows as $pr) {
            $clean_pname = strtolower(trim($pr->product_name));
            if (isset($seen_names[$clean_pname])) continue;
            $seen_names[$clean_pname] = true;

            $img = !empty($pr->image) ? $pr->image : $pr->master_image;
            $products[] = [
                'id' => (int)$pr->id,
                'name' => $pr->product_name,
                'brand' => $pr->brand,
                'unit' => $pr->unit,
                'sale_price' => (float)$pr->sale_price,
                'price' => (float)$pr->price,
                'store_name' => !empty($pr->store_name) ? $pr->store_name : $pr->vendor_name,
                'category_name' => $pr->category_name,
                'image_url' => !empty($img) ? base_url($img) : null
            ];
            if (count($products) >= 6) break;
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Search suggestions retrieved successfully',
                'data' => [
                    'products' => $products,
                    'brands' => $brands,
                    'stores' => $stores
                ]
            ]));
    }

    /**
     * Get recent searches for authenticated customer
     */
    public function get_recent_searches()
    {
        $this->ensureMethod('GET');
        $this->output->set_content_type('application/json');

        $user_id = $this->get_optional_user_id();

        if ($user_id) {
            $rows = $this->db
                ->select('search_query, MAX(created_at) as last_searched')
                ->where('user_id', $user_id)
                ->group_by('search_query')
                ->order_by('last_searched', 'DESC')
                ->limit(10)
                ->get('recent_searches')
                ->result();
            $queries = array_values(array_map(function($r) { return $r->search_query; }, $rows));
        } else {
            $queries = [];
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Recent searches retrieved successfully',
                'data' => $queries
            ]));
    }

    /**
     * Save recent search query
     */
    public function save_recent_search()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $raw_input = json_decode($this->input->raw_input_stream, true);
        if (!$raw_input) {
            $raw_input = json_decode(file_get_contents('php://input'), true);
        }
        $query = trim(
            $raw_input['query'] ?? 
            $raw_input['search_query'] ?? 
            $this->input->post('query', true) ?? 
            $this->input->post('search_query', true) ?? 
            $this->input->get('query', true) ?? 
            $this->input->get('q', true) ?? 
            ''
        );

        if (empty($query)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Query cannot be empty',
                    'data' => null
                ]));
        }

        $user_id = $this->get_optional_user_id();
        if ($user_id) {
            $this->db->where('user_id', $user_id)
                     ->where('LOWER(search_query)', strtolower($query))
                     ->delete('recent_searches');

            $this->db->insert('recent_searches', [
                'user_id' => $user_id,
                'search_query' => $query,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Recent search saved successfully',
                'data' => [
                    'query' => $query
                ]
            ]));
    }

    /**
     * Clear recent searches
     */
    public function clear_recent_searches()
    {
        $this->ensureMethod('POST');
        $this->output->set_content_type('application/json');

        $user_id = $this->get_optional_user_id();
        if ($user_id) {
            $this->db->where('user_id', $user_id)->delete('recent_searches');
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'code' => 200,
                'message' => 'Recent searches cleared successfully',
                'data' => null
            ]));
    }

    /**
     * Helper to retrieve authenticated user ID without terminating execution for guests
     */
    private function get_optional_user_id()
    {
        $authHeader = $this->input->get_request_header('Authorization', TRUE);
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            try {
                $decoded = \Firebase\JWT\JWT::decode($matches[1], new \Firebase\JWT\Key($this->jwt_secret, 'HS256'));
                if ($decoded && !empty($decoded->data->id)) {
                    return (int)$decoded->data->id;
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
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
        $this->db->select('vp.id, vp.vendor_id, vp.product_id, vp.variant_id, vp.product_name as name, vp.brand, vp.category_id, vp.unit, vp.mrp as price, vp.selling_price as sale_price, vp.stock, vp.temporarily_unavailable, vp.description, COALESCE(vp.image, p.image) as image, vp.is_active, c.name as category_name, u.name as vendor_name, u.store_name, u.address as store_address, u.contact_number as store_contact, u.opening_time as store_opening_time, u.closing_time as store_closing_time, u.store_photo, u.latitude as vendor_latitude, u.longitude as vendor_longitude, u.prep_time_minutes, u.slot_immediately_enabled, u.slot_later_enabled, u.slot_lunch_enabled, u.slot_dinner_enabled, u.store_status, u.is_holiday');
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

        // Fallback 2: fallback to first active vendor coordinates so guest visitors without GPS don't get 422 errors
        if (($lat === null || $lon === null) || !is_numeric($lat) || !is_numeric($lon)) {
            $default_vendor = $this->db->where('role', 'vendor')
                                       ->where('is_active', 1)
                                       ->where('latitude IS NOT NULL', null, false)
                                       ->where('longitude IS NOT NULL', null, false)
                                       ->order_by('id', 'ASC')
                                       ->get('users')
                                       ->row();
            if ($default_vendor && is_numeric($default_vendor->latitude) && is_numeric($default_vendor->longitude)) {
                $lat = (float)$default_vendor->latitude;
                $lon = (float)$default_vendor->longitude;
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

        // Count active vendors within delivery radius for this location (independent of filters)
        $all_active_vendors = $this->db->select('id, latitude, longitude')
            ->where('role', 'vendor')
            ->where('is_active', 1)
            ->where('latitude IS NOT NULL', null, false)
            ->where('longitude IS NOT NULL', null, false)
            ->get('users')
            ->result();

        $nearby_vendors_count = 0;
        foreach ($all_active_vendors as $v) {
            if (is_numeric($v->latitude) && is_numeric($v->longitude) && (float)$v->latitude != 0 && (float)$v->longitude != 0) {
                if (calculate_distance_km($cust_lat, $cust_lon, $v->latitude, $v->longitude) <= $delivery_radius_km) {
                    $nearby_vendors_count++;
                }
            }
        }

        // Query candidate vendor products
        $this->get_vendor_products_query();
        $this->db->where('vp.is_active', 1);
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

        // Filter: Unit
        if (!empty($options['units'])) {
            $this->db->where_in('vp.unit', $options['units']);
        } elseif (!empty($options['unit'])) {
            $this->db->where('vp.unit', $options['unit']);
        }

        // Filter: Fulfilment
        if (!empty($options['fulfilment'])) {
            $req_f = strtolower(trim($options['fulfilment']));
            if ($req_f === 'immediate') {
                $this->db->where('u.slot_immediately_enabled', 1);
            } elseif ($req_f === 'slots') {
                $this->db->group_start();
                $this->db->where('u.slot_lunch_enabled', 1);
                $this->db->or_where('u.slot_dinner_enabled', 1);
                $this->db->or_where('u.slot_later_enabled', 1);
                $this->db->group_end();
            }
        }

        // Filter: Vendor ID
        if (!empty($options['vendor_ids'])) {
            $this->db->where_in('vp.vendor_id', $options['vendor_ids']);
        } elseif (!empty($options['vendor_id'])) {
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
        $facet_units = [];
        $facet_stores = [];
        $facet_min_price = null;
        $facet_max_price = null;

        foreach ($rows as $row) {
            $dist = calculate_distance_km($cust_lat, $cust_lon, $row->vendor_latitude, $row->vendor_longitude);
            // Must be within configurable delivery radius
            if ($dist <= $delivery_radius_km) {
                // Compute factual store open status
                $is_store_open = true;
                if (isset($row->store_status) && strtolower((string)$row->store_status) === 'closed') {
                    $is_store_open = false;
                } elseif (isset($row->is_holiday) && (int)$row->is_holiday === 1) {
                    $is_store_open = false;
                } elseif (!empty($row->store_opening_time) && !empty($row->store_closing_time) && $row->store_opening_time !== '00:00:00' && $row->store_closing_time !== '00:00:00') {
                    $now_time = date('H:i:s');
                    $open_t = $row->store_opening_time;
                    $close_t = $row->store_closing_time;
                    if ($open_t <= $close_t) {
                        $is_store_open = ($now_time >= $open_t && $now_time <= $close_t);
                    } else {
                        $is_store_open = ($now_time >= $open_t || $now_time <= $close_t);
                    }
                }
                $row->is_store_open = $is_store_open;
                $stock_class = classify_stock_state($row->stock, $row->temporarily_unavailable ?? 0);
                $row->stock_status = $stock_class['stock_status'];
                $row->stock_status_label = $stock_class['stock_status_label'];
                $row->is_purchasable = $stock_class['is_purchasable'] && $is_store_open;
                $row->can_add_to_cart = $stock_class['can_add_to_cart'] && $is_store_open;
                $row->low_stock_threshold = $stock_class['low_stock_threshold'];
                $row->temporarily_unavailable = (int)($row->temporarily_unavailable ?? 0);
                $row->is_available = $row->is_purchasable;

                // Compute fulfilment label
                if (!empty($row->slot_immediately_enabled) && (int)$row->slot_immediately_enabled === 1) {
                    $row->fulfilment_option = 'Immediate Delivery';
                } elseif ((!empty($row->slot_lunch_enabled) && (int)$row->slot_lunch_enabled === 1) || (!empty($row->slot_dinner_enabled) && (int)$row->slot_dinner_enabled === 1) || (!empty($row->slot_later_enabled) && (int)$row->slot_later_enabled === 1)) {
                    $row->fulfilment_option = 'Scheduled Slots';
                } else {
                    $row->fulfilment_option = 'Standard Delivery';
                }

                // Filter: Availability
                if (!empty($options['availability']) && ($options['availability'] === 'in_stock' || $options['availability'] === '1')) {
                    if (!$row->is_available) continue;
                }

                // Filter: In stock only (Strictly available products for Home page, hiding Unavailable and Low Stock)
                if (!empty($options['in_stock_only'])) {
                    if ($row->stock_status !== 'available') continue;
                }

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

                if (!empty($row->unit)) {
                    $uname = trim($row->unit);
                    if (!isset($facet_units[$uname])) {
                        $facet_units[$uname] = [
                            'name' => $uname,
                            'count' => 0
                        ];
                    }
                    $facet_units[$uname]['count']++;
                }

                if (!empty($row->vendor_id)) {
                    $vid = (int)$row->vendor_id;
                    $sname = !empty($row->store_name) ? $row->store_name : ($row->vendor_name ?? 'Store');
                    if (!isset($facet_stores[$vid])) {
                        $facet_stores[$vid] = [
                            'id' => $vid,
                            'name' => $sname,
                            'count' => 0
                        ];
                    }
                    $facet_stores[$vid]['count']++;
                }

                $p = (float)$row->sale_price;
                if ($facet_min_price === null || $p < $facet_min_price) $facet_min_price = $p;
                if ($facet_max_price === null || $p > $facet_max_price) $facet_max_price = $p;
            }
        }

        // Group same product across multiple vendors:
        // Priority: product_id > normalized product name (do not split variants into separate cards)
        $groups = [];
        foreach ($qualifying_rows as $row) {
            if (!empty($row->product_id)) {
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

            // Winning offer is the #1 option (lowest price)
            $winner = $group_items[0];

            // Build variants summary for the winner vendor / product
            $variants_list = [];
            $seen_var_keys = [];
            foreach ($group_items as $gi) {
                if ((int)$gi->vendor_id === (int)$winner->vendor_id) {
                    $vk = !empty($gi->variant_id) ? 'vid_' . $gi->variant_id : 'id_' . $gi->id;
                    if (!isset($seen_var_keys[$vk])) {
                        $seen_var_keys[$vk] = true;
                        $v_stock_class = classify_stock_state($gi->stock, $gi->temporarily_unavailable ?? 0);
                        $variants_list[] = [
                            'id'                => (int)$gi->id,
                            'vendor_product_id' => (int)$gi->id,
                            'product_id'        => (int)$gi->product_id,
                            'variant_id'        => !empty($gi->variant_id) ? (int)$gi->variant_id : null,
                            'variant_name'      => !empty($gi->unit) ? $gi->unit : $gi->name,
                            'unit'              => $gi->unit ?? '',
                            'price'             => (float)$gi->price,
                            'sale_price'        => (float)$gi->sale_price,
                            'stock'             => (int)$gi->stock,
                            'temporarily_unavailable' => (int)($gi->temporarily_unavailable ?? 0),
                            'stock_status'      => $v_stock_class['stock_status'],
                            'stock_status_label'=> $v_stock_class['stock_status_label'],
                            'is_purchasable'    => $v_stock_class['is_purchasable'] && ($gi->is_store_open ?? true),
                            'can_add_to_cart'   => $v_stock_class['can_add_to_cart'] && ($gi->is_store_open ?? true),
                        ];
                    }
                }
            }
            $winner->variants = $variants_list;
            $winner->has_variants = count($variants_list) > 1;
            $winner->variant_count = count($variants_list);

            // Other qualifying vendors become alternative sellers (distinct stores)
            $alternatives = [];
            $seen_alt_vendors = [(int)$winner->vendor_id => true];
            for ($i = 1; $i < count($group_items); $i++) {
                $alt = $group_items[$i];
                $alt_vid = (int)$alt->vendor_id;
                if (!isset($seen_alt_vendors[$alt_vid])) {
                    $seen_alt_vendors[$alt_vid] = true;
                    $alt_stock_class = classify_stock_state($alt->stock, $alt->temporarily_unavailable ?? 0);
                    $alternatives[] = [
                        'vendor_product_id' => (int)$alt->id,
                        'vendor_id'         => $alt_vid,
                        'vendor_name'       => $alt->vendor_name,
                        'store_name'        => $alt->store_name,
                        'store_address'     => $alt->store_address,
                        'price'             => (float)$alt->price,
                        'sale_price'        => (float)$alt->sale_price,
                        'stock'             => (int)$alt->stock,
                        'temporarily_unavailable' => (int)($alt->temporarily_unavailable ?? 0),
                        'stock_status'      => $alt_stock_class['stock_status'],
                        'stock_status_label'=> $alt_stock_class['stock_status_label'],
                        'distance_km'       => (float)$alt->distance_km,
                        'fulfilment_option' => $alt->fulfilment_option ?? 'Standard Delivery',
                        'is_store_open'     => $alt->is_store_open ?? true,
                        'is_available'      => $alt_stock_class['is_purchasable'] && ($alt->is_store_open ?? true),
                        'is_purchasable'    => $alt_stock_class['is_purchasable'] && ($alt->is_store_open ?? true),
                        'can_add_to_cart'   => $alt_stock_class['can_add_to_cart'] && ($alt->is_store_open ?? true),
                    ];
                }
            }

            $winner->alternatives = $alternatives;
            $winner->total_vendors_count = count($seen_alt_vendors);
            $winner->delivery_radius_km = $delivery_radius_km;
            $winner->fulfilment_option = $winner->fulfilment_option ?? 'Standard Delivery';
            $winner->is_store_open = $winner->is_store_open ?? true;
            $winner->is_available = $winner->is_available ?? true;

            // Price range filter on winning offer
            if (isset($options['min_price']) && is_numeric($options['min_price'])) {
                if ((float)$winner->sale_price < (float)$options['min_price']) continue;
            }
            if (isset($options['max_price']) && is_numeric($options['max_price'])) {
                if ((float)$winner->sale_price > (float)$options['max_price']) continue;
            }

            $ranked_products[] = $winner;
        }

        // Helper to check if a product is unavailable / out of stock
        $is_unavail = function ($item) {
            return (
                empty($item->is_available) ||
                $item->stock_status === 'out_of_stock' ||
                $item->stock_status === 'temporarily_unavailable' ||
                (int)($item->stock ?? 0) <= 0
            );
        };

        // Group-level sorting (always push unavailable products to the very last)
        $sort_by = $options['sort_by'] ?? 'relevance';
        switch ($sort_by) {
            case 'price_low_to_high':
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
                    return ((float)$a->sale_price < (float)$b->sale_price) ? -1 : 1;
                });
                break;
            case 'price_high_to_low':
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
                    return ((float)$a->sale_price > (float)$b->sale_price) ? -1 : 1;
                });
                break;
            case 'availability':
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
                    if ((int)$a->stock !== (int)$b->stock) {
                        return ((int)$a->stock > (int)$b->stock) ? -1 : 1;
                    }
                    return ((float)$a->sale_price < (float)$b->sale_price) ? -1 : 1;
                });
                break;
            case 'distance_asc':
            case 'nearest':
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
                    return ((float)$a->distance_km < (float)$b->distance_km) ? -1 : 1;
                });
                break;
            case 'name_asc':
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
                    return strcasecmp($a->name, $b->name);
                });
                break;
            case 'name_desc':
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
                    return strcasecmp($b->name, $a->name);
                });
                break;
            case 'newest':
            case 'relevance':
            default:
                usort($ranked_products, function ($a, $b) use ($is_unavail) {
                    $a_u = $is_unavail($a);
                    $b_u = $is_unavail($b);
                    if ($a_u !== $b_u) return $a_u ? 1 : -1;
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
            'nearby_vendors_count' => $nearby_vendors_count,
            'no_nearby_vendors' => ($nearby_vendors_count === 0),
            'filters' => [
                'categories' => array_values($facet_categories),
                'brands' => array_values($facet_brands),
                'units' => array_values($facet_units),
                'stores' => array_values($facet_stores),
                'price_range' => [
                    'min' => $facet_min_price !== null ? (float)$facet_min_price : 0,
                    'max' => $facet_max_price !== null ? (float)$facet_max_price : 0
                ]
            ]
        ];
    }

    /**
     * Helper to fetch nearby stores based on customer location.
     * Uses Haversine distance, checks delivery radius, computes factual Open/Closed status,
     * categories offered, and distance.
     */
    public function get_nearby_stores_data($cust_lat, $cust_lon, $limit = 20)
    {
        $this->load->helper('distance');
        $delivery_radius_km = get_delivery_radius_km();

        $vendors = $this->db
            ->select('id, name, store_name, address, pincode, latitude, longitude, opening_time, closing_time, is_holiday, store_status, store_photo, contact_number')
            ->where('role', 'vendor')
            ->where('is_active', 1)
            ->where('latitude IS NOT NULL', null, false)
            ->where('longitude IS NOT NULL', null, false)
            ->get('users')
            ->result();

        $stores = [];
        $now_time = date('H:i:s');

        foreach ($vendors as $v) {
            if (!is_numeric($v->latitude) || !is_numeric($v->longitude) || ((float)$v->latitude == 0 && (float)$v->longitude == 0)) {
                continue;
            }

            $dist = ($cust_lat !== null && $cust_lon !== null)
                ? calculate_distance_km($cust_lat, $cust_lon, $v->latitude, $v->longitude)
                : null;

            // Only include stores within delivery radius if location is provided
            if ($dist !== null && $dist > $delivery_radius_km) {
                continue;
            }

            // Calculate factual Open/Closed status
            $is_open = true;
            $status_reason = 'Operating normally';
            if (isset($v->store_status) && strtolower((string)$v->store_status) === 'closed') {
                $is_open = false;
                $status_reason = 'Store is currently closed';
            } elseif (isset($v->is_holiday) && (int)$v->is_holiday === 1) {
                $is_open = false;
                $status_reason = 'On holiday';
            } elseif (!empty($v->opening_time) && !empty($v->closing_time) && $v->opening_time !== '00:00:00' && $v->closing_time !== '00:00:00') {
                $open_t = $v->opening_time;
                $close_t = $v->closing_time;
                if ($open_t <= $close_t) {
                    $is_open = ($now_time >= $open_t && $now_time <= $close_t);
                } else {
                    $is_open = ($now_time >= $open_t || $now_time <= $close_t);
                }
                if (!$is_open) {
                    $status_reason = 'Outside operating hours';
                }
            }

            // Format operating hours string
            $operating_hours = 'Hours not specified';
            if (!empty($v->opening_time) && !empty($v->closing_time)) {
                $formatted_open = date('h:i A', strtotime($v->opening_time));
                $formatted_close = date('h:i A', strtotime($v->closing_time));
                $operating_hours = "{$formatted_open} - {$formatted_close}";
            }

            // Fetch distinct categories that this store actually carries products for
            $cats_query = $this->db
                ->select('c.id, c.name, COUNT(vp.id) as product_count')
                ->from('vendor_products vp')
                ->join('categories c', 'c.id = vp.category_id')
                ->where('vp.vendor_id', (int)$v->id)
                ->where('vp.is_active', 1)
                ->where('vp.stock >', 0)
                ->group_by('c.id')
                ->order_by('product_count', 'DESC')
                ->get()
                ->result();

            $category_names = array_map(function($c) { return $c->name; }, $cats_query);
            $category_summary = !empty($category_names) ? implode(', ', array_slice($category_names, 0, 3)) : 'General Grocery';
            $total_products = array_reduce($cats_query, function($carry, $c) { return $carry + (int)$c->product_count; }, 0);

            $stores[] = [
                'id'                 => (int)$v->id,
                'name'               => $v->name,
                'store_name'         => !empty($v->store_name) ? $v->store_name : $v->name,
                'store_photo'        => $v->store_photo,
                'store_photo_url'    => !empty($v->store_photo) ? base_url($v->store_photo) : null,
                'address'            => $v->address,
                'pincode'            => $v->pincode,
                'contact_number'     => $v->contact_number,
                'latitude'           => (float)$v->latitude,
                'longitude'          => (float)$v->longitude,
                'distance_km'        => $dist !== null ? (float)$dist : null,
                'distance_text'      => $dist !== null ? ($dist < 1 ? round($dist * 1000) . ' m' : round($dist, 1) . ' km') : null,
                'store_status'       => $v->store_status ?? 'open',
                'is_holiday'         => (int)($v->is_holiday ?? 0),
                'opening_time'       => $v->opening_time,
                'closing_time'       => $v->closing_time,
                'operating_hours'    => $operating_hours,
                'is_open'            => $is_open,
                'status_label'       => $is_open ? 'Open' : 'Closed',
                'status_reason'      => $status_reason,
                'category_summary'   => $category_summary,
                'categories'         => $cats_query,
                'product_count'      => $total_products,
            ];
        }

        // Sort by distance ASC
        usort($stores, function($a, $b) {
            if ($a['distance_km'] === null && $b['distance_km'] === null) return 0;
            if ($a['distance_km'] === null) return 1;
            if ($b['distance_km'] === null) return -1;
            return ($a['distance_km'] < $b['distance_km']) ? -1 : 1;
        });

        if ($limit > 0) {
            $stores = array_slice($stores, 0, $limit);
        }

        return $stores;
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

        // Get order items with vendor/store details
        $items = $this->db
            ->select('oi.id, oi.product_id, oi.product_name, oi.product_image, oi.price, oi.gst_percent, oi.gst_amount, oi.quantity, oi.subtotal, oi.vendor_id, u.store_name, u.name as vendor_name')
            ->from('order_items oi')
            ->join('users u', 'u.id = oi.vendor_id', 'left')
            ->where('oi.order_id', $order_id)
            ->get()
            ->result_array();

        $store_map = [];
        foreach ($items as &$item) {
            $item['product_image'] = $this->get_product_image_url($item['product_image']);
            $item['id'] = (int) $item['id'];
            $item['product_id'] = (int) $item['product_id'];
            $item['vendor_id'] = (int) $item['vendor_id'];
            $item['store_name'] = !empty($item['store_name']) ? $item['store_name'] : (!empty($item['vendor_name']) ? $item['vendor_name'] : 'Store #' . $item['vendor_id']);
            $item['vendor_name'] = !empty($item['vendor_name']) ? $item['vendor_name'] : $item['store_name'];
            $item['price'] = (float) $item['price'];
            $item['gst_percent'] = (float) $item['gst_percent'];
            $item['gst_amount'] = (float) $item['gst_amount'];
            $item['quantity'] = (int) $item['quantity'];
            $item['subtotal'] = (float) $item['subtotal'];
            $item['total_amount'] = round($item['subtotal'] + $item['gst_amount'], 2);

            $vid = $item['vendor_id'];
            if (!isset($store_map[$vid])) {
                $store_map[$vid] = [
                    'vendor_id'   => $vid,
                    'store_name'  => $item['store_name'],
                    'vendor_name' => $item['vendor_name'],
                    'items'       => [],
                    'items_count' => 0,
                    'subtotal'    => 0.00
                ];
            }
            $store_map[$vid]['items'][] = $item;
            $store_map[$vid]['items_count'] += $item['quantity'];
            $store_map[$vid]['subtotal'] = round($store_map[$vid]['subtotal'] + $item['total_amount'], 2);
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
            'stores'             => array_values($store_map),
            'stores_count'       => count($store_map),
            'is_multi_store'     => count($store_map) > 1,
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
        if (!$this->db->field_exists('hidden_by_customer', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `hidden_by_customer` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`");
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

        $this->load->helper('invoice');

        // Allow on-screen HTML viewing if ?view=html or ?format=html
        if ($this->input->get('view') === 'html' || $this->input->get('format') === 'html') {
            $html = $this->generate_order_invoice_html($order_id);
            if (!$html) {
                show_404();
                return;
            }
            $this->output
                ->set_content_type('text/html')
                ->set_output($html);
            return;
        }

        $dir = FCPATH . 'assets/uploads/invoices/';
        $filepath = $dir . 'invoice_' . $order_id . '.pdf';

        if ($this->input->get('regenerate') || !file_exists($filepath)) {
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
        $this->load->helper('invoice');
        return render_order_invoice_html($order_id, $vendor_id);
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

    /**
     * Get live published legal page content (Terms, Privacy, Refund)
     * If no customized version has been published, falls back to the shipped default.
     *
     * @param string $slug
     */
    public function legal_page($slug = '')
    {
        $this->ensureMethod('GET');
        $this->load->helper('legal');
        $slug = strtolower(trim($slug));

        if (!in_array($slug, get_all_legal_page_slugs())) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'code'    => 404,
                    'message' => 'Legal page not found.',
                    'data'    => null,
                ]));
        }

        $page = resolve_legal_page($slug);

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'code'    => 200,
                'message' => 'Legal page retrieved successfully.',
                'data'    => [
                    'slug'         => $page['slug'],
                    'title'        => $page['title'],
                    'content'      => $page['content'],
                    'status'       => $page['status'],
                    'is_default'   => $page['is_default'],
                    'published_at' => $page['published_at'],
                    'updated_at'   => $page['updated_at'],
                    'last_updated' => $page['updated_at'] ? date('d M Y', strtotime($page['updated_at'])) : date('d M Y'),
                ],
            ]));
    }

    /**
     * Real-time Vendor Dashboard Analytics & Metrics
     */
    public function get_vendor_dashboard()
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
                'status'  => false,
                'code'    => 401,
                'message' => 'Unauthorized',
                'data'    => null
            ]));
        }

        $vendor_id = (int)$decoded->data->id;

        // 1. Stats - Today orders & sales
        $today_start = date('Y-m-d 00:00:00');
        $today_end   = date('Y-m-d 23:59:59');

        $today_res = $this->db->query("
            SELECT COUNT(DISTINCT o.id) as order_count,
                   COALESCE(SUM(oi.quantity * oi.price + COALESCE(oi.gst_amount, 0)), 0) as total_sales
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE oi.vendor_id = ?
              AND o.created_at >= ? AND o.created_at <= ?
        ", [$vendor_id, $today_start, $today_end])->row();

        $today_orders = $today_res ? (int)$today_res->order_count : 0;
        $today_sales  = $today_res ? round((float)$today_res->total_sales, 2) : 0.00;

        // Yesterday orders & sales
        $yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday_end   = date('Y-m-d 23:59:59', strtotime('-1 day'));

        $yesterday_res = $this->db->query("
            SELECT COUNT(DISTINCT o.id) as order_count,
                   COALESCE(SUM(oi.quantity * oi.price + COALESCE(oi.gst_amount, 0)), 0) as total_sales
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE oi.vendor_id = ?
              AND o.created_at >= ? AND o.created_at <= ?
        ", [$vendor_id, $yesterday_start, $yesterday_end])->row();

        $yesterday_orders = $yesterday_res ? (int)$yesterday_res->order_count : 0;
        $yesterday_sales  = $yesterday_res ? round((float)$yesterday_res->total_sales, 2) : 0.00;

        // Orders % change
        if ($yesterday_orders > 0) {
            $orders_change = round((($today_orders - $yesterday_orders) / $yesterday_orders) * 100);
        } else {
            $orders_change = $today_orders > 0 ? 100 : 0;
        }
        $orders_dir = $orders_change >= 0 ? 'up' : 'down';

        // Sales % change
        if ($yesterday_sales > 0) {
            $sales_change = round((($today_sales - $yesterday_sales) / $yesterday_sales) * 100);
        } else {
            $sales_change = $today_sales > 0 ? 100 : 0;
        }
        $sales_dir = $sales_change >= 0 ? 'up' : 'down';

        // Pending orders
        $pending_res = $this->db->query("
            SELECT COUNT(DISTINCT o.id) as pending_count
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            WHERE oi.vendor_id = ?
              AND o.status IN ('pending', 'new', 'confirmed', 'accepted', 'packed')
        ", [$vendor_id])->row();
        $pending_orders = $pending_res ? (int)$pending_res->pending_count : 0;

        // 2. Sales Data (Past 7 days)
        $sales_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $day_date  = date('Y-m-d', strtotime("-$i days"));
            $day_label = date('D', strtotime($day_date));
            $d_start   = "$day_date 00:00:00";
            $d_end     = "$day_date 23:59:59";

            $d_res = $this->db->query("
                SELECT COALESCE(SUM(oi.quantity * oi.price + COALESCE(oi.gst_amount, 0)), 0) as day_sales
                FROM orders o
                JOIN order_items oi ON oi.order_id = o.id
                WHERE oi.vendor_id = ?
                  AND o.created_at >= ? AND o.created_at <= ?
            ", [$vendor_id, $d_start, $d_end])->row();

            $sales_data[] = [
                'day'   => $day_label,
                'date'  => $day_date,
                'sales' => $d_res ? round((float)$d_res->day_sales, 2) : 0.00
            ];
        }

        // 3. Recent Orders (Latest 5)
        $recent_rows = $this->db->query("
            SELECT o.id, o.order_number, o.status, o.created_at,
                   u.name as customer_name,
                   COUNT(oi.id) as item_count,
                   SUM(oi.quantity * oi.price + COALESCE(oi.gst_amount, 0)) as vendor_total
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            LEFT JOIN users u ON u.id = o.user_id
            WHERE oi.vendor_id = ?
            GROUP BY o.id
            ORDER BY o.id DESC
            LIMIT 5
        ", [$vendor_id])->result();

        $recent_orders = [];
        $now = time();
        foreach ($recent_rows as $ro) {
            $created_ts = strtotime($ro->created_at);
            $time_diff = $now - $created_ts;

            if ($time_diff < 3600) {
                $time_str = max(1, round($time_diff / 60)) . ' mins ago';
            } elseif (date('Y-m-d') === date('Y-m-d', $created_ts)) {
                $time_str = 'Today, ' . date('h:i A', $created_ts);
            } elseif (date('Y-m-d', strtotime('-1 day')) === date('Y-m-d', $created_ts)) {
                $time_str = 'Yesterday, ' . date('h:i A', $created_ts);
            } else {
                $time_str = date('d M, h:i A', $created_ts);
            }

            $display_status = $ro->status === 'confirmed' ? 'accepted' : $ro->status;

            $recent_orders[] = [
                'id'           => (int)$ro->id,
                'orderNumber'  => $ro->order_number,
                'customerName' => $ro->customer_name ?: 'Customer',
                'items'        => (int)$ro->item_count,
                'amount'       => round((float)$ro->vendor_total, 2),
                'status'       => $display_status,
                'time'         => $time_str,
                'rawDate'      => $ro->created_at
            ];
        }

        // 4. Top Products (By Units Sold & Revenue)
        $top_rows = $this->db->query("
            SELECT oi.product_name,
                   SUM(oi.quantity) as sold,
                   SUM(oi.quantity * oi.price + COALESCE(oi.gst_amount, 0)) as revenue,
                   c.name as category_name
            FROM order_items oi
            LEFT JOIN vendor_products vp ON vp.id = oi.product_id
            LEFT JOIN categories c ON c.id = vp.category_id
            WHERE oi.vendor_id = ?
            GROUP BY oi.product_name
            ORDER BY sold DESC, revenue DESC
            LIMIT 5
        ", [$vendor_id])->result();

        $top_products = [];
        $p_idx = 1;
        foreach ($top_rows as $tp) {
            $top_products[] = [
                'id'       => $p_idx++,
                'name'     => $tp->product_name,
                'category' => $tp->category_name ?: 'General',
                'sold'     => (int)$tp->sold,
                'revenue'  => round((float)$tp->revenue, 2)
            ];
        }

        // If fewer than 5 products, supplement with active vendor products
        if (count($top_products) < 5) {
            $existing_names = array_column($top_products, 'name');
            $this->db->select('vp.product_name, c.name as category_name');
            $this->db->from('vendor_products vp');
            $this->db->join('categories c', 'c.id = vp.category_id', 'left');
            $this->db->where('vp.vendor_id', $vendor_id);
            $this->db->where('vp.is_active', 1);
            if (!empty($existing_names)) {
                $this->db->where_not_in('vp.product_name', $existing_names);
            }
            $this->db->limit(5 - count($top_products));
            $other_prods = $this->db->get()->result();

            foreach ($other_prods as $op) {
                $top_products[] = [
                    'id'       => $p_idx++,
                    'name'     => $op->product_name,
                    'category' => $op->category_name ?: 'General',
                    'sold'     => 0,
                    'revenue'  => 0.00
                ];
            }
        }

        $response_data = [
            'stats' => [
                'todayOrders'       => $today_orders,
                'todayOrdersChange' => abs($orders_change),
                'todayOrdersDir'    => $orders_dir,
                'todaySales'        => $today_sales,
                'todaySalesChange'  => abs($sales_change),
                'todaySalesDir'     => $sales_dir,
                'pendingOrders'     => $pending_orders,
                'customerRating'    => 4.8,
                'notifications'     => $pending_orders
            ],
            'salesData'    => $sales_data,
            'recentOrders' => $recent_orders,
            'topProducts'  => $top_products
        ];

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => true,
                'code'    => 200,
                'message' => 'Dashboard data retrieved successfully.',
                'data'    => $response_data
            ]));
    }
}