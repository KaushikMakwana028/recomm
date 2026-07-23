<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // List all categories
    public function index()
    {
        $this->setPageTitle('Categories');
        $this->loadView('categories/index');
    }

    // AJAX: Get categories with pagination and search
    public function get_categories()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search = $this->input->post('search', true);
        $page = (int)$this->input->post('page', true) ?: 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Build where clause for search
        $where = [];
        if (!empty($search)) {
            $where = [
                'name LIKE' => '%' . $this->db->escape_like_str($search) . '%'
            ];
        }

        // Get total records
        $total_records = $this->gm->countRows('categories', $where);
        $total_pages = ceil($total_records / $limit);

        // Get categories
        $categories = $this->db
            ->select('*')
            ->from('categories')
            ->where($where)
            ->order_by('id', 'asc')
            ->limit($limit, $offset)
            ->get()
            ->result();

        // Generate pagination
        $pagination = $this->generate_pagination($page, $total_pages);

        // Generate HTML
        $html = $this->generate_categories_html($categories, $offset);

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

    // Generate categories table HTML
    private function generate_categories_html($categories, $offset = 0)
    {
        if (empty($categories)) {
            return '
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No categories found</p>
                        <a href="' . base_url('categories/add') . '" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-2"></i>Add First Category
                        </a>
                    </td>
                </tr>
            ';
        }

        $html = '';
        foreach ($categories as $index => $category) {
            $serial = $offset + $index + 1;
            $image_html = '';
            if (!empty($category->image)) {
                $image_html = '
                    <img src="' . base_url(($category->image)) . '" 
                         alt="' . ($category->name) . '" 
                         class="img-thumbnail" 
                         style="width: 60px; height: 60px; object-fit: cover;">
                ';
            } else {
                $image_html = '
                    <div style="width: 60px; height: 60px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                ';
            }

            $description = '-';
            if (!empty($category->description)) {
                $desc = strlen($category->description) > 50
                    ? substr($category->description, 0, 50) . '...'
                    : $category->description;
                $description = ($desc);
            }

            $checked = $category->is_active == 1 ? 'checked' : '';

            $html .= '
                <tr>
                    <td><strong>' . $serial . '</strong></td>
                    <td>' . $image_html . '</td>
                    <td>
                        <strong>' . ($category->name) . '</strong>
                        <br><small class="text-muted">' . ($category->slug) . '</small>
                    </td>
                    <td>' . $description . '</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle" 
                                   type="checkbox" 
                                   role="switch"
                                   data-id="' . ($category->id) . '" 
                                   aria-label="Toggle status for ' . ($category->name) . '"
                                   ' . $checked . '>
                        </div>
                    </td>
                    <td>
                        <small>' . date('d M Y', strtotime($category->created_on)) . '</small>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="' . base_url('categories/edit/' . ($category->id)) . '" 
                               class="rc-btn-icon edit" 
                               title="Edit"
                               aria-label="Edit ' . ($category->name) . '">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="rc-btn-icon delete btn-delete" 
                                    data-id="' . ($category->id) . '"
                                    data-name="' . ($category->name) . '"
                                    title="Delete"
                                    aria-label="Delete ' . ($category->name) . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            ';
        }

        return $html;
    }

    // Generate pagination HTML (3 buttons + prev/next)
    private function generate_pagination($current_page, $total_pages)
    {
        if ($total_pages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Categories pagination">';
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

    // Add new category
    public function add()
    {
        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

            if ($this->form_validation->run()) {
                $slug = $this->generateSlug($this->post('name'));

                // Check if slug exists
                if ($this->gm->exists('categories', ['slug' => $slug])) {
                    $slug = $slug . '-' . time();
                }

                // Handle image upload
                $image = '';
                if (!empty($_FILES['image']['name'])) {
                    $upload_path = './assets/uploads/categories/';

                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0777, true);
                    }

                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048;
                    $config['encrypt_name'] = TRUE;

                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('image')) {
                        $upload_data = $this->upload->data();
                        $image = 'assets/uploads/categories/' . $upload_data['file_name'];
                    } else {
                        $this->setMessage('danger', $this->upload->display_errors());
                        redirect('categories/add');
                        return;
                    }
                }

                $data = [
                    'name' => $this->post('name'),
                    'slug' => $slug,
                    'description' => $this->post('description'),
                    'image' => $image,
                    'is_active' => $this->post('is_active') ? 1 : 0,
                    'created_on' => date('Y-m-d H:i:s')
                ];

                if ($this->gm->insert('categories', $data)) {
                    $this->setMessage('success', 'Category added successfully');
                    redirect('categories');
                } else {
                    $this->setMessage('danger', 'Failed to add category');
                }
            }
        }

        $this->setPageTitle('Add Category');
        $this->loadView('categories/add');
    }

    // Edit category
    public function edit($id)
    {
        $data['category'] = $this->gm->getById('categories', $id);

        if (!$data['category']) {
            $this->setMessage('danger', 'Category not found');
            redirect('categories');
        }

        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

            if ($this->form_validation->run()) {
                $update_data = [
                    'name' => $this->post('name'),
                    'description' => $this->post('description'),
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

                // Handle image upload
                if (!empty($_FILES['image']['name'])) {
                    $upload_path = './assets/uploads/categories/';

                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048;
                    $config['encrypt_name'] = TRUE;

                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('image')) {
                        // Delete old image
                        if ($data['category']->image && file_exists($data['category']->image)) {
                            unlink($data['category']->image);
                        }

                        $upload_data = $this->upload->data();
                        $update_data['image'] = 'assets/uploads/categories/' . $upload_data['file_name'];
                    }
                }

                if ($this->gm->update('categories', $update_data, ['id' => $id])) {
                    $this->setMessage('success', 'Category updated successfully');
                    redirect('categories');
                } else {
                    $this->setMessage('danger', 'Failed to update category');
                }
            }
        }

        $this->setPageTitle('Edit Category');
        $this->loadView('categories/edit', $data);
    }

    // Delete category
    public function delete($id)
    {
        $category = $this->gm->getById('categories', $id);

        if ($category) {
            // Delete image file
            if ($category->image && file_exists($category->image)) {
                unlink($category->image);
            }

            if ($this->gm->delete('categories', ['id' => $id])) {
                $this->setMessage('success', 'Category deleted successfully');
            } else {
                $this->setMessage('danger', 'Failed to delete category');
            }
        } else {
            $this->setMessage('danger', 'Category not found');
        }

        redirect('categories');
    }

    // Toggle status (AJAX)
    public function toggle_status()
    {
        if ($this->isAjax()) {
            $id = $this->post('id');
            $status = $this->post('status');

            if ($this->gm->update('categories', ['is_active' => $status], ['id' => $id])) {
                $this->jsonResponse(true, 'Status updated successfully');
            } else {
                $this->jsonResponse(false, 'Failed to update status');
            }
        }
    }
}
