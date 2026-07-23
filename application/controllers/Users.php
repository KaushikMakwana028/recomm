<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

require_once FCPATH . 'vendor/autoload.php';

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('security');
    }

    // Main index page - loads view only
    public function index()
    {
        $this->setPageTitle('Users Management');

        // Get statistics for cards
        $data['total_users'] = $this->gm->countRows('users', ['role' => 'user']);
        $data['total_vendors'] = $this->gm->countRows('users', ['role' => 'vendor']);
        $data['total_admins'] = $this->gm->countRows('users', ['role' => 'admin']);
        $data['total_all'] = $this->gm->countRows('users');

        $this->loadView('users/index', $data);
    }

    // AJAX: Get users with pagination, search, and filter
    public function get_users()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get inputs with sanitization
        $search = trim($this->input->post('search', true));
        $role_filter = trim($this->input->post('role', true));
        $page = (int)$this->input->post('page', true) ?: 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Build query
        $this->db->select('*');
        $this->db->from('users');

        // Apply role filter
        if (!empty($role_filter) && in_array($role_filter, ['admin', 'vendor', 'user'])) {
            $this->db->where('role', $role_filter);
        }

        // Apply search filter
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('mobile', $search);
            $this->db->group_end();
        }

        // Get total records for pagination
        $total_records = $this->db->count_all_results('', false); // false = don't reset query
        $total_pages = ceil($total_records / $limit);

        // Get paginated results
        $users = $this->db
            ->order_by('id', 'ESC')
            ->limit($limit, $offset)
            ->get()
            ->result();

        // Generate HTML
        $html = $this->generate_users_html($users, $page);

        // Generate pagination
        $pagination = $this->generate_pagination($page, $total_pages);

        $response = [
            'status' => true,
            'html' => $html,
            'pagination' => $pagination,
            'total_records' => $total_records,
            'current_page' => $page,
            'total_pages' => $total_pages
        ];

        echo json_encode($response);
    }

    // Generate users table HTML
    private function generate_users_html($users, $current_page = 1)
    {
        if (empty($users)) {
            return '
        <tr>
            <td colspan="8">
                <div class="rc-empty">
                    <i class="fas fa-users-slash"></i>
                    <p class="mb-0">No users found</p>
                </div>
            </td>
        </tr>';
        }

        $index = (($current_page - 1) * 10) + 1;
        $html = '';

        foreach ($users as $user) {

            // Avatar
            if (!empty($user->photo) && file_exists(FCPATH . 'uploads/users/' . $user->photo)) {

                $avatar = '
            <div class="rc-avatar role-' . $user->role . '">
                <img src="' . base_url('uploads/users/' . $user->photo) . '">
            </div>';
            } else {

                $avatar = '
            <div class="rc-avatar role-' . $user->role . '">
                ' . strtoupper(substr($user->name, 0, 1)) . '
            </div>';
            }

            // Store
            if (!empty($user->store_name)) {

                $store = '<span class="rc-store-badge">'
                    . htmlspecialchars($user->store_name)
                    . '</span>';
            } else {

                $store = '<span class="rc-no-store">—</span>';
            }

            // Role

            switch ($user->role) {

                case 'admin':
                    $roleIcon = 'fa-user-shield';
                    break;

                case 'vendor':
                    $roleIcon = 'fa-store';
                    break;

                default:
                    $roleIcon = 'fa-user';
            }

            $roleBadge = '
        <span class="rc-badge role-' . $user->role . '">
            <i class="fas ' . $roleIcon . '"></i>
            ' . ucfirst($user->role) . '
        </span>';

            // Status

            $checked = (!empty($user->is_active) && $user->is_active == 1)
                ? 'checked'
                : '';

            $disabled = ($user->id == $this->session->userdata('user_id')) ? 'disabled' : '';

            $status = '
        <label class="rc-toggle">
            <input
                type="checkbox"
                class="status-toggle"
                data-id="' . $user->id . '"
                ' . $checked . '
                ' . $disabled . '
            >
            <span class="rc-slider"></span>
        </label>';

            // Actions

            $actions = '

        <div class="rc-actions">

            <a href="' . base_url('users/view/' . $user->id) . '" class="rc-btn-icon view">
                <i class="fas fa-eye"></i>
            </a>

            <a href="' . base_url('users/edit/' . $user->id) . '" class="rc-btn-icon edit">
                <i class="fas fa-pen"></i>
            </a>';

            if ($user->role == 'vendor') {

                $actions .= '
            <a href="' . base_url('users/store/' . $user->id) . '" class="rc-btn-icon store">
                <i class="fas fa-store"></i>
            </a>';
            }

            $actions .= '

            <button
                type="button"
                class="rc-btn-icon delete btn-delete"
                data-id="' . $user->id . '"
                data-name="' . htmlspecialchars($user->name) . '">
                <i class="fas fa-trash"></i>
            </button>

        </div>';

            $joined = !empty($user->created_on)
                ? date('d M Y', strtotime($user->created_on))
                : '-';

            $html .= '

        <tr>

            <td class="rc-cell-id">' . $index++ . '</td>

            <td class="rc-cell-user">

                <div class="rc-user-cell">

                    ' . $avatar . '

                    <h6>' . htmlspecialchars($user->name) . '</h6>

                </div>

            </td>

            <td>

                <div class="rc-contact-cell">

                    <span>
                        <i class="fas fa-phone"></i>
                        ' . htmlspecialchars($user->mobile) . '
                    </span>

                    <span>
                        <i class="fas fa-envelope"></i>
                        ' . htmlspecialchars($user->email) . '
                    </span>

                </div>

            </td>

            <td>' . $store . '</td>

            <td>' . $roleBadge . '</td>

            <td>' . $status . '</td>

            <td class="rc-date-cell">
                <i class="fas fa-calendar"></i>
                ' . $joined . '
            </td>

            <td class="rc-cell-actions">
                ' . $actions . '
            </td>

        </tr>';
        }

        return $html;
    }

    // Generate pagination HTML (3 buttons + prev/next)
    private function generate_pagination($current_page, $total_pages)
    {
        if ($total_pages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Users pagination">';
        $html .= '<ul class="pagination pagination-sm justify-content-center mb-0">';

        // Previous button
        if ($current_page > 1) {
            $html .= '
                <li class="page-item">
                    <a class="page-link" href="#" data-page="' . ($current_page - 1) . '">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            ';
        }

        // Calculate range for 3 buttons
        $start_page = max(1, $current_page - 1);
        $end_page = min($total_pages, $start_page + 2);

        // Adjust start if we're near the end
        if ($end_page - $start_page < 2) {
            $start_page = max(1, $end_page - 2);
        }

        // Page buttons (max 3)
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $i == $current_page ? 'active' : '';
            $html .= '
                <li class="page-item ' . $active . '">
                    <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
                </li>
            ';
        }

        // Show last page if not visible
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $html .= '
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                ';
            }
            $html .= '
                <li class="page-item">
                    <a class="page-link" href="#" data-page="' . $total_pages . '">' . $total_pages . '</a>
                </li>
            ';
        }

        // Next button
        if ($current_page < $total_pages) {
            $html .= '
                <li class="page-item">
                    <a class="page-link" href="#" data-page="' . ($current_page + 1) . '">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            ';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
    public function add()
    {
        if ($this->input->method() == 'post') {
            $role = $this->post('role');

            // Common validation
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric|exact_length[10]|is_unique[users.mobile]');
            $this->form_validation->set_rules('email', 'Email', 'valid_email');
            $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,vendor,user]');

            // Role specific validation
            if ($role == 'vendor') {
                $this->form_validation->set_rules('store_name', 'Store Name', 'required|trim');
            }

            if ($this->form_validation->run()) {
                $data = [
                    'name' => $this->post('name'),
                    'mobile' => $this->post('mobile'),
                    'email' => $this->post('email') ?: NULL,
                    'store_name' => $role == 'vendor' ? $this->post('store_name') : NULL,
                    'address' => $role == 'user' ? $this->post('address') : NULL,
                    'role' => $role,
                    'is_active' => $this->post('is_active') ? 1 : 0,
                    'created_on' => date('Y-m-d H:i:s')
                ];

                if ($this->gm->insert('users', $data)) {
                    $this->setMessage('success', 'User added successfully');
                    redirect('users');
                } else {
                    $this->setMessage('danger', 'Failed to add user');
                }
            }
        }

        $this->setPageTitle('Add User');
        $this->loadView('users/add');
    }

    public function edit($id)
    {
        $data['user'] = $this->gm->getById('users', $id);

        if (!$data['user']) {
            $this->setMessage('danger', 'User not found');
            redirect('users');
        }

        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'valid_email');
            $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,vendor,user]');

            if ($this->form_validation->run()) {
                $update_data = [
                    'name' => $this->post('name'),
                    'email' => $this->post('email') ?: NULL,
                    'role' => $this->post('role'),
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

                if (!empty($_FILES['profile_image']['name'])) {
                    $profile_image = $this->uploadProfileImage('profile_image');
                    if ($profile_image) {
                        if ($data['user']->profile_image && file_exists($data['user']->profile_image)) {
                            unlink($data['user']->profile_image);
                        }
                        $update_data['profile_image'] = $profile_image;
                    }
                }

                if ($this->gm->update('users', $update_data, ['id' => $id])) {
                    $this->setMessage('success', 'User updated successfully');
                    redirect('users');
                } else {
                    $this->setMessage('danger', 'Failed to update user');
                }
            }
        }

        $this->setPageTitle('Edit User');
        $this->loadView('users/edit', $data);
    }

    public function delete($id)
    {
        if ($id == $this->session->userdata('admin_id')) {
            $this->setMessage('danger', 'You cannot delete your own account');
            redirect('users');
            return;
        }

        $user = $this->gm->getById('users', $id);

        if ($user) {
            if ($user->profile_image && file_exists($user->profile_image)) {
                unlink($user->profile_image);
            }
            if ($this->gm->delete('users', ['id' => $id])) {
                $this->setMessage('success', 'User deleted successfully');
            } else {
                $this->setMessage('danger', 'Failed to delete user');
            }
        } else {
            $this->setMessage('danger', 'User not found');
        }

        redirect('users');
    }

    public function toggle_status()
    {
        if ($this->isAjax()) {
            $id = $this->post('id');
            $status = $this->post('status');

            if ($id == $this->session->userdata('admin_id')) {
                $this->jsonResponse(false, 'You cannot deactivate your own account');
                return;
            }

            if ($this->gm->update('users', ['is_active' => $status], ['id' => $id])) {
                $this->jsonResponse(true, 'Status updated successfully');
            } else {
                $this->jsonResponse(false, 'Failed to update status');
            }
        }
    }

    private function uploadProfileImage($field_name)
    {
        $upload_path = './assets/uploads/users/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            return 'assets/uploads/users/' . $this->upload->data()['file_name'];
        }
        return false;
    }

    public function view($id)
    {
        $data['user'] = $this->gm->getById('users', $id);

        if (!$data['user']) {
            $this->setMessage('danger', 'User not found');
            redirect('users');
        }

        if ($data['user']->role == 'vendor') {
            $data['vendor_products'] = $this->db
                ->select('vp.*, p.image as admin_image, c.name as category_name')
                ->from('vendor_products vp')
                ->join('products p', 'p.id = vp.product_id', 'left')
                ->join('categories c', 'c.id = vp.category_id', 'left')
                ->where('vp.vendor_id', $id)
                ->get()
                ->result();
        }

        $this->setPageTitle('User Details');
        $this->loadView('users/view', $data);
    }

    public function login_as_vendor($vendor_id)
    {
        // Admin must be logged-in
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('login');
        }

        $vendor = $this->gm->getOne('users', [
            'id' => (int)$vendor_id,
            'role' => 'vendor',
            'is_active' => 1
        ]);

        if (!$vendor) {
            $this->setMessage('danger', 'Vendor not found / inactive');
            redirect('users?role=vendor');
        }

        $jwt_secret = 'a0d5f8e9c2b7a6d1c4e3f98b19d2a4f6c9f7a31bc9e2d6f81d845a47b8f92c4e';

        $payload = [
            'iss' => base_url(),
            'iat' => time(),
            'exp' => time() + (10 * 365 * 24 * 60 * 60),
            'data' => [
                'id' => (int)$vendor->id,
                'name' => $vendor->name,
                'email' => $vendor->email ?? '',
                'mobile' => $vendor->mobile,
                'role' => 'vendor',
                'is_admin' => true,
                'admin_id' => (int)$this->session->userdata('admin_id')
            ]
        ];

        $token = JWT::encode($payload, $jwt_secret, 'HS256');

        // User object for query param
        $user = [
            'id' => (string)$vendor->id,
            'name' => $vendor->name,
            'mobile' => $vendor->mobile,
            'email' => $vendor->email ?? '',
            'shop_name' => $vendor->store_name ?? '',
            'role' => 'vendor'
        ];

        $vendor_panel_url = 'https://vendor.recomm.in/login';

        $query = http_build_query([
            'token' => $token,
            'user' => json_encode($user)
        ]);

        redirect($vendor_panel_url . '?' . $query);
    }
}
