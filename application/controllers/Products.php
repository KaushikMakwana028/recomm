<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('security');
    }
    public function index()
    {
        $this->setPageTitle('Products');
        $this->loadView('products/index');
    }

    // List all products
    public function get_products()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search      = trim($this->input->post('search', true));
        $category_id = trim($this->input->post('category_id', true));
        $page        = (int)$this->input->post('page', true) ?: 1;
        $limit       = 10;
        $offset      = ($page - 1) * $limit;

        // Build query
        $this->db->select('products.*, categories.name as category_name');
        $this->db->from('products');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');

        // Apply category filter
        if (!empty($category_id)) {
            $this->db->where('products.category_id', $category_id);
        }

        // Apply search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('products.name', $search);
            $this->db->or_like('products.sku', $search);
            $this->db->group_end();
        }

        // Total records for pagination
        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

        // Get paginated results
        $products = $this->db
            ->order_by('products.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();

        // Generate HTML
        $html       = $this->generate_products_html($products, $offset);
        $pagination = $this->generate_pagination($page, $total_pages);

        echo json_encode([
            'status'        => true,
            'html'          => $html,
            'pagination'    => $pagination,
            'total_records' => $total_records,
            'current_page'  => $page,
            'total_pages'   => $total_pages
        ]);
    }

    // Generate products table HTML
    private function generate_products_html($products, $offset = 0)
    {
        if (empty($products)) {
            return '
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No products found</p>
                        <a href="' . base_url('products/add') . '" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-2"></i>Add First Product
                        </a>
                    </td>
                </tr>
            ';
        }

        $html = '';
        foreach ($products as $index => $product) {

            // Serial number (descending)
            $serial = $offset + $index + 1;

            // Product image
            if (!empty($product->image)) {
                $image_html = '
                    <img src="' . base_url(htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8')) . '" 
                         alt="' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '" 
                         class="img-thumbnail" 
                         style="width: 60px; height: 60px; object-fit: cover;">
                ';
            } else {
                $image_html = '
                    <div style="width: 60px; height: 60px; background: #e9ecef; 
                                display: flex; align-items: center; 
                                justify-content: center; border-radius: 8px;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                ';
            }

            // Category badge
            $category_html = !empty($product->category_name)
                ? '<span class="badge bg-info">' . htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8') . '</span>'
                : '<span class="text-muted">-</span>';

            // Price
            $price_html = '<strong>₹' . number_format($product->price, 2) . '</strong>';
            if (!empty($product->sale_price)) {
                $price_html .= '<br><small class="text-success">Sale: ₹' . number_format($product->sale_price, 2) . '</small>';
            }

            // Status toggle
            $checked = $product->is_active == 1 ? 'checked' : '';

            $html .= '
                <tr>
                    <td><strong>' . $serial . '</strong></td>
                    <td>' . $image_html . '</td>
                    <td>
                        <strong>' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '</strong>
                        <br>
                        <small class="text-muted">
                            SKU: ' . htmlspecialchars($product->sku ?: 'N/A', ENT_QUOTES, 'UTF-8') . '
                        </small>
                    </td>
                    <td>' . $category_html . '</td>
                    <td>' . $price_html . '</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle" 
                                   type="checkbox" 
                                   role="switch"
                                   data-id="' . (int)$product->id . '" 
                                   aria-label="Toggle status"
                                   ' . $checked . '>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="' . base_url('products/edit/' . (int)$product->id) . '" 
                           class="btn btn-sm btn-info" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button"
                                class="btn btn-sm btn-danger btn-delete"
                                data-id="' . (int)$product->id . '"
                                data-name="' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            ';
        }

        return $html;
    }

    // Generate pagination HTML
    private function generate_pagination($current_page, $total_pages)
    {
        if ($total_pages <= 1) {
            return '';
        }

        $html  = '<nav aria-label="Products pagination">';
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

        // Calculate 3 page buttons
        $start_page = max(1, $current_page - 1);
        $end_page   = min($total_pages, $start_page + 2);

        // Adjust if near end
        if ($end_page - $start_page < 2) {
            $start_page = max(1, $end_page - 2);
        }

        // Page buttons
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $i == $current_page ? 'active' : '';
            $html .= '
                <li class="page-item ' . $active . '">
                    <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
                </li>
            ';
        }

        // Last page with dots
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

        $html .= '</ul></nav>';

        return $html;
    }


    public function add()
    {
        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Product Name', 'required|trim');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');

            if ($this->form_validation->run()) {
                $slug = $this->generateSlug($this->post('name'));

                // Check if slug exists
                if ($this->gm->exists('products', ['slug' => $slug])) {
                    $slug = $slug . '-' . time();
                }

                // Handle main image upload
                $image = '';
                if (!empty($_FILES['image']['name'])) {
                    $image = $this->uploadProductImage('image');
                    if (!$image) {
                        $this->setMessage('danger', $this->upload->display_errors());
                        redirect('products/add');
                        return;
                    }
                }

                // Handle gallery images
                $gallery = [];
                if (!empty($_FILES['gallery']['name'][0])) {
                    $gallery = $this->uploadGalleryImages();
                }

                $data = [
                    'category_id' => $this->post('category_id'),
                    'name' => $this->post('name'),
                    'slug' => $slug,
                    'description' => $this->post('description'),
                    'price' => $this->post('price'),
                    'sale_price' => $this->post('sale_price') ?: NULL,
                    'sku' => $this->post('sku'),
                    'image' => $image,
                    'gallery' => !empty($gallery) ? json_encode($gallery) : NULL,
                    'is_active' => $this->post('is_active') ? 1 : 0,
                    'created_by' => 'admin',
                    'created_on' => date('Y-m-d H:i:s')
                ];

                if ($this->gm->insert('products', $data)) {
                    $this->setMessage('success', 'Product added successfully');
                    redirect('products');
                } else {
                    $this->setMessage('danger', 'Failed to add product');
                }
            }
        }

        $data['categories'] = $this->gm->getAll('categories', ['is_active' => 1], '*', 'name ASC');

        $this->setPageTitle('Add Product');
        $this->loadView('products/add', $data);
    }


    public function edit($id)
    {
        $data['product'] = $this->gm->getById('products', $id);

        if (!$data['product']) {
            $this->setMessage('danger', 'Product not found');
            redirect('products');
        }

        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Product Name', 'required|trim');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');

            if ($this->form_validation->run()) {
                $update_data = [
                    'category_id' => $this->post('category_id'),
                    'name' => $this->post('name'),
                    'description' => $this->post('description'),
                    'price' => $this->post('price'),
                    'sale_price' => $this->post('sale_price') ?: NULL,
                    'sku' => $this->post('sku'),
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

                // Handle main image upload
                if (!empty($_FILES['image']['name'])) {
                    $image = $this->uploadProductImage('image');
                    if ($image) {
                        // Delete old image
                        if ($data['product']->image && file_exists($data['product']->image)) {
                            unlink($data['product']->image);
                        }
                        $update_data['image'] = $image;
                    }
                }

                // Handle gallery images
                if (!empty($_FILES['gallery']['name'][0])) {
                    $gallery = $this->uploadGalleryImages();
                    if (!empty($gallery)) {
                        // Delete old gallery images
                        if ($data['product']->gallery) {
                            $old_gallery = json_decode($data['product']->gallery, true);
                            foreach ($old_gallery as $img) {
                                if (file_exists($img)) {
                                    unlink($img);
                                }
                            }
                        }
                        $update_data['gallery'] = json_encode($gallery);
                    }
                }

                if ($this->gm->update('products', $update_data, ['id' => $id])) {
                    $this->setMessage('success', 'Product updated successfully');
                    redirect('products');
                } else {
                    $this->setMessage('danger', 'Failed to update product');
                }
            }
        }

        $data['categories'] = $this->gm->getAll('categories', ['is_active' => 1], '*', 'name ASC');
        $data['gallery_images'] = $data['product']->gallery ? json_decode($data['product']->gallery, true) : [];

        $this->setPageTitle('Edit Product');
        $this->loadView('products/edit', $data);
    }

    // Delete product
    public function delete($id)
    {
        $product = $this->gm->getById('products', $id);

        if ($product) {
            // Check if any vendor using this product
            $vendor_count = $this->gm->countRows('vendor_products', ['product_id' => $id]);

            if ($vendor_count > 0) {
                $this->setMessage('warning', 'Cannot delete! This product is being used by ' . $vendor_count . ' vendor(s)');
                redirect('products');
                return;
            }

            // Delete images
            if ($product->image && file_exists($product->image)) {
                unlink($product->image);
            }

            if ($product->gallery) {
                $gallery = json_decode($product->gallery, true);
                foreach ($gallery as $img) {
                    if (file_exists($img)) {
                        unlink($img);
                    }
                }
            }

            if ($this->gm->delete('products', ['id' => $id])) {
                $this->setMessage('success', 'Product deleted successfully');
            } else {
                $this->setMessage('danger', 'Failed to delete product');
            }
        } else {
            $this->setMessage('danger', 'Product not found');
        }

        redirect('products');
    }

    // Toggle status
    public function toggle_status()
    {
        if ($this->isAjax()) {
            $id = $this->post('id');
            $status = $this->post('status');

            if ($this->gm->update('products', ['is_active' => $status], ['id' => $id])) {
                $this->jsonResponse(true, 'Status updated successfully');
            } else {
                $this->jsonResponse(false, 'Failed to update status');
            }
        }
    }

    // Upload product image
    private function uploadProductImage($field_name)
    {
        $upload_path = './assets/uploads/products/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $upload_data = $this->upload->data();
            return 'assets/uploads/products/' . $upload_data['file_name'];
        }

        return false;
    }

    // Upload gallery images
    private function uploadGalleryImages()
    {
        $upload_path = './assets/uploads/products/gallery/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $gallery = [];
        $files = $_FILES['gallery'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if (!empty($files['name'][$i])) {
                $_FILES['file']['name'] = $files['name'][$i];
                $_FILES['file']['type'] = $files['type'][$i];
                $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['file']['error'] = $files['error'][$i];
                $_FILES['file']['size'] = $files['size'][$i];

                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;

                $this->upload->initialize($config);

                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $gallery[] = 'assets/uploads/products/gallery/' . $upload_data['file_name'];
                }
            }
        }

        return $gallery;
    }

    // Search products (AJAX for vendors)
    public function search()
    {
        if ($this->isAjax()) {
            $keyword = $this->post('keyword');

            $products = $this->gm->search('products', ['name', 'sku'], $keyword, ['is_active' => 1]);

            $result = [];
            foreach ($products as $product) {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'image' => $product->image ? base_url($product->image) : ''
                ];
            }

            $this->jsonResponse(true, 'Products found', $result);
        }
    }


    // ============================
    // BULK ADD PRODUCTS
    // ============================

    // Download CSV template
    public function download_template()
    {
        $filename = 'products_bulk_upload_template.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Product Name',
            'Category',
            'Regular Price',
            'Sale Price',
            'Main Image (HTTPS URL)',
            'SKU (Optional)',
            'Description (Optional)',
            'Gallery Images (Optional, separate multiple URLs with |)'
        ]);

        fputcsv($output, [
            'Sprite 2 L',
            'Beverages',
            '100',
            '90',
            'https://example.com/images/sprite.jpg',
            'SPR-001',
            'Chilled lemon-lime soft drink',
            'https://example.com/images/sprite2.jpg|https://example.com/images/sprite3.jpg'
        ]);

        fclose($output);
        exit;
    }

    // Validate + insert products from an uploaded CSV/XLSX/XLS file
    public function bulk_upload()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (empty($_FILES['bulk_file']['name'])) {
            echo json_encode(['status' => false, 'message' => 'Please select a file to upload']);
            return;
        }

        $file    = $_FILES['bulk_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['csv', 'xlsx', 'xls'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['status' => false, 'message' => 'Invalid file type. Only CSV, XLSX, XLS files are allowed']);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => false, 'message' => 'File size too large. Max 5MB allowed']);
            return;
        }

        try {
            $rows = $this->parse_bulk_file($file['tmp_name'], $ext);
        } catch (\Exception $e) {
            echo json_encode(['status' => false, 'message' => 'Unable to read file: ' . $e->getMessage()]);
            return;
        }

        if (empty($rows)) {
            echo json_encode(['status' => false, 'message' => 'No data found in file']);
            return;
        }

        // Category name => id lookup
        $categories   = $this->gm->getAll('categories', ['is_active' => 1], 'id, name');
        $category_map = [];
        foreach ($categories as $cat) {
            $category_map[strtolower(trim($cat->name))] = $cat->id;
        }

        $errors         = [];
        $valid_products = [];

        foreach ($rows as $index => $row) {
            $row_no = $index + 2; // header row = 1

            $name        = trim($row['product_name'] ?? '');
            $category    = trim($row['category'] ?? '');
            $price       = trim($row['regular_price'] ?? '');
            $sale_price  = trim($row['sale_price'] ?? '');
            $image_url   = trim($row['main_image'] ?? '');
            $sku         = trim($row['sku'] ?? '');
            $description = trim($row['description'] ?? '');
            $gallery_raw = trim($row['gallery_images'] ?? '');

            // skip fully blank lines
            if ($name === '' && $category === '' && $price === '' && $sale_price === '' && $image_url === '') {
                continue;
            }

            $row_errors = [];

            if ($name === '') {
                $row_errors[] = 'Product Name is missing';
            }

            $category_id = null;
            if ($category === '') {
                $row_errors[] = 'Category is missing';
            } elseif (!isset($category_map[strtolower($category)])) {
                $row_errors[] = 'Category "' . $category . '" does not exist';
            } else {
                $category_id = $category_map[strtolower($category)];
            }

            if ($price === '') {
                $row_errors[] = 'Regular Price is missing';
            } elseif (!is_numeric($price) || $price <= 0) {
                $row_errors[] = 'Regular Price is invalid';
            }

            if ($sale_price === '') {
                $row_errors[] = 'Sale Price is missing';
            } elseif (!is_numeric($sale_price) || $sale_price <= 0) {
                $row_errors[] = 'Sale Price is invalid';
            } elseif (is_numeric($price) && $sale_price >= $price) {
                $row_errors[] = 'Sale Price must be less than Regular Price';
            }

            if ($image_url === '') {
                $row_errors[] = 'Main Image is missing';
            } elseif (stripos($image_url, 'https://') !== 0) {
                $row_errors[] = 'Main Image must be a valid HTTPS URL';
            } else {
                $check = $this->validate_remote_image($image_url);
                if ($check !== true) {
                    $row_errors[] = 'Main Image: ' . $check;
                }
            }

            $gallery_urls = [];
            if ($gallery_raw !== '') {
                $gallery_urls = array_filter(array_map('trim', explode('|', $gallery_raw)));
                foreach ($gallery_urls as $g_url) {
                    if (stripos($g_url, 'https://') !== 0) {
                        $row_errors[] = 'Gallery Image URL must be HTTPS: ' . $g_url;
                        continue;
                    }
                    $g_check = $this->validate_remote_image($g_url);
                    if ($g_check !== true) {
                        $row_errors[] = 'Gallery Image (' . $g_url . '): ' . $g_check;
                    }
                }
            }

            if (!empty($row_errors)) {
                $errors[] = ['row' => $row_no, 'product' => $name ?: '(Unnamed)', 'errors' => $row_errors];
                continue;
            }

            $valid_products[] = [
                'category_id'  => $category_id,
                'name'         => $name,
                'price'        => $price,
                'sale_price'   => $sale_price,
                'sku'          => $sku,
                'description'  => $description,
                'image_url'    => $image_url,
                'gallery_urls' => $gallery_urls
            ];
        }

        // ANY error anywhere -> add nothing
        if (!empty($errors)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Fix the errors below and re-upload. No products were added.',
                'errors'  => $errors
            ]);
            return;
        }

        if (empty($valid_products)) {
            echo json_encode(['status' => false, 'message' => 'No valid product rows found in file']);
            return;
        }

        // All rows valid -- download images and insert
        $this->db->trans_start();

        $inserted          = 0;
        $downloaded_files  = [];

        foreach ($valid_products as $p) {
            $image_path = $this->download_remote_image($p['image_url'], './assets/uploads/products/');
            if (!$image_path) {
                $this->db->trans_rollback();
                $this->cleanup_files($downloaded_files);
                echo json_encode(['status' => false, 'message' => 'Failed to download image for "' . $p['name'] . '". No products were added.']);
                return;
            }
            $downloaded_files[] = $image_path;

            $gallery_paths = [];
            foreach ($p['gallery_urls'] as $g_url) {
                $g_path = $this->download_remote_image($g_url, './assets/uploads/products/gallery/');
                if ($g_path) {
                    $gallery_paths[]     = $g_path;
                    $downloaded_files[]  = $g_path;
                }
            }

            $slug = $this->generateSlug($p['name']);
            if ($this->gm->exists('products', ['slug' => $slug])) {
                $slug = $slug . '-' . time() . rand(10, 99);
            }

            $this->gm->insert('products', [
                'category_id' => $p['category_id'],
                'name'        => $p['name'],
                'slug'        => $slug,
                'description' => $p['description'] ?: NULL,
                'price'       => $p['price'],
                'sale_price'  => $p['sale_price'],
                'sku'         => $p['sku'] ?: NULL,
                'image'       => $image_path,
                'gallery'     => !empty($gallery_paths) ? json_encode($gallery_paths) : NULL,
                'is_active'   => 1,
                'created_by'  => 'admin',
                'created_on'  => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->cleanup_files($downloaded_files);
            echo json_encode(['status' => false, 'message' => 'Database error. No products were added.']);
            return;
        }

        echo json_encode(['status' => true, 'message' => $inserted . ' product(s) added successfully']);
    }

    // Parse CSV / XLSX / XLS into an array of associative rows
    private function parse_bulk_file($tmp_path, $ext)
    {
        $rows = [];

        $normalize = function ($h) {
            $h = preg_replace('/\s*\(.*?\)\s*/', '', (string)$h);
            return str_replace(' ', '_', strtolower(trim($h)));
        };

        if ($ext === 'csv') {
            if (($handle = fopen($tmp_path, 'r')) !== false) {
                $header = array_map($normalize, fgetcsv($handle));
                while (($data = fgetcsv($handle)) !== false) {
                    if (count(array_filter($data, function ($v) {
                        return trim((string)$v) !== '';
                    })) === 0) {
                        continue;
                    }
                    $rows[] = array_combine($header, array_pad($data, count($header), ''));
                }
                fclose($handle);
            }
        } else {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmp_path);
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($tmp_path)->getActiveSheet();
            $data  = $sheet->toArray(null, true, true, false);

            $header = array_map($normalize, array_shift($data));
            foreach ($data as $line) {
                if (count(array_filter($line, function ($v) {
                    return trim((string)$v) !== '';
                })) === 0) {
                    continue;
                }
                $rows[] = array_combine($header, array_pad($line, count($header), ''));
            }
        }

        return $rows;
    }

    // Check a remote image (HEAD request) is reachable, an image, and <=1MB — without downloading it
    private function validate_remote_image($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);

        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return 'Could not reach image URL (' . $err . ')';
        }

        $http_code      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type   = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        if ($http_code != 200) {
            return 'Image URL not reachable (HTTP ' . $http_code . ')';
        }
        if (!$content_type || stripos($content_type, 'image/') !== 0) {
            return 'URL does not point to a valid image';
        }
        if ($content_length > 0 && $content_length > 1048576) {
            return 'Image size exceeds 1MB';
        }

        return true;
    }

    // Download a remote image to local storage
    private function download_remote_image($url, $upload_path)
    {
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $ext = 'jpg';
        }
        $filename    = md5(uniqid('', true)) . '.' . $ext;
        $destination = rtrim($upload_path, '/') . '/' . $filename;

        $fp = fopen($destination, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $success   = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!$success || $http_code != 200 || !file_exists($destination) || filesize($destination) === 0) {
            if (file_exists($destination)) unlink($destination);
            return false;
        }
        if (filesize($destination) > 1048576) {
            unlink($destination);
            return false;
        }

        return $upload_path === './assets/uploads/products/'
            ? 'assets/uploads/products/' . $filename
            : 'assets/uploads/products/gallery/' . $filename;
    }

    private function cleanup_files($paths)
    {
        foreach ($paths as $path) {
            if (file_exists($path)) unlink($path);
        }
    }
}
